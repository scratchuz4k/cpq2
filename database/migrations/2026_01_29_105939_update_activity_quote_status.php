<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Map old quote statuses to the new ones. We normalize values with strtoupper(trim()) before lookup.
        $statuses = [
            'PENDING_APPROVAL' => 'PENDING',
            'VALIDATED' => 'APPROVED',
            'DECLINED' => 'REJECTED',
        ];

        DB::table('activities')
            ->where('activitable_type', 'App\\Models\\Quote')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($statuses) {
                foreach ($rows as $row) {
                    $data = json_decode($row->data, true);
                    if (! is_array($data)) {
                        continue;
                    }

                    $changed = false;
                    foreach ($data as $i => $item) {
                        if (isset($item['field']) && $item['field'] === 'status') {
                            if (isset($item['to']) && is_string($item['to'])) {
                                $key = strtoupper(trim($item['to']));
                                if (array_key_exists($key, $statuses)) {
                                    $item['to'] = $statuses[$key];
                                    $changed = true;
                                }
                            }
                            if (isset($item['from']) && is_string($item['from'])) {
                                $key = strtoupper(trim($item['from']));
                                if (array_key_exists($key, $statuses)) {
                                    $item['from'] = $statuses[$key];
                                    $changed = true;
                                }
                            }
                            $data[$i] = $item;
                        }
                    }

                    if ($changed) {
                        DB::table('activities')->where('id', $row->id)->update(['data' => json_encode($data)]);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $statuses = [
            'PENDING' => 'PENDING_APPROVAL',
            'APPROVED' => 'VALIDATED',
            'REJECTED' => 'DECLINED',
        ];

        DB::table('activities')
            ->where('activitable_type', 'App\\Models\\Quote')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($statuses) {
                foreach ($rows as $row) {
                    $data = json_decode($row->data, true);
                    if (! is_array($data)) {
                        continue;
                    }

                    $changed = false;
                    foreach ($data as $i => $item) {
                        if (isset($item['field']) && $item['field'] === 'status') {
                            if (isset($item['to']) && is_string($item['to'])) {
                                $key = strtoupper(trim($item['to']));
                                if (array_key_exists($key, $statuses)) {
                                    $item['to'] = $statuses[$key];
                                    $changed = true;
                                }
                            }
                            if (isset($item['from']) && is_string($item['from'])) {
                                $key = strtoupper(trim($item['from']));
                                if (array_key_exists($key, $statuses)) {
                                    $item['from'] = $statuses[$key];
                                    $changed = true;
                                }
                            }

                            $data[$i] = $item;
                        }
                    }

                    if ($changed) {
                        DB::table('activities')->where('id', $row->id)->update(['data' => json_encode($data)]);
                    }
                }
            });
    }
};
