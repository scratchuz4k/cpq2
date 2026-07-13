<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $roles = DB::table('roles')->whereNotNull('parent_id')->get();

            foreach ($roles as $role) {
                $parent = DB::table('roles')->find($role->parent_id);

                if ($parent->tenant_id === $role->tenant_id) {
                    continue;
                }

                $newParent = DB::table('roles')
                    ->where('tenant_id', $role->tenant_id)
                    ->where('name', $parent->name)
                    ->first();

                if (! $newParent) {
                    continue;
                }

                DB::table('roles')
                    ->where('id', $role->id)
                    ->update([
                        'parent_id' => $newParent->id,
                    ]);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
