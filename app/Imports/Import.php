<?php

namespace App\Imports;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
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
        $this->model = $model;
        $this->isDryRun = $isDryRun;
    }

    /**
     * Validate the import file.
     */
    public static function validateFile($file, $model, User $actingUser): array
    {
        $import = new self($actingUser, $model, true);
        Excel::import($import, $file);

        return $import->results;
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

    public static function getImportTemplate()
    {
        $template = require app_path('Helpers/Import/user_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }

    public function collection(Collection $collection): void
    {
        foreach ($collection->values() as $index => $item) {
            $item = array_map(fn ($value) => trim((string) $value), $item->toArray());
            $this->results['data'][] = [
                'index' => $index,
                ...$item,
                'is_active' => (int) $item['is_active'],
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

            $newItem = new $this->model($item);

            $newItem->save();

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
}
