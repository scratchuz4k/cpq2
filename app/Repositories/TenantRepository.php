<?php

namespace App\Repositories;

use App\Models\Resource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantRepository
{
    public function createSooFileForTenant($data, $tenant)
    {
        // TODO on php upgrade to 8.4 implement LIBXML_NO_XXE verification to prevent xxe injextion
        $resource = Resource::find($tenant->sso_id);

        if ($resource) {
            Storage::disk('public')->put($resource->getRouteValue(), $data['sso_text_area']);

            return $resource->id;
        }

        $xml_content = $data['sso_text_area'];

        $fileName = 'files/'.Str::random(40).'.xml';
        Storage::disk('public')->put($fileName, $xml_content);

        $resource = Resource::create([
            'title' => 'tenant_'.$tenant->id.'.xml',
            'mime_type' => 'text/xml',
            'route' => $fileName,
            'size' => Storage::disk('public')->size($fileName),
            'tenant_id' => auth()->user()->tenant_id,
        ]);

        return $resource->id;
    }
}
