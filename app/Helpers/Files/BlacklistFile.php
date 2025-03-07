<?php

namespace App\Helpers\Files;

use Illuminate\Support\Facades\Cache;


class BlacklistFile
{
    public function __construct(
        public $path,
        public $filename,
    ) {}

    public function write(array $data, $created_at, $updated_at)
    {
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777, true);
        }

        $dataString = json_encode([
            'data' => $data,
            'total' => count($data),
            'created_at' => $created_at,
            'updated_at' => $updated_at
        ]);

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
                'created_at' => null,
                'finished_at' => null
            ];
        }
        return json_decode(file_get_contents($this->path . $this->filename), true);
    }
}
