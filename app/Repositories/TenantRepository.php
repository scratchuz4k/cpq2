<?php

namespace App\Repositories;

use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TenantRepository
{
    public function createSooFileForTenant($data, $tenant)
    {
        $resource = Resource::find($tenant->sso_id);

        if ($resource) {
            Storage::disk('public')->put($resource->getRouteValue(), $data['sso_text_area']);

            return $resource->id;
        }

        $xml_content = $data['sso_text_area'];
        $path = storage_path('/app/temp/');

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path . 'temp_file.xml', $xml_content);
        $file = new UploadedFile($path . 'temp_file.xml', '');

        $path = $file->store('files', 'public');

        $resource = Resource::create([
            'title' => $file->getClientOriginalName() ?: $file->hashName(),
            'mime_type' => 'text/xml',
            'route' => $path,
            'size' => $file->getSize()
        ]);

        if (Storage::exists('temp/temp_file.xml')) {
            Storage::delete('temp/temp_file.xml');
        }

        return $resource->id;
    }
}
