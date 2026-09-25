<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Rules\TenantExistsRule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false)
    {
        parent::__construct($actingUser, $model, $isDryRun);
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
     * Insert a new user with a specific manager.
     */
    public function insert(array $item, int $index): bool
    {
        try {
            $item['groups'] = $item['groups']
                ? array_map('trim', explode(',', $item['groups']))
                : [];

            $validator = Validator::make($item, $this->rules());

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $index,
                    'row' => $item,
                    'errors' => $validator->errors()->all(),
                ];

                return false;
            }

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            $newUser = User::create([
                'is_active' => (int) $item['is_active'] ?? 1,
                'name' => $item['name'] ?? '',
                'email' => $item['email'] ?? '',
                'password' => ! empty($item['password']) ? Hash::make($item['password']) : Hash::make(Str::random(12)),
                'sso' => isset($item['sso']) && (int) $item['sso'] === 1 ? 1 : 0,
                'commission_rate' => (int) $item['commission_rate'] ?? 0,
                'tenant_id' => $this->tenant->id,
            ]);

            $newUser->save();

            if (! empty($newUser->id)) {
                DB::table('tenant_user')->insert([
                    'tenant_id' => $this->tenant->id,
                    'user_id' => $newUser->id,
                ]);

                // Assign role
                if ($item['role']) {
                    $role = Role::find($item['role']);

                    $newUser->assignRole($role);
                }

                // Assign group
                $temp = [];
                foreach ($item['groups'] as $group) {
                    $temp[$group] = [
                        'tenant_id' => $this->actingUser->tenant_id,
                    ];
                }
                $newUser->groups()->sync($temp);

                $this->results['success']++;

                return true;
            }
        } catch (ValidationException $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $index,
                'row' => $item,
                'error' => $e->errors(),
            ];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $index,
                'row' => $item,
                'error' => $e->getMessage(),
            ];
        }

        return false;
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection->values() as $index => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['data'][] = [
                'index' => $index,
                ...$row,
            ];
            $this->results['processed']++;

            if ($this->skipIfDuplicate($row, $index)) {
                continue;
            }

            $this->insert($row, $index);
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')],
            'password' => ['required', 'string', 'min:8'],
            'groups' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value) || ! $this->actingUser->groups->count()) {
                        return;
                    }

                    if (
                        ! $this->actingUser->hasRole('Super Admin')
                        && ! empty(array_diff($value, $this->actingUser->groups->pluck('id')->toArray()))
                    ) {
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
                    $authRole = $this->actingUser->roles->first();

                    if (
                        ! $this->actingUser->hasRole('Super Admin')
                        && ! in_array((int) $value, [$authRole->id, ...$authRole->getDescendantIds()])
                    ) {
                        $fail("The {$attribute} must be {$authRole->name} or a descendant.");
                    }
                },
            ],
            'commission_rate' => 'nullable|integer|min:0|max:100',
            'sso' => ['boolean'],
            ...$this->isActiveRule(),
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
