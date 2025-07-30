<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function store(array $array): Category
    {
        $category = Category::create($array);

        return $category;
    }

    public function update(Category $category, array $array): Category
    {
        $category->update($array);

        if (array_key_exists('product_id', $array)) {
            $category->products()->toggle($array['product_id']);
        }

        return $category->refresh();
    }
}
