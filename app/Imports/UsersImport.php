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

class UsersImport implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    public $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    private $usersWithManagers = [];

    private Tenant $tenant;

    public function __construct(protected bool $overwrite = false)
    {
        $this->tenant = Tenant::current();
    }

    /**
     * Recursively processes and inserts users with missing managers.
     *
     * @param  array  $usersWithManagers
     * @return void
     */
    private function processUsersWithManagers(?int $managerIndex = null)
    {

        if (empty($this->usersWithManagers)) {
            return;
        }
        if ($managerIndex !== null) {
            $currentUser = $this->usersWithManagers[$managerIndex];
            array_splice($this->usersWithManagers, $managerIndex, 1);
        } else {
            $currentUser = array_shift($this->usersWithManagers);
        }

        $manager = User::where('name', $currentUser['managername'])->first();

        if ($manager) {
            $this->insertUser($currentUser, $manager->id);
        } else {
            $managerKey = array_search($currentUser['managername'], array_column($this->usersWithManagers, 'name'),
                false);

            if ($managerKey !== false) {
                $this->processUsersWithManagers($managerKey);

                $newManager = User::where('name', $currentUser['managername'])->first();
                if (! empty($newManager)) {
                    $this->insertUser($currentUser, $newManager->id);
                } else {
                    $this->results['skipped']++;
                    $this->results['errors'][] = [
                        'row' => $currentUser, 'error' => 'Manager "'.$currentUser['managername'].'" not found!',
                    ];
                }
            } else {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row' => $currentUser, 'error' => 'Manager "'.$currentUser['managername'].'" not found!',
                ];
            }
        }

        if ($managerIndex === null) {
            $this->processUsersWithManagers();
        }
    }

    /**
     * Insert a new user with a specific manager.
     *
     * @return void
     */
    private function insertUser(array $user, ?int $managerId = null)
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

                return null;
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

                $this->results['success']++;
            }
        } catch (ValidationException $e) {
            $this->results['errors'][] = ['row' => $user, 'error' => $e->errors()];
        } catch (\Exception $e) {
            $this->results['errors'][] = ['row' => $user, 'error' => $e->getMessage()];
        }

        return null;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['processed']++;
            if (empty($row['managername'])) {
                $this->insertUser($row);
            } else {
                $this->usersWithManagers[] = $row;
            }
        }
        $this->processUsersWithManagers();
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
        $dummyUsers = User::factory()->count(10)->make();

        $data = $dummyUsers->map(function ($item) {
            return [
                'name' => $item->name,
                'email' => $item->email,
                'password' => $item->password,
                'sso' => $item->sso,
                'managername' => fake()->optional()->word(),
            ];
        });

        $filename = 'import_users_template.csv';

        return [
            'data' => $data,
            'filename' => $filename,
        ];
    }
}
