<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private $fields = ['bearer', 'speed'];

    private $units = [
        'Mb' => 'Mbps',
        'Gb' => 'Gbps',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->fields as $field) {
            foreach ($this->units as $from => $to) {
                DB::connection('mongodb_general')
                    ->table('blacklists')
                    ->where("{$field}.unit", $from)
                    ->update(["{$field}.unit" => $to]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->fields as $field) {
            foreach (array_flip($this->units) as $from => $to) {
                DB::connection('mongodb_general')
                    ->table('blacklists')
                    ->where("{$field}.unit", $from)
                    ->update(["{$field}.unit" => $to]);
            }
        }
    }
};
