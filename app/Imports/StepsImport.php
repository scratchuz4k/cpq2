<?php

namespace App\Imports;

use App\Http\Requests\StepCreateRequest;
use App\Models\SolutionTypeTenant;
use App\Models\Step;
use App\Models\User;
use App\Rules\TenantExistsRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class StepsImport extends Import implements ToCollection
{
    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false, protected bool $overwrite = false)
    {
        parent::__construct($this->actingUser, $model, $isDryRun);
    }

    /**
     * Insert a new Step
     */
    public function insert(array $item, int $index, ?int $parentId = null): bool
    {
        try {
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

            $newItem = Step::create([
                ...$item,
                'solution_type_id' => SolutionTypeTenant::find($item['solution_type_id'])->solution_type_id,
            ]);

            $newItem->save();

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
        $rules = (new StepCreateRequest)->rules();

        $rules['solution_type_id'] = ['required', 'integer', new TenantExistsRule('solution_type_tenant')];
        $rules['sort_order'] = ['integer', 'min:0'];

        return [...$rules, ...$this->isActiveRule()];
    }
}
