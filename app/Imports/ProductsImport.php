<?php

namespace App\Imports;

use App\Http\Requests\ProductCreateRequest;
use App\Models\Category;
use App\Models\PricePlan;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithBatchInserts, WithHeadingRow
{
    use SkipsErrors;

    public $results = [
        'processed' => 0,
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    /**
     * Recursively processes and inserts users with missing managers.
     *
     * @param  array  $usersWithManagers
     * @return void
     */
    private function processUsersWithManagers(?int $managerIndex = null)
    {

        if (empty($this->usersWithManagers)) {
            return;
        }
        if ($managerIndex !== null) {
            $currentUser = $this->usersWithManagers[$managerIndex];
            array_splice($this->usersWithManagers, $managerIndex, 1);
        } else {
            $currentUser = array_shift($this->usersWithManagers);
        }

        $manager = User::where('name', $currentUser['managername'])->first();

        if ($manager) {
            $this->insertUser($currentUser, $manager->id);
        } else {
            $managerKey = array_search($currentUser['managername'], array_column($this->usersWithManagers, 'name'), false);

            if ($managerKey !== false) {
                $this->processUsersWithManagers($managerKey);

                $newManager = User::where('name', $currentUser['managername'])->first();
                if (! empty($newManager)) {
                    $this->insertUser($currentUser, $newManager->id);
                } else {
                    $this->results['skipped']++;
                    $this->results['errors'][] = ['row' => $currentUser, 'error' => 'Manager "'.$currentUser['managername'].'" not found!'];
                }
            } else {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $currentUser, 'error' => 'Manager "'.$currentUser['managername'].'" not found!'];
            }
        }

        if ($managerIndex === null) {
            $this->processUsersWithManagers();
        }
    }

    /**
     * Insert a new user with a specific manager.
     *
     * @return void
     */
    private function insertProduct(array $product, ?int $managerId = null)
    {
        try {
            $rules = (new ProductCreateRequest)->rules();

            $validator = Validator::make($product, $rules);

            if ($validator->fails()) {
                $this->results['skipped']++;
                $this->results['errors'][] = ['row' => $product, 'error' => 'Product has already been added!'];

                return null;
            }

            $newProduct = Product::create([
                'sku' => $product['sku'] ?? '',
                'name' => $product['name'] ?? '',
                'description' => $product['description'] ?? '',
                'expires_at' => $product['expires_at'] ?? null,
                'is_active' => $product['is_active'] ? true : false,
                'supplier_id' => Supplier::find($product['managername'])->first(),
                'brand_id' => Supplier::find($product['managername'])->first(),
                'price_plan_id' => PricePlan::find($product['managername'])->first(),
                'category_id' => Category::find($product['managername'])->first(),
                'parent_id' => '',
            ]);

            $newProduct->save();
        } catch (ValidationException $e) {
            $this->results['errors'][] = ['row' => $product, 'error' => $e->errors()];
        } catch (\Exception $e) {
            $this->results['errors'][] = ['row' => $product, 'error' => $e->getMessage()];
        }

        return null;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            $row = array_map(fn ($value) => trim((string) $value), $row->toArray());
            $this->results['processed']++;
            if (empty($row['managername'])) {
                $this->insertProduct($row);
            } else {
                $this->usersWithManagers[] = $row;
            }
        }
        $this->processUsersWithManagers();
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

    public function rules(): array
    {
        return [
            '*.email' => 'required|email|unique:users,email',
            '*.name' => 'required|string|max:255',
        ];
    }
}
