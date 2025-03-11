<?php

namespace App\Helpers\Files;

use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;


class BlacklistFile
{
    private $path = '';
    private $filename = '';
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
        $this->filename = 'blacklist_' . md5($tenant?->name) . '.json';
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

    public function getBlacklistFilters(): array
    {
        $blacklist_items = $this->getContent()['data'];

        foreach ($blacklist_items as &$blacklist_item) {
            $blacklist_filters = [];
            foreach ($blacklist_item as $column => $value) {
                if ($value && in_array($column, $this->filterable_columns)) {
                    if (is_array($value)) {
                        if (!is_null($value['value'])) {
                            $blacklist_filters[] = [
                                'column' => $column,
                                'operator' => $this->getInvertedOperator($value['operator']),
                                'value' => $value['unit'] === 'Mb' ? $value['value'] : $value['value'] * 1000                            ];
                        }
                        break;
                    }
                    $blacklist_filters[] = [
                        'column' => $column,
                        'operator' => 'not_in',
                        'value' => $value
                    ];
                }
            }
            $blacklist_item['filters'] = $blacklist_filters ?? [];
        }
        return $blacklist_items;
    }

    public function getInvertedOperator($operator): string
    {
        $array = [
            'equals' => 'not_equals',
            'greater_than' => 'less_than_or_equal_to',
            'less_than' => 'greater_than_or_equal_to',
            'greater_than_or_equal_to' => 'less_than',
            'less_than_or_equal_to' => 'greater_than'
        ];

        return $array[$operator];
    }
}
