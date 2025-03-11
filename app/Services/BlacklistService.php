<?php

namespace App\Services;


use App\Helpers\Files\BlacklistFile;
use App\Helpers\FilterProvider;
use App\Models\Tenant;

class BlacklistService
{

    public function index()
    {
        $blacklist = new BlacklistFile(Tenant::current());
        $data = $blacklist->getContent();
        $filters = request()->input('filters', []);
        $filters = is_string($filters) ? json_decode($filters, true) : $filters;
        $items = FilterProvider::get($data['data'], $filters);
        $meta = FilterProvider::paginate($items);

        return $meta;
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
            'updated_at' => now()
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
                'updated_at' => now()
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

        $blacklist->write(
            $data['data'],
            $data['created_at'] ?? now(),
            now()
        );
    }
}
