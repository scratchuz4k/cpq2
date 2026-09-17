<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Carbon;

class ProductsService
{
    public function __construct(protected PriceService $priceService) {}

    public function store(array $array): Product
    {
        $product = Product::create([
            ...$array,
            'expires_at' => isset($array['expires_at']) ? Carbon::parse($array['expires_at'])->endOfDay() : null,
        ]);

        if (array_key_exists('prices', $array) && is_array($array['prices'])) {
            foreach ($array['prices'] as $price) {
                $this->priceService->store([...$price, 'product_id' => $product->id]);
            }
        }

        if (array_key_exists('categories', $array)) {
            $product->categories()->sync($array['categories']);
        }

        if (array_key_exists('steps', $array)) {
            $product->steps()->sync($array['steps']);
        }

        return $product;
    }

    public function update(Product $product, array $array): Product
    {
        $expiresAt = $product->expires_at;
        if (array_key_exists('expires_at', $array)) {
            $expiresAt = is_null($array['expires_at']) ? null : Carbon::parse($array['expires_at'])->endOfDay();
        }

        $product->update([
            ...$array,
            'expires_at' => $expiresAt,
        ]);

        if (array_key_exists('categories', $array)) {
            $product->categories()->syncWithoutDetaching($array['categories']);
        }

        if (array_key_exists('detach_categories', $array) && ! is_null($array['detach_categories'])) {
            $product->categories()->detach($array['detach_categories']);
        }

        if (array_key_exists('steps', $array) && is_array($array['steps'])) {
            $product->steps()->sync($array['steps']);
        }

        if (array_key_exists('attach_steps', $array) && is_array($array['attach_steps'])) {
            $product->steps()->syncWithoutDetaching($array['attach_steps']);
        }

        if (array_key_exists('detach_steps', $array) && ! is_null($array['detach_steps'])) {
            $product->steps()->detach($array['detach_steps']);
        }

        return $product->refresh();
    }
}
