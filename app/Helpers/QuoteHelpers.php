<?php

if (! function_exists('findLeaves')) {
    function findLeaves(array $items, &$leaves)
    {
        foreach ($items as $item) {
            if (isset($item['children']) && is_array($item['children']) && count($item['children']) > 0) {
                findLeaves($item['children'], $leaves);
            } else {
                $leaves[] = $item;
            }
        }
    }
}
if (! function_exists('getLeafNodesWithChildren')) {
    function getLeafNodesWithChildren(array $objects)
    {
        $leaves = [];
        findLeaves($objects, $leaves);

        return $leaves;
    }
}
if (! function_exists('quoteTotal')) {
    function quoteTotal($price_plan, $column, $items)
    {
        $temp = getLeafNodesWithChildren($items);
        $filteredItems = array_filter($temp, function ($item) use ($price_plan) {
            return isset($item['price_plan']) && $item['price_plan']['code'] == $price_plan;
        });

        $total = array_reduce(
            $filteredItems,
            function ($acc, $item) use ($column) {
                return $acc + $item[$column];
            },
            0,
        );

        return $total;
    }
}
if (! function_exists('quoteQuantityPrice')) {
    function quoteQuantityPrice($price_plan, $items, $operation = 'simple')
    {
        $temp = getLeafNodesWithChildren($items);
        $filteredItems = array_filter($temp, function ($item) use ($price_plan) {
            return isset($item['price_plan']) && $item['price_plan']['code'] == $price_plan;
        });

        $total = array_reduce(
            $filteredItems,
            function ($acc, $item) {
                $acc += $item['quantity'] * $item['price'];

                return $acc;
            },
            0,
        );
        $tax = array_reduce(
            $filteredItems,
            function ($acc, $item) {
                $acc += $item['quantity'] * $item['price'] * $item['tax'];

                return $acc;
            },
            0,
        );

        if ($operation == 'tax') {
            return $tax;
        }
        if ($operation == 'total') {
            return $total + $tax;
        }

        return $total;
    }
}
if (! function_exists('frequencyLabel')) {
    function frequencyLabel($value, $unit)
    {
        if (! $value || ! $unit) {
            return '';
        }

        $abbreviations = [
            'DAYS' => 'Days',
            'MONTHS' => 'Months',
            'YEARS' => 'Years',
        ];

        return $value.($abbreviations[$unit] ?? '');
    }
}
if (! function_exists('getCss')) {
    function getCss()
    {
        $directory = 'build/assets/';
        if (! is_dir($directory)) {
            return null;
        }
        $files = scandir($directory);
        foreach ($files as $file) {
            if (
                is_file($directory.'/'.$file) &&
                pathinfo($file, PATHINFO_EXTENSION) === 'css' &&
                strpos($file, 'app-') === 0
            ) {
                return public_path('build/assets/'.$file);
            }
        }

        return null;
    }
}
if (! function_exists('formatBytes')) {
    function formatBytes($value)
    {
        $temp = $value ?? 0;
        $number = $temp;
        $suffix = 'Mbps';

        if ($temp < 100) {
            $number = ceil($temp / 10) * 10;
        } elseif ($temp < 1000) {
            $number = ceil($temp / 100) * 100;
        } else {
            $number = round($temp / 1000, 1);
            $suffix = 'Gbps';
        }

        return $number.' '.$suffix;
    }
}
