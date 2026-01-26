<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $originalUrls = [];

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
                // Store the original URL for rollback
                $this->originalUrls[$notification->id] = $url;

                DB::table('notifications')
                    ->where('id', $notification->id)
                    ->update(['redirect_url' => $parsedUrl['path']]);
            }
        }

        // Save original URLs to a temporary table for rollback
        if (! empty($this->originalUrls)) {
            Schema::create('notifications_redirect_url_backup', function (Blueprint $table) {
                $table->unsignedBigInteger('notification_id')->primary();
                $table->text('original_url');
            });

            foreach ($this->originalUrls as $id => $originalUrl) {
                DB::table('notifications_redirect_url_backup')->insert([
                    'notification_id' => $id,
                    'original_url' => $originalUrl,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('notifications_redirect_url_backup')) {
            $backups = DB::table('notifications_redirect_url_backup')->get();

            foreach ($backups as $backup) {
                DB::table('notifications')
                    ->where('id', $backup->notification_id)
                    ->update(['redirect_url' => $backup->original_url]);
            }

            Schema::dropIfExists('notifications_redirect_url_backup');
        }
    }
};
