<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Rules\TenantExistsRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class UsersImport implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    public $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
        'data' => [],
    ];

    protected Tenant $tenant;

    protected $isDryRun;

    public function __construct(protected bool $overwrite = false, bool $isDryRun = false)
    {
        $this->tenant = Tenant::current();
        $this->isDryRun = $isDryRun;
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function setDryRun(bool $isDryRun): void
    {
        $this->isDryRun = $isDryRun;
    }

    /**
     * Validate the import file.
     */
    public static function validateFile($file, bool $overwrite): array
    {
        $import = new self($overwrite, true);
        Excel::import($import, $file);

        return $import->results;
    }

    /**
     * Insert a new user with a specific manager.
     */
    private function insertUser(array $user, int $rowIndex): bool
    {
        try {
            $user['groups'] = explode(',', $user['groups']);
            $validator = Validator::make($user, [
                'name' => 'required|string|max:255',
                'email' => $this->overwrite
                    ? 'required|email'
                    : ['required', 'email', Rule::unique('users')],
                'is_active' => ['required', 'boolean'],
                'groups' => [
                    function ($attribute, $value, $fail) {
                        if (empty($value)) {
                            return;
                        }

                        if (! empty(array_diff($value, auth()->user()->groups->pluck('id')->toArray()))) {
                            $fail("The {$attribute} field contains group(s) the authenticated user does not belong to.");
                        }
                    },
                ],
                'groups.*' => [
                    'integer',
                    new TenantExistsRule('groups'),
                ],
                'role' => [
                    'integer',
                    new TenantExistsRule('roles'),
                    function ($attribute, $value, $fail) {
                        $authRole = auth()->user()->roles->first();

                        if (! in_array((int) $value, [$authRole->id, ...$authRole->getDescendantIds()])) {
                            $fail("The {$attribute} must be {$authRole->name} or a descendant.");
                        }
                    },
                ],
                'commission_rate' => 'nullable|integer|min:0|max:100',
            ]);

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $rowIndex,
                    'row' => $user,
                    'errors' => $validator->errors()->all(),
                ];

                return false;
            }

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            $newUser = User::create([
                'is_active' => (int) $user['is_active'] ?? 1,
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'password' => ! empty($user['password']) ? Hash::make($user['password']) : Hash::make(Str::random(12)),
                'sso' => isset($user['sso']) && (int) $user['sso'] === 1 ? 1 : 0,
                'commission_rate' => (int) $user['commission_rate'] ?? 0,
                'tenant_id' => $this->tenant->id,
            ]);

            $newUser->save();

            if (! empty($newUser->id)) {
                DB::table('tenant_user')->insert([
                    'tenant_id' => $this->tenant->id,
                    'user_id' => $newUser->id,
                ]);

                // Assign role
                if ($user['role']) {
                    $role = Role::find($user['role']);
                    $newUser->assignRole($role->name);
                }

                // Assign group
                $temp = [];
                foreach ($user['groups'] as $group) {
                    $temp[$group] = [
                        'tenant_id' => Auth::user()->tenant_id,
                    ];
                }
                $newUser->groups()->sync($temp);

                $this->results['success']++;

                return true;
            }
        } catch (ValidationException $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $rowIndex,
                'row' => $user,
                'error' => $e->errors(),
            ];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $rowIndex,
                'row' => $user,
                'error' => $e->getMessage(),
            ];
        }

        return false;
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection->values() as $rowIndex => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['data'][] = [
                'index' => $rowIndex,
                ...$row,
            ];
            $this->results['processed']++;
            $this->insertUser($row, $rowIndex);
        }
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function rules(): array
    {
        return [
            '*.email' => 'required|email|unique:users,email',
            '*.name' => 'required|string|max:255',
        ];
    }

    public static function getImportTemplate()
    {
        $template = require app_path('Helpers/Import/user_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }
}
