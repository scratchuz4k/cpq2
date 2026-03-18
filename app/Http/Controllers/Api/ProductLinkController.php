<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\BaseController;
use App\Models\ProductLink;
use App\Services\ProductLinkService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductLinkController extends BaseController
{
    protected $model = ProductLink::class;

    public function __construct(protected ProductLinkService $service)
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
    public function show(ProductLink $productLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductLink $productLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductLink $productLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductLink $productLink)
    {
        //
    }
}
