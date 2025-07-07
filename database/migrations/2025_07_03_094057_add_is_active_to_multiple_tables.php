<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    protected $models = [
        'App\Models\Account',
        'App\Models\Brand',
        'App\Models\EmailTemplate',
        'App\Models\Merchant',
        'App\Models\Order',
        'App\Models\Product',
        'App\Models\Quote',
        'App\Models\Region',
        'App\Models\SolutionType',
        'App\Models\Spa',
        'App\Models\Supplier',
        'App\Models\Token',
        'App\Models\User',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {

        foreach ($this->models as $model) {
            Schema::table(app($model)->getTable(), function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->models as $model) {
            Schema::table(app($model)->getTable(), function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
