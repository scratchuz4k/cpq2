<?php

namespace App\Http\Controllers\Api;

use App\Services\CategoryService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
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

    public function __construct(protected CategoryService $categoryService)
    {
        parent::__construct();
    }

    /**
     * List Categories
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
     * Store a new Category
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request)
    {
    }

    /**
     * Show Category
     */
    #[ExcludeRouteFromDocs]
    public function show(Category $category)
    {
    }

    /**
     * Update Category
     */
    public function update(Request $request, Category $category)
    {
        try {
            $api = DB::transaction(function () use ($category, $request) {
                return $this->categoryService->update($category, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return new CategoryResource($category);
    }

    /**
     * Remove Category
     */
    public function destroy(Category $category)
    {
        $category->delete();
    }
}
