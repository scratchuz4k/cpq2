<?php

namespace App\Models\ShopBlocks;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $connection = 'mysql_shopblocks'; // Use the MySQL connection

    protected $dates = ['expire_at']; // Ensure the field is treated as a DateTime

    protected $fillable = ['code', 'long_description', 'meta_description', 'meta_title', 'name', 'short_description', 'parent_id'];

    protected function casts(): array
    {
        return [
            'image' => 'object',
        ];
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the parent user of this user.
     */
    public function parent()
    {
        return $this->hasOne(Category::class, 'id', 'parent_id');
    }
}
