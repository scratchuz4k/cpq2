<?php

namespace App\Imports;

use App\Http\Requests\AccountCreateRequest;
use App\Models\Account;
use App\Rules\TenantExistsRule;
use App\Support\GroupAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AccountsImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Insert a new Account
     */
    public function insert(array $item, int $index): bool
    {
        try {
            $item['groups'] = $item['groups']
                ? array_map('trim', explode(',', $item['groups']))
                : [];
            $actingUser = $this->actingUser;

            request()->merge($item);

            $existingAccount = $this->findExistingAccount($item);

            if ($existingAccount && ! $actingUser->can('update', $existingAccount)) {
                return $this->skip(['index' => $index, ...$item], 'You are not allowed to update this account.');
            }

            $validator = Validator::make($item, $this->rules($existingAccount));

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

            if ($existingAccount) {
                DB::transaction(fn () => $this->updateAccount($existingAccount, $item));
                $this->results['success']++;

                return true;
            }

            $newItem = DB::transaction(function () use ($item) {
                $trashed = Account::onlyTrashed()
                    ->where('tenant_id', $this->actingUser->tenant_id)
                    ->where('name', $item['name'])
                    ->where('reference', $item['reference'])
                    ->first();

                if (! $trashed) {
                    return Account::create($item);
                }

                $trashed->restore();
                $trashed->update($item);

                return $trashed;
            });

            if (! empty($newItem->id)) {
                $temp = [];
                foreach ($item['groups'] as $group) {
                    $temp[$group] = [
                        'tenant_id' => $actingUser->tenant_id,
                    ];
                }
                $newItem->groups()->sync($temp);
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

    /**
     * The account a row updates: the one sharing its reference, narrowed by name
     * when several accounts share that reference.
     */
    private function findExistingAccount(array $item): ?Account
    {
        if (! $this->overwrite || empty($item['reference'])) {
            return null;
        }

        $matches = Account::where('reference', $item['reference'])->get();

        if ($matches->count() > 1) {
            $matches = $matches->where('name', $item['name']);
        }

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function updateAccount(Account $account, array $item): void
    {
        $account->update([
            'name' => $item['name'],
            'reference' => $item['reference'],
            'is_active' => (int) $item['is_active'],
        ]);

        $groupIds = GroupAssignment::resolve(
            $this->actingUser,
            $account->groups()->pluck('groups.id'),
            $item['groups'],
            'account',
        );

        $account->groups()->sync(
            collect($groupIds)->mapWithKeys(fn ($id) => [$id => ['tenant_id' => $this->actingUser->tenant_id]])->all()
        );
    }

    public function rules(?Account $existingAccount = null): array
    {
        $rules = (new AccountCreateRequest)->rules();
        $currentGroupIds = $existingAccount ? $existingAccount->groups()->pluck('groups.id')->all() : [];

        unset($rules['groups.*'], $rules['groups']);

        if ($existingAccount) {
            $rules['name'] = [
                'required',
                Rule::unique('accounts')->where(fn ($query) => $query->where('tenant_id', $this->actingUser->tenant_id)
                    ->where('reference', request('reference'))
                    ->whereNull('deleted_at'))->ignore($existingAccount->id),
            ];
            $rules['reference'] = [
                'required',
                Rule::unique('accounts')->where(fn ($query) => $query->where('tenant_id', $this->actingUser->tenant_id)
                    ->where('name', request('name'))
                    ->whereNull('deleted_at'))->ignore($existingAccount->id),
            ];
        }

        $rules['groups'] = [
            'nullable',
            function ($attribute, $value, $fail) use ($currentGroupIds) {
                if (empty($value) || ! $this->actingUser->groups->count()) {
                    return;
                }

                $allowed = [...$this->actingUser->groups->pluck('id')->toArray(), ...$currentGroupIds];

                if (
                    ! $this->actingUser->hasRole('Super Admin')
                    && ! empty(array_diff($value, $allowed))
                ) {
                    $fail("The {$attribute} field contains group(s) the authenticated user does not belong to.");
                }
            },
        ];

        $rules['groups.*'] = [
            'integer',
            new TenantExistsRule('groups'),
        ];

        return [...$rules, ...$this->isActiveRule()];
    }
}
