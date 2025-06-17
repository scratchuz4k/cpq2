<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;

class CategoryController extends BaseController
{

    protected $model = Category::class;
    public function __construct(protected CategoryService $categoryService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render(component: 'Category/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render(component: 'Category/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryCreateRequest $request)
    {
        try {
            $category = DB::transaction(function () use ($request) {
                return $this->categoryService->store($request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        return Inertia::render('Category/Show', [
            "category" => (new CategoryResource($category))->resolve()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return Inertia::render('Category/Edit', [
            "category" => (new CategoryResource($category))->resolve()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        try {
            $category = DB::transaction(function () use ($request, $category) {
                return $this->categoryService->update($category, $request->all());
            });
        } catch (\Exception $e) {
            throw new \Exception($e);
        }
        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
    }
}
