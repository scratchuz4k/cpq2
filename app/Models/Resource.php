<?php

namespace App\Models;

use App\Trait\SetTenantId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    use SetTenantId;

    protected $fillable = [
        'title',
        'route',
        'mime_type',
        'alt_text',
        'size',
        'tenant_id',
    ];

    protected $appends = ['full_route'];

    public function toArray()
    {
        $array = parent::toArray();

        if ($this->isXmlFile()) {
            $array['file_content'] = $this->file_content;
        }

        return $array;
    }

    public function isXmlFile(): bool
    {
        return $this->mime_type === 'application/xml' || $this->mime_type === 'text/xml';
    }

    public function getFileContentAttribute()
    {
        return $this->getContent();
    }

    public function setFileContentAttribute()
    {
        return $this->getContent();
    }

    public function getRouteAttribute(): string
    {
        return 'storage/'.$this->attributes['route'];
    }

    public function getFullRouteAttribute(): string
    {
        return asset('storage/'.$this->attributes['route']);
    }

    public function getRouteValue(): string
    {
        return $this->attributes['route'];
    }

    public function getBase64Attribute(): string
    {
        return base64_encode($this->getContent());
    }

    public function getContent(): string
    {
        return Storage::disk('public')->get($this->getRouteValue()) ?? '';
    }

    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
