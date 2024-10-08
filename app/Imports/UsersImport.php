<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class UsersImport implements ToModel, WithHeadingRow
{

    private $rowsCreated = 0;
    private $rowsUpdated = 0;
    private $rowsFailures = 0;
    private $failures = [];

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $user = User::where('email', $row['email'])->first();
        try {
            if ($user) {
                $user->update([
                    'name'      => $row['name'],
                    'password'  =>  !empty($row['password']) ? Hash::make($row['password']) : '',
                    'sso'       => isset($user['sso']) && (int)$user['sso'] === 1 ? 1 : 0,
                    'parent_id' => User::where('name', $row['managername'])->first()?->id
                ]);
                $this->rowsUpdated++;
            } else {
                $user = User::create([
                    'name'      => $row['name'],
                    'email'     => $row['email'],
                    'password'  => !empty($row['password']) ? Hash::make($row['password']) : '',
                    'sso'       => isset($row['sso']) && (int)$row['sso'] === 1 ? 1 : 0,
                    'parent_id' => User::where('name', $row['managername'])->first()?->id
                ]);
                $this->rowsCreated++;
            }


            if (!empty($user->id)) {
                $tenant = app('currentTenant')->id;
                $user->tenants()->sync($tenant);

                $user->assignRole(User::$default_role);
            }
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
    }

    public function getRowCounts()
    {
        return [
            'Lines updated' => $this->rowsUpdated,
            'Lines created' => $this->rowsCreated,
            'Failures' => $this->failures ?? [],
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->rowsFailures += count($failures);
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
