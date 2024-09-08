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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->string('primary_contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('address_1')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');

        });

        DB::table('accounts')->select('id', 'primary_contact_name', 'email', 'address_1', 'city', 'postcode')
            ->chunkById(100, function ($accounts) {
                foreach ($accounts as $account) {
                    if ($account->primary_contact_name && $account->email) {
                        DB::table('contacts')->insert([
                            'account_id' => $account->id,
                            'primary_contact_name' => $account->primary_contact_name,
                            'email' => $account->email,
                            'address_1' => $account->address_1,
                            'city' => $account->city,
                            'postcode' => $account->postcode,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['primary_contact_name', 'email', 'address_1', 'city', 'postcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->after('reference', function ($table) {
                $table->string('primary_contact_name')->nullable();
                $table->string('email')->nullable();
                $table->string('address_1')->nullable();
                $table->string('city')->nullable();
                $table->string('postcode')->nullable();
            });
        });

        $contacts = DB::table('contacts')->select('account_id', 'primary_contact_name', 'email', 'address_1', 'city', 'postcode')
        ->get()
        ->groupBy('account_id');

        foreach ($contacts as $accountId => $contactGroup) {
            $firstContact = $contactGroup->first();

            DB::table('accounts')->where('id', $accountId)->update([
                'primary_contact_name' => $firstContact->primary_contact_name,
                'email' => $firstContact->email,
                'address_1' => $firstContact->address_1,
                'city' => $firstContact->city,
                'postcode' => $firstContact->postcode,
            ]);
        }

        Schema::dropIfExists('contacts');

    }
};
