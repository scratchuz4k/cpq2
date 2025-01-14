<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Resource;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dataProvider = new DataProvider(Resource::class);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            Log::debug($e->getMessage());
            return $e->getMessage();
        }

        return JsonResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);

        $files = $request->file;
        if (!is_array($files)) {
            $files = [$request->file];
        }

        try {
            foreach ($files as $key) {
                $folder = in_array($key->getClientMimeType(), ['image/jpeg', 'image/png', 'image/gif']) ? 'images' : 'files';
                if ($path = $key->store($folder, 'public')) {
                    $newFile = Resource::create([
                        'title' => $key->getClientOriginalName(),
                        'mime_type' => $key->getClientMimeType(),
                        'route' => $path,
                        'size' => $key->getSize(),
                        'user_id' => auth()->id(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response(['message' => 'Ocorreu um erro ao carregar o recurso!', 'error' => true], 500);
        }

        return response(['file' => $newFile, 'message' => 'Recurso carregado com sucesso!', 'error' => false], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource)
    {
        try {
            $resource = $resource->update([
                'title' => $request->title ?? null,
                'resource_type_id' => $request->resource_type_id ?? null,
                'alt_text' => $request->alt_text,
            ]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        //
    }
}
