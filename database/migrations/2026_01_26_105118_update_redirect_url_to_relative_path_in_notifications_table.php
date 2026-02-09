<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $notifications = DB::table('notifications')
            ->whereNotNull('redirect_url')
            ->where('redirect_url', '!=', '')
            ->get();

        foreach ($notifications as $notification) {
            $url = $notification->redirect_url;

            $parsedUrl = parse_url($url);

            if (isset($parsedUrl['path'])) {
                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['redirect_url' => $parsedUrl['path']]);
            }
        }
    }
};
