<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_id')->after('tenant_id')->nullable()->constrained('products', 'id')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->timestamp('expires_at')->nullable();
            // $table->renameColumn('code', 'sku');
        });

        $items = DB::table('products')->get();
        foreach ($items as $item) {
            $status = $item->is_active ? 'Active' : 'Inactive';
            $date = Carbon::parse($item->expires_at);
            if ($item->expires_at && $date->isPast()) {
                $status = 'Retired';
            }

            DB::table('products')
                ->where('id', $item->id)
                ->update(['status' => $status]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
            $table->dropColumn('status');
            $table->dropColumn('expires_at');
            // $table->renameColumn('sku', 'code');
        });
    }
};
