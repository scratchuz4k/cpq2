<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryCreateRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\SolutionTypeTenantResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Category/Index', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Category/Create', [
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryCreateRequest $request)
    {
        try {
            $category = DB::transaction(fn () => $this->categoryService->store($request->all()));
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        $solutionTypes = Auth::user()->currentTenant->solutionTypes->filter(fn ($solutionType) => $solutionType->pivot->is_active === 1)->makeHidden('pivot');

        return Inertia::render('Category/Edit', [
            'category' => (new CategoryResource($category))->resolve(),
            'solution_types' => (SolutionTypeTenantResource::collection($solutionTypes))->resolve(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category)
    {
        try {
            $category = DB::transaction(fn () => $this->categoryService->update($category, $request->all()));
        } catch (\Exception $e) {
            throw new \Exception($e);
        }

        return redirect()->route('categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category) {}
}
