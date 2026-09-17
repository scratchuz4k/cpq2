<?php

namespace App\Imports;

use App\Http\Requests\CategoryCreateRequest;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class CategoriesImport extends Import implements ToCollection
{
    protected ?array $availableCodes = [];

    public function __construct(protected User $actingUser, protected string $model, bool $isDryRun = false, protected bool $overwrite = false)
    {
        parent::__construct($this->actingUser, $model, $isDryRun);
    }

    /**
     * Insert a new Category
     */
    private function insert(array $item, int $index, ?int $parentId = null): bool
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

            $this->availableCodes[] = $item['code'];

            if ($this->isDryRun) {
                $this->results['success']++;

                return true;
            }

            unset($item['parent_code']);

            $newItem = Category::create([
                ...$item,
                'parent_id' => $parentId,
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

    public function collection(Collection $collection): void
    {
        $pendingItems = [];
        $fileCodes = $collection->pluck('code')->unique()->toArray();

        foreach ($collection->values() as $index => $item) {
            $item = array_map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            }, $item->toArray());

            if (array_key_exists('is_active', $item)) {
                $item['is_active'] = $item['is_active'] === null ? 1 : (int) $item['is_active'];
            }

            $this->results['data'][] = [
                'index' => $index,
                ...$item,
            ];
            $this->results['processed']++;

            if (! $item['parent_code']) {
                $this->insert($item, $index);
            } else {
                $pendingItems[$index] = $item;

            }
        }

        $fileCodeParentCodes = collect($pendingItems)->pluck('parent_code')->unique()->filter();

        if ($fileCodeParentCodes->isNotEmpty()) {
            $missingParentCodes = array_diff($fileCodeParentCodes->toArray(), $fileCodes);

            if (! empty($missingParentCodes)) {
                $existingDbCodes = Category::query()
                    ->whereIn('code', $missingParentCodes)
                    ->pluck('code')
                    ->toArray();

                $this->availableCodes = array_unique(array_merge($this->availableCodes, $existingDbCodes));
            }
        }

        $this->processDeferredChildren($pendingItems);
    }

    public function rules(): array
    {
        return (new CategoryCreateRequest)->rules();
    }

    private function processDeferredChildren(array $pendingItems): void
    {
        $processedInLastIteration = true;
        while (count($pendingItems) > 0 && $processedInLastIteration) {
            $processedInLastIteration = false;
            $remainingItems = [];

            foreach ($pendingItems as $index => $item) {
                $parent = null;
                if (in_array($item['parent_code'], $this->availableCodes)) {
                    if (! $this->isDryRun) {
                        $parent = Category::where('code', $item['parent_code'])->first();
                    }

                    if ($this->insert($item, $index, $parent ? $parent->id : null)) {
                        $processedInLastIteration = true;
                    }
                } else {
                    $remainingItems[$index] = $item;
                }
            }
            $pendingItems = $remainingItems;
        }

        if (count($pendingItems) > 0) {
            foreach ($pendingItems as $index => $item) {
                $this->results['skipped']++;
                $this->results['errors'][] = [
                    'row_index' => $index,
                    'row' => $item,
                    'error' => 'Parent Category with Code: "'.$item['parent_code'].'" not found!',
                ];
            }
        }
    }
}
