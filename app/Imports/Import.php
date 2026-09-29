<?php

namespace App\Imports;

use App\Models\Tenant;
use App\Models\User;
use App\Rules\TenantUniqueRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\UploadedFile;
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
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

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

    protected array $processedItemsIndexes = [];

    protected ?array $templateColumns = null;

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

    /**
     * Trim a raw row into a null-blank associative array, tagged with its row index.
     */
    protected function normalizeRow(Collection $row, int $index): array
    {
        $item = collect($row)->map(function ($value) {
            $value = trim((string) $value);

            return $value === '' ? null : $value;
        })->toArray();

        $item['index'] = $index;

        return $item;
    }

    /**
     * Record a row as skipped, with either a list of validation-style errors
     * or a single explanatory message (e.g. "already exists").
     */
    protected function skip(array $row, array|string $errors): bool
    {
        $entry = [
            'row_index' => $row['index'] ?? null,
            'row' => $row,
        ];

        $entry += is_array($errors) ? ['errors' => $errors] : ['error' => $errors];

        $this->results['skipped']++;
        $this->results['errors'][] = $entry;

        return false;
    }

    /**
     * Validation rule restricting the Is Active column to 1 or 0.
     */
    protected function isActiveRule(): array
    {
        return ['is_active' => ['required', 'in:0,1']];
    }

    public function collection(Collection $collection): void
    {
        // Set the auth user so is available on rules classes.
        Auth::setUser($this->actingUser);

        $columnKeys = $this->templateColumnKeys();

        foreach ($collection->values() as $index => $item) {
            $item = array_map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            }, $item->toArray());

            $item = $this->useTemplateColumnKeys($item, $columnKeys);

            if (! array_key_exists('tenant_id', $item)) {
                $item['tenant_id'] = $this->tenant->id;
            }

            $this->results['data'][] = [
                'index' => $index,
                ...$item,
            ];
            $this->results['processed']++;

            if ($this->skipIfDuplicate($item, $index)) {
                continue;
            }

            $this->insert($item, $index);
        }
    }

    /**
     * The keys of the model's import template (app/Helpers/Import), indexed by
     * the heading WithHeadingRow reads for their label, e.g. "Post Code" => post_code.
     */
    protected function templateColumnKeys(): array
    {
        return collect($this->templateColumns())
            ->mapWithKeys(fn ($label, $key) => [HeadingRowFormatter::format([$label])[0] => $key])
            ->all();
    }

    protected function templateColumns(): array
    {
        if ($this->templateColumns !== null) {
            return $this->templateColumns;
        }

        $file = app_path('Helpers/Import/'.mb_strtolower(class_basename($this->model)).'_import.php');

        return $this->templateColumns = file_exists($file)
            ? ((require $file)['columns'] ?? [])
            : [];
    }

    /**
     * Rename the row's headings to the template column keys.
     */
    protected function useTemplateColumnKeys(array $item, array $columnKeys): array
    {
        $renamed = [];

        foreach ($item as $heading => $value) {
            $renamed[$columnKeys[$heading] ?? $heading] = $value;
        }

        return $renamed;
    }

    public function rules(): array
    {
        $modelName = class_basename($this->model);
        $requestClass = "App\\Http\\Requests\\{$modelName}CreateRequest";

        if (! class_exists($requestClass)) {
            throw new \RuntimeException("Request class {$requestClass} not found.");
        }

        $rules = (new $requestClass)->rules();

        return [...$rules, ...$this->isActiveRule()];
    }

    public function insert(array $item, int $index): bool
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
     * Fields covered by a Rule::unique() or TenantUniqueRule validation rule
     */
    protected function uniqueFields(): array
    {
        return collect($this->rules())
            ->filter(fn ($rule, $field) => ! str_contains($field, '.')
                && collect(is_array($rule) ? $rule : [$rule])
                    ->contains(fn ($r) => $r instanceof Unique || $r instanceof TenantUniqueRule))
            ->keys()
            ->all();
    }

    protected function isDuplicateInFile(string $group, array $values, int $index): int|false
    {
        if (in_array(null, $values, true) || in_array('', $values, true)) {
            return false;
        }

        $key = implode('|', array_map(fn ($value) => mb_strtolower(trim((string) $value)), $values));

        if (isset($this->processedItemsIndexes[$group][$key])) {
            return $this->processedItemsIndexes[$group][$key];
        }

        $this->processedItemsIndexes[$group][$key] = $index;

        return false;
    }

    protected function skipDuplicate(array $row, array $duplicateOf): bool
    {
        $messages = collect($duplicateOf)
            ->map(fn ($firstIndex, $group) => "Duplicate {$group}: matches row #".($firstIndex + 1).'.')
            ->all();

        return $this->skip($row, $messages);
    }

    protected function skipIfDuplicate(array $item, int $index): bool
    {
        $duplicateOf = $this->isDuplicateRow($item, $index);

        if (empty($duplicateOf)) {
            return false;
        }

        $this->skipDuplicate(['index' => $index, ...$item], $duplicateOf);

        return true;
    }

    protected function isDuplicateRow(array $item, int $index): array
    {
        $uniqueFields = $this->uniqueFields();

        if (empty($uniqueFields)) {
            return [];
        }

        $columns = $this->templateColumns();
        $group = implode(' and ', array_map(fn ($field) => $columns[$field] ?? $field, $uniqueFields));
        $values = array_map(fn ($field) => $item[$field] ?? null, $uniqueFields);

        $firstIndex = $this->isDuplicateInFile($group, $values, $index);

        return $firstIndex === false ? [] : [$group => $firstIndex];
    }

    public function storeFile(?UploadedFile $file, string $filename)
    {
        return $file->storeAs('imports', $filename, 'public');
    }
}
