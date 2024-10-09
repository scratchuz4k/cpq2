<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection, WithHeadingRow, WithBatchInserts, SkipsOnFailure
{
    private $rowsProcessed = 0;
    private $rowsSkipped = 0;
    private $rowsSuccess = 0;
    private $rowsFailures = 0;
    private $failures = [];
    private $usersWithManagers = [];

    /**
     * Recursively processes and inserts users with missing managers.
     *
     * @param array $usersWithManagers
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
            $managerKey = array_search($currentUser['managername'], array_column($this->usersWithManagers, 'name'), false);
            //
            if ($managerKey !== false) {
                $this->processUsersWithManagers($managerKey);

                $newManager = User::where('name', $currentUser['managername'])->first();
                if (!empty($newManager)) {
                    $this->insertUser($currentUser, $newManager->id);
                }
            } else {
                $this->rowsSuccess = 0;
                throw new \Exception('Manager "' . $currentUser['managername'] . '" not found, associated to "' . $currentUser['name'] . '" (' . $currentUser['email'] . ')');
            }
        }

        if ($managerIndex === null) {
            $this->processUsersWithManagers();
        }
    }

    /**
     * Insert a new user with a specific manager.
     *
     * @param array $user
     * @param int|null $managerId
     * @return void
     */
    private function insertUser(array $user, ?int $managerId = null)
    {
        try {
            $newUser = User::create([
                'parent_id' => $managerId,
                'name'     => $user['name'] ?? '',
                'email'    => $user['email'] ?? '',
                'password' => !empty($user['password']) ? Hash::make($user['password']) : Hash::make(Str::random(12)),
                'sso'      => isset($user['sso']) && (int)$user['sso'] === 1 ? 1 : 0,
            ]);

            $newUser->save();

            if (!empty($newUser->id)) {

                $currentTenantId = app('currentTenant')->id;
                DB::table('tenant_user')->insert([
                    'tenant_id' => $currentTenantId,
                    'user_id' => $newUser->id,
                ]);

                $newUser->assignRole(User::$default_role);

                $this->rowsSuccess++;
            }
        } catch (QueryException $e) {
            $this->rowsSkipped++;
        }
    }

    /**
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $row = $row->toArray();
            $this->rowsProcessed++;

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

    public function onFailure(Failure ...$failures)
    {
        $this->rowsFailures += count($failures);
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getRowCounts()
    {
        return [
            'Lines Processed' => $this->rowsProcessed,
            'Lines Success' => $this->rowsSuccess,
            'Lines Skipped' => $this->rowsSkipped,  // Added skipped rows count to output
            'Failures' => $this->failures ?? [],
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function headingRow(): int
    {
        return 1;
    }
}
