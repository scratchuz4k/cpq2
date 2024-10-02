<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Resource extends Model
{
    protected $fillable = [
        'title',
        'route',
        'mime_type',
        'alt_text',
        'size',
        'tenant_id',
        'resource_type_id'
    ];

    protected $appends = ['full_route', 'file_content'];

    public function getFileContentAttribute()
    {
        return $this->getContent();
    }

    public function getFullRouteAttribute(): string
    {
        return asset('storage/' . $this->attributes['route']);
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
        return Storage::get($this->route);
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
