<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\IndexRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\JsonResponse;
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
     * List Categories as nested tree (all active, no pagination)
     */
    public function tree(IndexRequest $request): JsonResponse
    {
        $stepId = $request->input('step_id');

        $dataProvider = new DataProvider(Category::class);
        $all = $dataProvider->getCollection(['id', 'name', 'parent_id', 'sort_order']);

        $map = [];
        foreach ($all as $c) {
            $map[$c->id] = array_merge($c->toArray(), ['children' => []]);
        }

        $roots = [];
        foreach ($map as $id => &$node) {
            if ($node['parent_id'] && isset($map[$node['parent_id']])) {
                $map[$node['parent_id']]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);

        if ($stepId) {
            $matchingIds = Category::where('is_active', true)
                ->whereHas('products', fn ($q) => $q->whereHas('steps', fn ($q2) => $q2->where('steps.id', $stepId)))
                ->pluck('id')
                ->flip()
                ->all();

            $roots = array_values(array_filter(array_map(
                fn (&$node) => $this->pruneTree($node, $matchingIds),
                $roots
            )));
        }

        return response()->json($roots);
    }

    private function pruneTree(array &$node, array $matchingIds): ?array
    {
        $node['children'] = array_values(array_filter(array_map(
            fn (&$child) => $this->pruneTree($child, $matchingIds),
            $node['children']
        )));

        if (isset($matchingIds[$node['id']]) || count($node['children']) > 0) {
            return $node;
        }

        return null;
    }

    /**
     * Store a new Category
     */
    #[ExcludeRouteFromDocs]
    public function store(CategoryCreateRequest $request) {}

    /**
     * Show Category
     */
    #[ExcludeRouteFromDocs]
    public function show(Category $category) {}

    /**
     * Update Category
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        try {
            $api = DB::transaction(fn () => $this->categoryService->update($category, $request->all()));
        } catch (\Exception $e) {
            return handleException($e);
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
