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
        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            $file = $data['file'];
            $xml_content = file_get_contents($file);
        }

        if ($data['sso_text_area']) {
            $xml_content = $data['sso_text_area'];
            $path = storage_path('/app/temp/');

            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            file_put_contents($path . 'temp_file.xml', $xml_content);
            $file = new UploadedFile($path . 'temp_file.xml', '');
        }

        if (!$xml_content || !simplexml_load_string($xml_content, null, LIBXML_NOERROR )) {
            if (Storage::exists('temp/temp_file.xml')) {
                Storage::delete('temp/temp_file.xml');
            }
            return redirect('admin')->withErrors(__('An error occurred while uploading the XML file.'));
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
}
