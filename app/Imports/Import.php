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

            $duplicateOf = $this->isDuplicateRow($item, $index);

            if (! empty($duplicateOf)) {
                $this->skipDuplicate(['index' => $index, ...$item], $duplicateOf);

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
        $file = app_path('Helpers/Import/'.mb_strtolower(class_basename($this->model)).'_import.php');

        if (! file_exists($file)) {
            return [];
        }

        return collect((require $file)['columns'] ?? [])
            ->mapWithKeys(fn ($label, $key) => [HeadingRowFormatter::format([$label])[0] => $key])
            ->all();
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

    /**
     * Whether a row with the same values for this group was already seen earlier
     * in the file. The first occurrence is recorded and never flagged. Rows with
     * a missing value are not checked, as their required-field errors cover them.
     *
     * Returns the index of the first row that had this key, or false if this is
     * the first occurrence.
     */
    protected function isDuplicateInFile(string $group, array $values, int $index): int|false
    {
        if (in_array(null, $values, true) || in_array('', $values, true)) {
            return false;
        }

        $key = implode('|', array_map(fn ($value) => mb_strtolower(trim((string) $value)), $values));

        if (isset($this->seenRowKeys[$group][$key])) {
            return $this->seenRowKeys[$group][$key];
        }

        $this->seenRowKeys[$group][$key] = $index;

        return false;
    }

    /**
     * Record a row as a duplicate, naming which earlier row(s) it collides with
     * for each duplicate-key group.
     */
    protected function skipDuplicate(array $row, array $duplicateOf): bool
    {
        $messages = collect($duplicateOf)
            ->map(fn ($firstIndex, $group) => "Duplicate {$group}: matches row ".($firstIndex + 2).'.')
            ->all();

        return $this->skip($row, $messages);
    }

    /**
     * Check every duplicate-key group for this row. Returns an array mapping
     * each group that duplicates an earlier row to that earlier row's index
     * (empty array if the row is unique across all groups).
     */
    protected function isDuplicateRow(array $item, int $index): array
    {
        $duplicateOf = [];

        foreach ($this->duplicateKeys() as $group => $columns) {
            $values = array_map(fn ($column) => $item[$column] ?? null, $columns);

            $firstIndex = $this->isDuplicateInFile($group, $values, $index);

            if ($firstIndex !== false) {
                $duplicateOf[$group] = $firstIndex;
            }
        }

        return $duplicateOf;
    }
}
