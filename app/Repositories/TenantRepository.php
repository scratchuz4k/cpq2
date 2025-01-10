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
        if (array_key_exists('file', $data) && $data['file'] instanceof UploadedFile) {
            $file = $data['file'];
            $xml_content = file_get_contents($file);
        }

        if (array_key_exists('sso_text_area', $data) && $data['sso_text_area']) {
            $xml_content = $data['sso_text_area'];
            $path = storage_path('/app/temp/');

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . 'temp_file.xml', $xml_content);
            $file = new UploadedFile($path . 'temp_file.xml', '');
        }

        $path = $file->store('sso', 'local');

        $resource = Resource::create([
            'title' => $tenant->name . '-sso.xml',
            'mime_type' => 'text/xml',
            'route' => $path,
            'tenant_id' => $tenant->id,
        ]);

        if (Storage::exists('temp/temp_file.xml')) {
            Storage::delete('temp/temp_file.xml');
        }

        return $resource->id;
    }

    public function storeLogoForTenant($data, $tenant)
    {
        $path = $data['logo']->store('logo', 'public');
        $resource = Resource::create([
            'title' => $tenant->name . '-logo.png',
            'mime_type' => 'image/png',
            'route' => $path,
            'tenant_id' => $tenant->id,
            'alt_text' => $tenant->name
        ]);

        return $resource->id;
    }
}
