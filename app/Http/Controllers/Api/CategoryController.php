<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CategoryController extends BaseController
{
    protected $model = Category::class;

    public function __construct(protected CategoryService $categoryService)
    {
        parent::__construct();
    }

    /**
     * List Categories
     *
     * @response AnonymousResourceCollection<LengthAwarePaginator<CategoryResource>>
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Category::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return CategoryResource::collection($data);
    }

    /**
     * Store a new Category
     */
    #[ExcludeRouteFromDocs]
    public function store(Request $request) {}

    /**
     * Show Category
     */
    #[ExcludeRouteFromDocs]
    public function show(Category $category) {}

    /**
     * Update Category
     */
    public function update(Request $request, Category $category)
    {
        try {
            $api = DB::transaction(fn () => $this->categoryService->update($category, $request->all()));
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
