<?php

namespace App\Imports;

use App\Http\Requests\CategoryCreateRequest;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;

class CategoriesImport extends Import implements ToCollection
{
    protected ?array $availableCodes = [];

    /**
     * Insert a new Category
     */
    public function insert(array $item, int $index, ?int $parentId = null): bool
    {
        try {
            request()->merge($item);

            $existingCategory = $this->overwrite && ! empty($item['code'])
                ? Category::where('code', $item['code'])->first()
                : null;

            $validator = Validator::make($item, $this->rules($existingCategory));

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

            if ($existingCategory) {
                $existingCategory->update([
                    ...$item,
                    'parent_id' => $parentId,
                ]);

                $this->results['success']++;

                return true;
            }

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
        Auth::setUser($this->actingUser);

        $pendingItems = [];
        $fileCodes = $collection->pluck('code')->unique()->toArray();

        foreach ($collection->values() as $index => $item) {
            $item = array_map(function ($value) {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            }, $item->toArray());

            $this->results['data'][] = [
                'index' => $index,
                ...$item,
            ];
            $this->results['processed']++;

            if ($this->skipIfDuplicate($item, $index)) {
                continue;
            }

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

    public function rules(?Category $existingCategory = null): array
    {
        $rules = (new CategoryCreateRequest)->rules();

        if ($existingCategory) {
            $rules['code'] = [
                'required',
                Rule::unique('categories')->where(fn ($query) => $query->where('tenant_id', $this->actingUser->tenant_id))
                    ->ignore($existingCategory->id),
            ];
        }

        return [...$rules, ...$this->isActiveRule()];
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
