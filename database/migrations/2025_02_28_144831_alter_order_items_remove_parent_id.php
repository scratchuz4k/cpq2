<?php

use App\Models\OrderItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('group')->default(0);
            $table->dropForeign(['parent_id']);
            $table->unsignedBigInteger('parent_id')->nullable()->change();
            $table->foreign('parent_id')->references('id')->on('order_items')->nullOnDelete();
        });

        $items = OrderItem::whereNull('parent_id')->get();

        foreach ($items as $item) {
            if (count($item->children)) {
                foreach ($item->children as $child) {
                    $child->update(['group' => $item->id]);
                }
                $item->delete();
            } else {
                $item->update(['group' => $item->id]);
            }
        }

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->foreign('parent_id')->references('id')->on('order_items')->cascadeOnDelete();
            $table->dropColumn('group');
        });
    }
};
