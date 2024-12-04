<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\IndexRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Http\Resources\CategoryResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends BaseController
{
    protected $model = Category::class;
    /**
     * Index
     * @response AnonymousResourceCollection<LengthAwarePaginator<AccountResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Category::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return CategoryResource::collection($data);
    }

    /**
     * Store
     */
    public function store(Request $request) {}

    /**
     * Show.
     */
    public function show(Category $category) {}

    /**
     * Update
     */
    public function update(Request $request, Category $category) {}

    /**
     * Remove
     */
    public function destroy(Category $category) {
        $category->delete();
    }
}
