<?php

namespace App\Http\Controllers\Api;

use App\Helpers\DataProvider;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexRequest;
use App\Models\Resource;
use Dedoc\Scramble\Attributes\ExcludeRouteFromDocs;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * List of Resources
     */
    public function index(IndexRequest $request)
    {
        $dataProvider = new DataProvider(Resource::class);
        $dataProvider->builder = $dataProvider->builder->where('tenant_id', request()->user()->tenant_id);
        try {
            $data = $dataProvider->get();
        } catch (Exception $e) {
            return handleException($e);
        }

        return JsonResource::collection($data);
    }

    /**
     * Store a new Resource
     */
    public function store(Request $request)
    {
        $request->validate([
            'upload' => 'required',
        ]);

        $files = $request->upload;
        if (! is_array($files)) {
            $files = [$request->upload];
        }

        try {
            foreach ($files as $key) {
                $folder = in_array($key->getClientMimeType(), ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']) ? 'images' : 'files';

                [$mimeType, $originalName, $fileContent, $size] = [
                    $key->getClientMimeType(),
                    $key->getClientOriginalName(),
                    $key->get(),
                    $key->getSize(),
                ];

                $storedName = $this->getSafeFilename($originalName);

                if (in_array($mimeType, ['text/xml', 'application/xml']) && ! safeXmlParse($key->get())) {
                    return response()->json([
                        'message' => 'The file must contain a valid XML.',
                        'error' => true,
                    ], 422);
                }

                // check content file
                if ($path = $key->storeAs($folder, $storedName, 'public')) {
                    $newFile = Resource::create([
                        'title' => $originalName,
                        'mime_type' => $mimeType,
                        'route' => $path,
                        'size' => $size,
                        'tenant_id' => auth()->user()->tenant_id,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return handleException($e);
        }

        return response(['file' => $newFile, 'message' => 'Resource uploaded successfully!', 'error' => false], 200);
    }

    protected function getSafeFilename(string $originalName): string
    {
        $name = pathinfo($originalName, PATHINFO_FILENAME);
        $ext = pathinfo($originalName, PATHINFO_EXTENSION);
        $slug = Str::slug($name);
        $unique = time().'_'.Str::random(6);

        return $slug ? "{$slug}_{$unique}.{$ext}" : "{$unique}.{$ext}";
    }

    /**
     * Show Resource
     */
    #[ExcludeRouteFromDocs]
    public function show(Resource $resource)
    {
        //
    }

    /**
     * Update Resource
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
     * Remove Resource
     */
    public function destroy(Resource $resource)
    {
        $resource->delete();
        if (Storage::disk('public')->exists($resource->getRouteValue())) {
            Storage::disk('public')->delete($resource->getRouteValue());
        }
    }
}
