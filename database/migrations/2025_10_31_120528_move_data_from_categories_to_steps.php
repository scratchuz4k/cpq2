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
        Schema::disableForeignKeyConstraints();
        DB::table('categories')->orderBy('id')->chunk(100, function ($categories) {
            $steps = [];

            foreach ($categories as $category) {
                $steps[] = [
                    'name' => $category->name,
                    'tenant_id' => $category->tenant_id,
                    'sort_order' => $category->sort_order,
                    'solution_type_id' => $category->solution_type_id,
                    'is_active' => $category->is_active,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            }

            if (! empty($steps)) {
                DB::table('steps')->insert($steps);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['solution_type_id']);
            $table->dropColumn('solution_type_id');
        });

        DB::table('categories')->truncate();
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('steps')->orderBy('id')->chunk(100, function ($steps) {
            $categories = [];
            foreach ($steps as $step) {
                $categories[] = [
                    'name' => $step->name,
                    'code' => mb_strtolower($step->name),
                    'tenant_id' => $step->tenant_id,
                    'sort_order' => $step->sort_order,
                    'solution_type_id' => $step->solution_type_id,
                    'is_active' => $step->is_active,
                    'created_at' => $step->created_at,
                    'updated_at' => $step->updated_at,
                ];
            }

            if (! empty($categories)) {
                DB::table('categories')->insert($categories);
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('solution_type_id')->nullable()->constrained('solution_types', 'id')->nullOnDelete();
        });

        DB::table('steps')->truncate();
        Schema::disableForeignKeyConstraints();
    }
};
