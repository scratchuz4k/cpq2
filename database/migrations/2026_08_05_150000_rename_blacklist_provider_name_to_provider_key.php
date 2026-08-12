<?php

use App\Models\General\Blacklist;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Blacklist::raw(function ($collection) {
            return $collection->updateMany(
                ['providerName' => ['$exists' => true]],
                ['$rename' => ['providerName' => 'providerKey']]
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Blacklist::raw(function ($collection) {
            return $collection->updateMany(
                ['providerKey' => ['$exists' => true]],
                ['$rename' => ['providerKey' => 'providerName']]
            );
        });
    }
};
