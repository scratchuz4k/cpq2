<?php

namespace App\Imports;

use App\Http\Requests\AccountCreateRequest;
use App\Models\Account;
use App\Models\User;
use App\Rules\TenantExistsRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AccountsImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false)
    {
        parent::__construct($actingUser, $model, $isDryRun);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Insert a new Account
     */
    protected function insert(array $item, int $index): bool
    {
        try {
            $item['groups'] = $item['groups']
                ? array_map('trim', explode(',', $item['groups']))
                : [];
            $actingUser = $this->actingUser;

            request()->merge($item);

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

            $newItem = Account::create($item);

            $newItem->save();

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

    public function rules(): array
    {
        $rules = (new AccountCreateRequest)->rules();

        unset($rules['groups.*'], $rules['groups']);

        $rules['groups'] = [
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
        ];

        $rules['groups.*'] = [
            'integer',
            new TenantExistsRule('groups'),
        ];

        return $rules;
    }
}
