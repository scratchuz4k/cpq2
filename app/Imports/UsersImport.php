<?php

namespace App\Imports;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
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
    private function insertUser(array $user, ?int $managerId = null): bool
    {
        try {
            $validator = Validator::make($user, [
                'name' => 'required|string|max:255',
                'email' => $this->overwrite
                    ? 'required|email'
                    : ['required', 'email', Rule::unique('users')],
            ]);

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $user, 'error' => 'User has already been added!'];

                return false;
            }

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            $newUser = User::create([
                'parent_id' => $managerId,
                'name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'password' => ! empty($user['password']) ? Hash::make($user['password']) : Hash::make(Str::random(12)),
                'sso' => isset($user['sso']) && (int) $user['sso'] === 1 ? 1 : 0,
                'tenant_id' => $this->tenant->id,
            ]);

            $newUser->save();

            if (! empty($newUser->id)) {
                $user = request()->user();
                DB::table('tenant_user')->insert([
                    'tenant_id' => $this->tenant->id,
                    'user_id' => $newUser->id,
                ]);

                $newUser->assignRole(User::$default_role);
                $this->results['success']++;

                return true;
            }
        } catch (ValidationException $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = ['row' => $user, 'error' => $e->errors()];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = ['row' => $user, 'error' => $e->getMessage()];
        }

        return false;
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['data'][] = $row;
            $this->results['processed']++;
            $this->insertUser($row);
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
