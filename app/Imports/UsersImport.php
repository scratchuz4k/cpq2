<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Rules\TenantExistsRule;
use App\Support\GroupAssignment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    public const PASSWORD_MASK = '********';

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

            if ($this->overwrite && $existingUser) {
                unset($item['password']);
            }

            $validator = Validator::make($item, $this->rules($existingUser));

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $index,
                    'row' => $this->maskPassword($item),
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
                'row' => $this->maskPassword($item),
                'error' => $e->errors(),
            ];
        } catch (\Exception $e) {
            $this->results['skipped']++;
            $this->results['errors'][] = [
                'row_index' => $index,
                'row' => $this->maskPassword($item),
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

    /**
     * Hide the plaintext password before a row is kept in the import results.
     */
    private function maskPassword(array $row): array
    {
        if (! empty($row['password'])) {
            $row['password'] = self::PASSWORD_MASK;
        }

        return $row;
    }

    public function collection(Collection $collection): void
    {
        Auth::setUser($this->actingUser);
        foreach ($collection->values() as $index => $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['data'][] = [
                'index' => $index,
                ...$this->maskPassword($row),
            ];
            $this->results['processed']++;

            if ($this->skipIfDuplicate($row, $index)) {
                continue;
            }

            $this->insert($row, $index);
        }
    }

    public function rules(?User $existingUser = null): array
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
                    if ($existingUser && ! $actingUser->can('update', $existingUser)) {
                        $fail('You are not allowed to update this user.');
                    }
                },
            ],
            'password' => [
                $existingUser ? 'nullable' : 'required',
                'string',
                'min:8',
            ],
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

    /**
     * Return the CSV at $filePath with every non-empty password replaced by '********'.
     */
    public static function maskPasswordsInCsv(string $filePath): string
    {
        $in = fopen($filePath, 'r');
        $out = fopen('php://temp', 'r+');

        // Imports auto-detect the delimiter, so do the same from the header line.
        $firstLine = (string) fgets($in);
        rewind($in);
        $counts = array_map(fn ($d) => substr_count($firstLine, $d), [',' => ',', ';' => ';', "\t" => "\t", '|' => '|']);
        $delimiter = array_search(max($counts), $counts, true) ?: ',';

        $header = fgetcsv($in, null, $delimiter) ?: [];

        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        }
        $keys = array_map(fn ($h) => Str::slug((string) $h, '_'), $header);
        $idx = array_search('password', $keys, true);

        fputcsv($out, $header, $delimiter);
        while (($row = fgetcsv($in, null, $delimiter)) !== false) {
            if ($idx !== false && ($row[$idx] ?? '') !== '') {
                $row[$idx] = self::PASSWORD_MASK;
            }
            fputcsv($out, $row, $delimiter);
        }

        rewind($out);
        $contents = stream_get_contents($out);
        fclose($in);
        fclose($out);

        return $contents;
    }

    public static function getImportTemplate()
    {
        $template = require app_path('Helpers/Import/user_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }

    public function storeFile(?UploadedFile $file, string $filename)
    {
        $path = "imports/{$filename}";
        $stored = $file->storeAs('imports', $filename, 'local')
            && Storage::disk('public')->put($path, UsersImport::maskPasswordsInCsv($file->getRealPath()));

        if (! $stored) {
            Storage::disk('local')->delete($path);

            return false;
        }

        return $path;
    }
}
