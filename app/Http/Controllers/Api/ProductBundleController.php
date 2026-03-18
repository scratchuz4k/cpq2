<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Models\ProductBundle;
use App\Services\ProductBundleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBundleController extends BaseController
{
    protected $model = ProductBundle::class;

    public function __construct(protected ProductBundleService $service)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider($this->model);

        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return JsonResource::collection($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductBundle $productBundle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductBundle $productBundle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductBundle $productBundle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductBundle $productBundle)
    {
        //
    }
}
