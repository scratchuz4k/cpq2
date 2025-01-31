<?php

namespace App\Repositories;

use App\Models\Resource;
use App\Models\Tenant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Mime\MimeTypes;

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

        $fileName = 'files/' . Str::random(40) . '.xml';
        Storage::disk('public')->put($fileName, $xml_content);

        $resource = Resource::create([
            'title' => 'tenant_' . $tenant->id . '.xml',
            'mime_type' => 'text/xml',
            'route' => $fileName,
            'size' => Storage::disk('public')->size($fileName),
            'tenant_id' => auth()->user()->tenant_id
        ]);

        if (Storage::exists('temp/temp_file.xml')) {
            Storage::delete('temp/temp_file.xml');
        }

        return $resource->id;
    }
}
