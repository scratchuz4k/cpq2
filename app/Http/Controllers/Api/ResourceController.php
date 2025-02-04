<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\Models\Resource;
use Illuminate\Http\Request;
use App\Helpers\DataProvider;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
        //TODO on php upgrade to 8.4 implement LIBXML_NO_XXE verification to prevent xxe injextion
        $request->validate([
            'file' => 'required',
        ]);

        $files = $request->file;
        if (!is_array($files)) {
            $files = [$request->file];
        }

        try {
            foreach ($files as $key) {
				$folder = in_array($key->getMimeType(), ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']) ? 'images' : 'files';

                [$mimeType, $originalName, $fileContent, $size] = [
                    $key->getMimeType(), $key->getClientOriginalName(), $key->get(), $key->getSize()
                ];

                if (in_array($mimeType, ['text/xml', 'application/xml'])  && !simplexml_load_string($key->get(), null, LIBXML_NOERROR)) {
                    return response()->json([
                        'message' => 'The file must contain a valid XML.',
                        'error' => true,
                    ], 422);
                }

                // check content file
                if ($path = $key->store($folder, 'public')) {
                    $newFile = Resource::create([
                        'title' => $originalName,
                        'mime_type' => $mimeType,
                        'route' => $path,
                        'size' => $size,
                        'tenant_id' => auth()->user()->tenant_id
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response(['message' => 'An error occurred while uploading the resource.!', 'error' => true], 500);
        }

        return response(['file' => $newFile, 'message' => 'Resource uploaded successfully!', 'error' => false], 200);
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
        $resource->delete();
        if (Storage::disk('public')->exists($resource->getRouteValue())) {
            Storage::disk('public')->delete($resource->getRouteValue());
        }
    }
}
