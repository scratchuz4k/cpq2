<?php

namespace App\Helpers\Files;

use App\Helpers\FilterProvider;
use App\Models\Tenant;
use Illuminate\Support\Collection;


class BlacklistFile
{
    private $path = '';
    private $filename = '';
    private $file = '';
    private $filterable_columns = [
        'productName',
        'providerName',
        'connectivitySupplierGroup',
        'bearer',
        'speed',
        'contractTermMonths'
    ];
    public function __construct(Tenant $tenant) {
        $this->path = storage_path('/app/blacklists/');
        $this->filename = 'blacklist_' . md5($tenant?->id.$tenant?->name) . '.json';
        $this->file = $this->path . $this->filename;
    }

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

        file_put_contents($this->file, $dataString);
    }

    public function exists(): bool
    {
        return file_exists($this->file);
    }

    public function delete()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
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
        return json_decode(file_get_contents($this->file), true);
    }

    public function getBlacklistFilters(): array
    {
        $blacklist_items = $this->getContent()['data'];
        $blacklist_filters = [];

        foreach ($blacklist_items as $blacklist_item) {
            foreach ($blacklist_item as $column => $value) {
                if ($value && in_array($column, $this->filterable_columns)) {
                    if (is_array($value)) {
                        if (!is_null($value['value'])) {
                            $blacklist_filters[$blacklist_item['id']][] = [
                                'column' => $column,
                                'operator' => $value['operator'],
                                'value' => $value['unit'] === 'Mb' ? $value['value'] : $value['value'] * 1000                            ];
                        }
                        continue;
                    }
                    $blacklist_filters[$blacklist_item['id']][] = [
                        'column' => $column,
                        'operator' => 'in',
                        'value' => $value
                    ];
                }
            }
        }
        return $blacklist_filters;
    }

    public function getBlacklistedData(Collection $data): Collection
    {
        $blacklisted_items = collect();
        foreach ($this->getBlacklistFilters() as $item_filter) {
            $blacklisted_items = $blacklisted_items->merge(FilterProvider::get($data->toArray(), $item_filter));
        }
        return $blacklisted_items;
    }
}
