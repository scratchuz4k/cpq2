<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Rules\TenantExistsRule;
use App\Support\GroupAssignment;
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
    public function __construct(
        protected User $actingUser,
        protected string $model,
        bool $isDryRun = false,
        protected bool $overwrite = false
    ) {
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

            $existingUser = $this->overwrite && ! empty($item['email'])
                ? User::where('email', $item['email'])->where('tenant_id', $this->tenant->id)->first()
                : null;

            $validator = Validator::make($item, $this->rules($existingUser));

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

            $saved = $existingUser
                ? DB::transaction(fn () => $this->updateUser($existingUser, $item))
                : $this->createUser($item);

            if ($saved) {
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

    private function createUser(array $item): bool
    {
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

        if (empty($newUser->id)) {
            return false;
        }

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

        return true;
    }

    /**
     * Update existing user
     */
    private function updateUser(User $user, array $item): bool
    {
        $data = [
            'is_active' => (int) $item['is_active'],
            'name' => $item['name'],
            'sso' => isset($item['sso']) && (int) $item['sso'] === 1 ? 1 : 0,
            'commission_rate' => (int) $item['commission_rate'],
        ];

        if (! empty($item['password'])) {
            $data['password'] = Hash::make($item['password']);
        }

        $user->update($data);

        $user->syncRoles($item['role'] ? [Role::find($item['role'])] : []);

        $groupIds = GroupAssignment::resolve(
            $this->actingUser,
            $user->groups()->pluck('groups.id'),
            $item['groups'],
            'user',
        );

        $user->groups()->sync(
            collect($groupIds)->mapWithKeys(fn ($id) => [$id => ['tenant_id' => $this->actingUser->tenant_id]])->all()
        );

        return true;
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
            $this->insert($row, $index);
        }
    }

    public function rules(): array
    {
        $actingUser = $this->actingUser;
        $retainableGroupIds = $existingUser ? $existingUser->groupIds()->all() : [];

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($existingUser?->id),
                function ($attribute, $value, $fail) use ($actingUser, $existingUser) {
                    $targetRole = $existingUser?->roles->first();

                    if (! $targetRole || $actingUser->hasRole('Super Admin')) {
                        return;
                    }

                    $authRole = $actingUser->roles->first();

                    if (! $authRole || ! in_array($targetRole->id, [$authRole->id, ...$authRole->getDescendantIds()])) {
                        $fail('You are not allowed to update this user.');
                    }
                },
            ],
            'password' => $existingUser
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
            'is_active' => ['boolean'],
            'groups' => [
                'nullable',
                function ($attribute, $value, $fail) use ($actingUser, $retainableGroupIds) {
                    if (empty($value) || ! $actingUser->groups->count()) {
                        return;
                    }

                    $allowed = [...$actingUser->groups->pluck('id')->toArray(), ...$retainableGroupIds];

                    if (
                        ! $actingUser->hasRole('Super Admin')
                        && ! empty(array_diff($value, $allowed))
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
                function ($attribute, $value, $fail) use ($actingUser) {
                    $authRole = $actingUser->roles->first();

                    if (
                        ! $actingUser->hasRole('Super Admin')
                        && ! in_array((int) $value, [$authRole->id, ...$authRole->getDescendantIds()])
                    ) {
                        $fail("The {$attribute} must be {$authRole->name} or a descendant.");
                    }
                },
            ],
            'commission_rate' => 'nullable|integer|min:0|max:100',
            'sso' => ['boolean'],
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
