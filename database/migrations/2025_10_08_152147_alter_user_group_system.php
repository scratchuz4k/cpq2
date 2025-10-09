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
        Schema::create('group_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        $items = DB::table('users')->get();
        foreach ($items as $item) {
            if (! $item->group_id) {
                continue;
            }
            $item = DB::table('group_user')->insert(['group_id' => $item->group_id, 'user_id' => $item->id]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn(['group_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
        });

        $items = DB::table('user_groups')->get();
        foreach ($items as $item) {
            $item = DB::table('users')->where('id', $item->id)->update(['group_id' => $item->group_id]);
        }

        Schema::dropIfExists('user_groups');

    }
};
