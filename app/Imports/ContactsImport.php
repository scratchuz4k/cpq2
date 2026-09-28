<?php

namespace App\Imports;

use App\Http\Requests\ContactCreateRequest;
use App\Models\Account;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactsImport extends Import implements ToCollection, WithBatchInserts, WithHeadingRow
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
     * Insert a new Contact, linked to the account matching the row's Account ID
     */
    public function insert(array $item, int $index): bool
    {
        try {
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

            // Check if row has any value other than the account id
            if (! $this->rowHasValues($item)) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $index,
                    'row' => $item,
                    'error' => 'No Contact field is filled for this row.',
                ];

                return false;
            }

            $accountId = Account::visibleToGroups($this->actingUser)
                ->whereKey($item['account_id'])
                ->value('id');

            if (! $accountId) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $index,
                    'row' => $item,
                    'error' => "No account was found with the ID {$item['account_id']}.",
                ];

                return false;
            }

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            Contact::create([
                ...Arr::only($item, (new Contact)->getFillable()),
                'account_id' => $accountId,
            ]);

            $this->results['success']++;

            return true;
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
        $rules = (new ContactCreateRequest)->rules();

        $rules['account_id'] = ['required', 'integer'];
        $rules['phone'] = ['nullable', 'regex:/^[0-9]+$/', 'max_digits:12'];

        return $rules;
    }

    public function rowHasValues(array $item): bool
    {
        foreach ($item as $key => $value) {
            if (in_array($key, ['account_id', 'tenant_id'], true)) {
                continue;
            }
            if ($value !== null) {
                return true;
            }
        }

        return false;
    }
}
