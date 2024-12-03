<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

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
        return $category;
    }
}
