<?php

namespace App\Repositories;

use App\Models\Resource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantRepository
{
    public function createSooFileForTenant($data, $tenant)
    {
        $resource = Resource::find($tenant->sso_id);

        $ssoText = safeXmlParse($data['sso_text_area']);
        if ($resource) {
            Storage::disk('public')->put($resource->getRouteValue(), $ssoText);

            return $resource->id;
        }

        $xml_content = $ssoText;

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
