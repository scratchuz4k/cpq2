<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\ProductAddon;
use Illuminate\Support\Collection;

class ProductAddonImport extends Import
{
    public function collection(Collection $collection): void
    {
        foreach ($collection as $rowIndex => $row) {
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $item = $this->normalizeRow($row, $rowIndex);

            $this->results['processed']++;
            $this->results['data'][] = $item;
            $this->insertAddon($item);
        }
    }

    private function insertAddon(array $row): bool
    {
        // Column keys must match Str::slug($label, '_') of the CSV header label
        // (see app/Helpers/Import/productaddon_import.php's "columns"), which is
        // how Laravel Excel's WithHeadingRow derives row keys from the uploaded
        // file's header row — "Add-on Product SKU" slugs to "add_on_product_sku".
        $productSku = $row['product_sku'] ?? null;
        $addonSku = $row['add_on_product_sku'] ?? null;

        $errors = [];

        if (empty($productSku)) {
            $errors[] = 'The Product SKU field is required.';
        }

        if (empty($addonSku)) {
            $errors[] = 'The Add-on Product SKU field is required.';
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if ($productSku === $addonSku) {
            return $this->skip($row, ['A product cannot be an add-on of itself.']);
        }

        $product = Product::where('sku', $productSku)->first();
        $addonProduct = Product::where('sku', $addonSku)->first();

        if (! $product) {
            $errors[] = "No product found with SKU \"{$productSku}\".";
        }

        if (! $addonProduct) {
            $errors[] = "No product found with SKU \"{$addonSku}\".";
        }

        if ($errors) {
            return $this->skip($row, $errors);
        }

        if (ProductAddon::where('product_id', $product->id)->where('addon_id', $addonProduct->id)->exists()) {
            return $this->skip($row, 'This product add-on already exists.');
        }

        if ($this->isDryRun) {
            $this->results['success']++;

            return true;
        }

        ProductAddon::create([
            'product_id' => $product->id,
            'addon_id' => $addonProduct->id,
        ]);

        $this->results['success']++;

        return true;
    }

    public static function getImportTemplate(): array
    {
        $template = require app_path('Helpers/Import/productaddon_import.php');

        return [
            'data' => collect($template['data']),
            'filename' => $template['filename'],
        ];
    }
}
