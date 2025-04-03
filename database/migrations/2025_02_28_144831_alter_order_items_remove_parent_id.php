<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        });

        $items = DB::table('order_items')->get();
        foreach ($items as $item) {
            $item = DB::table('order_items')->where('id', $item->id)->first();
            $childs = DB::table('order_items')->where('parent_id', $item->id)->get();

            if (! count($childs) && $item->group == 0) {
                DB::table('order_items')
                    ->where('id', $item->id)
                    ->update(['group' => $item->id]);

                continue;
            }

            if (count($childs)) {
                foreach ($childs as $child) {
                    DB::table('order_items')
                        ->where('id', $child->id)
                        ->update(['group' => $item->id]);
                }

                DB::table('order_items')
                    ->where('id', $item->id)
                    ->delete();

            }

        }

        Schema::table('order_items', function (Blueprint $table) {
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
