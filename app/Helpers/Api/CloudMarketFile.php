<?php

namespace App\Helpers\Api;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class CloudMarketFile
{
    public function __construct(
        public $path,
        public $filename,
    ) {}

    public function write(array $data, bool $wating_task, $finished_at, $created_at, $filters = [])
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $dataString = json_encode([
            'data' => $data,
            'total' => count($data),
            'wating_task' => $wating_task,
            'created_at' => $created_at,
            'finished_at' => $finished_at,
            'filters' => $filters,
        ]);

        Cache::put($this->filename, json_encode(['updated_at' => now()]), 86400);
        file_put_contents($this->path . $this->filename, $dataString);
    }

    public function exists(): bool
    {
        return file_exists($this->path . $this->filename);
    }

    public function getContent()
    {
        if (!$this->exists()) {
            return [
                'data' => [],
                'total' => null,
                'wating_task' => null,
                'created_at' => null,
                'finished_at' => null,
                'filters' => null,
            ];
        }
        return json_decode(file_get_contents($this->path . $this->filename), true);
    }
}
