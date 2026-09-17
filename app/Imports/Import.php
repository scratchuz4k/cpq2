<?php

namespace App\Imports;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class Import implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    protected Tenant $tenant;

    protected bool $isDryRun;

    protected array $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
        'data' => [],
    ];

    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false)
    {
        $this->tenant = Tenant::current();
        $this->isDryRun = $isDryRun;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function validateFile($file): array
    {
        $this->isDryRun = true;
        Excel::import($this, $file);

        return $this->results;
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

    public function getResults(): array
    {
        return $this->results;
    }

    public function collection(Collection $collection): void
    {
        // Set the auth user so is available on rules classes.
        Auth::setUser($this->actingUser);

        foreach ($collection->values() as $index => $item) {
            $item = array_map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            }, $item->toArray());

            if (array_key_exists('is_active', $item)) {
                $item['is_active'] = $item['is_active'] === null ? 1 : (int) $item['is_active'];
            }

            if (! array_key_exists('tenant_id', $item)) {
                $item['tenant_id'] = $this->tenant->id;
            }

            $this->results['data'][] = [
                'index' => $index,
                ...$item,
            ];
            $this->results['processed']++;
            $this->insert($item, $index);
        }
    }

    public function rules(): array
    {
        $modelName = class_basename($this->model);
        $requestClass = "App\\Http\\Requests\\{$modelName}CreateRequest";

        if (! class_exists($requestClass)) {
            throw new \RuntimeException("Request class {$requestClass} not found.");
        }

        $rules = (new $requestClass)->rules();

        return $rules;
    }

    private function insert(array $item, int $index): bool
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

            $newItem = $this->resolveModel($item);

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

    /**
     * Build the model to save for this row. For soft-deletable models, a row that
     * matches an existing trashed record on its unique fields is restored and
     * updated instead of inserted, since the row would otherwise still collide
     * with the trashed record's unique constraint.
     */
    private function resolveModel(array $item): Model
    {
        $modelClass = $this->model;
        $item = array_filter($item, fn ($value) => $value !== null);

        if (! in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
            return new $modelClass($item);
        }

        $uniqueFields = $this->uniqueFields();

        if (empty($uniqueFields)) {
            return new $modelClass($item);
        }

        $conditions = collect($uniqueFields)
            ->mapWithKeys(fn ($field) => [$field => $item[$field] ?? null])
            ->all();

        $existing = $modelClass::withTrashed()->where($conditions)->first();

        if (! $existing) {
            return new $modelClass($item);
        }

        if ($existing->trashed()) {
            $existing->restore();
        }

        $existing->fill($item);

        return $existing;
    }

    /**
     * Fields covered by a Rule::unique() validation rule for this import, i.e.
     * the columns whose combination the model's DB unique constraint enforces.
     */
    private function uniqueFields(): array
    {
        return collect($this->rules())
            ->filter(fn ($rule) => collect(is_array($rule) ? $rule : [$rule])
                ->contains(fn ($r) => $r instanceof Unique))
            ->keys()
            ->all();
    }
}
