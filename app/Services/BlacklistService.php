<?php

namespace App\Services;

use App\Enums\ComparisonOperatorsEnum;
use App\Helpers\Files\BlacklistFile;
use App\Helpers\FilterProvider;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

class BlacklistService
{
    public function index()
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();
        $filters = request()->input('filters', []);
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;
        $items = FilterProvider::get($data['data'], $filters);

        return FilterProvider::paginate($items);
    }

    public function store(array $array)
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();
        $id = count($data['data']) > 0 ? intval(max(array_keys($data['data']))) + 1 : 1;

        $data['data'][$id] = [
            'id' => $id,
            ...$array,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $blacklist->write(
            $data['data'],
            $data['created_at'] ?? now(),
            now()
        );
    }

    public function edit(string $id)
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();

        return $data['data'][$id];
    }

    public function update(array $array)
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();
        $item = $data['data'][$array['id']];

        $item = array_merge($item, array_intersect_key(
            [
                'id' => $array['id'],
                ...$array,
                'updated_at' => now(),
            ],
            $item
        ));

        $data['data'][$array['id']] = $item;

        $blacklist->write(
            $data['data'],
            $data['created_at'] ?? now(),
            now()
        );
    }


    public function destroy(string $id)
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();

        unset($data['data'][$id]);

        if (count($data['data']) > 0) {
            $blacklist->write(
                $data['data'],
                $data['created_at'] ?? now(),
                now()
            );

            return;
        }

        $blacklist->delete();
    }

    public function export(array $array)
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();
        $filters = $array['filters'] ?? [];
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;
        $data = FilterProvider::get($data['data'], $filters);

        $data = $data->map(function ($item) {
            $item['speed'] = $this->parseSpeedAndBearer($item['speed']);
            $item['bearer'] = $this->parseSpeedAndBearer($item['bearer']);
            return $item;
        });

        return new Collection($data);
    }

    public function parseSpeedAndBearer($item)
    {
        if (is_null($item['value'])) {
            return null;
        }

        if (is_array($item['value'])) {
            return ComparisonOperatorsEnum::tryFromName($item['operator'])->description()
                . ' '
                . $item['value'][0]
                . ' and '
                . $item['value'][1]
                . ' '
                . $item['unit'];
        }

        return ComparisonOperatorsEnum::tryFromName($item['operator'])->description()
            . ' '
            . $item['value']
            . ' '
            . $item['unit'];
    }
}
