@php
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

    function getLeafNodesWithChildren(array $objects)
    {
        $leaves = [];
        findLeaves($objects, $leaves);
        return $leaves;
    }

    function quoteTotal($price_plan, $column, $items)
    {
        $filteredItems = array_filter($items, function ($item) use ($price_plan) {
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
    function getCss()
    {
        $directory = 'build/assets/';
        if (!is_dir($directory)) {
            return null;
        }
        $files = scandir($directory);
        foreach ($files as $file) {
            if (is_file($directory . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                return public_path('build/assets/' . $file);
            }
        }
        return null;
    }

    function formatBytes($value)
    {
        $temp = $value ?? 0;
        $number = $temp;
        $suffix = 'Mb';
        $rounding = 10;

        if ($temp < 1000) {
            $number = $temp;
        } elseif ($temp < 10000) {
            $number = $temp / 1000;
            $suffix = 'Gb';
            $rounding = 1;
        } else {
            $number = $temp / 1000;
            $suffix = 'Gb';
            $rounding = 1;
        }

        // Remove rounding if value < 1000
        if ($value < 1000) {
            $result = $number . ' ' . $suffix;
        } else {
            $result = ceil($number / $rounding) * $rounding . ' ' . $suffix;
        }

        return $result;
    }

    $isConnectivity = !is_null($quote['solution_type']) && $quote['solution_type']['code'] == 3 ? true : false;

    $leafNodes = getLeafNodesWithChildren($quote['items']);
    $quote['items'] = $leafNodes;

    $single_items = array_filter($quote['items'], function ($item) {
        return isset($item['price_plan']) && $item['price_plan']->code === 'SINGLE';
    });

    $monthly_items = array_filter($quote['items'], function ($item) {
        return isset($item['price_plan']) && $item['price_plan']->code === 'MONTHLY';
    });

@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote #{{ $quote['id'] }}</title>
    <link href="{{ getCss() }}" rel="stylesheet">
</head>

<body>
    <div id="print">
        <div class="row" id="header">
            <div class="col" id="header_left">
                <img src="https://img.logoipsum.com/331.svg" alt="Sample Logo" class="img-fluid">
                <p>Insert Tagline</p>
            </div>
            <div class="col text-end" id="header_right">
                <p>{{ $quote['account']->name }}<br>{{ $quote['address_data']?->label }}</p>
                <p>Tel: +44 (0) 191 300 700</p>
                <p>Email: sales@example.com</p>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <h1 class="mb-3">Quote Q-{{ $quote['id'] }}</h1>
                <div class="row mb-3">
                    <div class="col">
                        <p>Company address, city, state, post code<br>
                            sales@example.com<br>
                            +44 (0) 123 456 7890</p>
                    </div>
                    <div class="col">
                        <p>Quote #{{ $quote['id'] }}<br>
                            Quotation Date: {{ date('d/m/Y') }}<br>
                            Valid Date: {{ date('d/m/Y', strtotime('+7 days')) }}
                        </p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <p class="big bold">BILL FROM</p>
                        <p>Name Surname<br>
                            Company address, city, state, post code<br>
                            sales@example.com<br>
                            +44 (0) 123 456 7890</p>
                    </div>
                    <div class="col">
                        <p class="big bold">BILL TO</p>
                        <p>Name Surname<br>
                            Company address, city, state, post code<br>
                            contact@account.test<br>
                            +44 (0) 234 567 8910</p>
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col">
                        <div class="mb-5">
                            <div
                                class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                                <h3>Recurring charges</h3>
                                <h3 id="recurring_total">
                                    £{{ number_format(quoteTotal('MONTHLY', 'total', $quote['items']), 2) }}</h3>
                            </div>
                            <table id="table-MONTHLY" class="table table-dealmaker table-bordered  ">
                                <!-- Headers -->
                                <thead>
                                    <tr>
                                        @if ($isConnectivity)
                                            <th class="column-provider-header d-xl-table-cell w-5">Provider</th>
                                            <th class="column-subprovider-header d-xl-table-cell w-5">SubProvider</th>
                                        @endif
                                        <th class="column-name-header w-20">Name</th>
                                        <th class="column-contract_lenght-header w-5">Months</th>
                                        @if ($isConnectivity)
                                            <th class="column-speed-header w-5">Speed</th>
                                            <th class="column-bearer-header w-5">Bearer</th>
                                        @endif
                                        <th class="column-quantity-header w-5">Quantity</th>
                                        <th class="column-price-header w-10">Price</th>
                                        <th class="column-discount-header w-5">Discount</th>
                                        <th class="column-total-header text-end" style="white-space: nowrap;">Total
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthly_items as $key => $item)
                                        <tr class="item-row" style="vertical-align: middle;">
                                            @if ($isConnectivity)
                                                <td class="column-provider-body">
                                                    @if ($item['data']?->providerKey)
                                                        <img class="img-provider"
                                                            style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                            src="{{ $item['image'] ?? public_path() . '/images/providers/' . $item['data']?->providerKey . '.png' }}" />
                                                    @endif
                                                </td>
                                                <td class="column-subprovider-body ">
                                                    @if ($item['data']?->connectivitySupplierGroup)
                                                        <img class="img-provider"
                                                            style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                            src="{{ $item['image'] ?? public_path() . '/images/providers/' . strtolower($item['data']?->connectivitySupplierGroup) . '.png' }}" />
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="column-name-body">
                                                {{ $item['name'] }}
                                                @if (isset($item['data']->additionalInfo->{'Primary Router Model'}))
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <i class="fa-solid fa-plug"></i>
                                                        {{ $item['data']->additionalInfo->{'Primary Router Model'} }}
                                                    </span>
                                                @endif
                                                @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Contended" v-tooltip>
                                                        <i class="fa-solid fa-network-wired"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="column-contract_length-body">
                                                {{ $item['contract_length'] }}
                                            </td>
                                            @if ($isConnectivity)
                                                <td class="column-speed-body">
                                                    {{ formatBytes($item['data']?->speed) }}
                                                </td>
                                                <td class="column-bearer-body">
                                                    {{ formatBytes($item['data']?->bearer) }}
                                                </td>
                                            @endif
                                            <td class="column-quantity-body">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="column-price-body">
                                                £{{ number_format($item['price'], 2) }}
                                            </td>
                                            <td class="column-discount-body">
                                                {{ number_format((float) $item['discount']) }}%
                                            </td>
                                            <td class="column-total-body">
                                                £{{ number_format($item['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mb-5">
                            <div
                                class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                                <h3>One off charges</h3>
                                <h3 id="recurring_total">
                                    £{{ number_format(quoteTotal('SINGLE', 'total', $quote['items']), 2) }}</h3>
                            </div>
                            <table id="table-SINGLE" class="table table-dealmaker table-bordered">
                                <!-- Headers -->
                                <thead>
                                    <tr>
                                        @if ($isConnectivity)
                                            <th class="column-provider-header d-xl-table-cell w-5">Provider</th>
                                            <th class="column-subprovider-header d-xl-table-cell w-5">SubProvider</th>
                                        @endif
                                        <th class="column-name-header w-20">Name</th>
                                        @if ($isConnectivity)
                                            <th class="column-speed-header w-5">Speed</th>
                                            <th class="column-bearer-header w-5">Bearer</th>
                                        @endif
                                        <th class="column-quantity-header w-5">Quantity</th>
                                        <th class="column-price-header w-10">Price</th>
                                        <th class="column-discount-header w-5">Discount</th>
                                        <th class="column-total-header text-end" style="white-space: nowrap;">Total
                                            Price
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($single_items as $key => $item)
                                        <tr class="item-row" style="vertical-align: middle;">
                                            @if ($isConnectivity)
                                                <td class="column-provider-body">
                                                    @if ($item['data']?->providerKey)
                                                        <img class="img-provider"
                                                            style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                            src="{{ $item['image'] ?? public_path() . '/images/providers/' . $item['data']?->providerKey . '.png' }}" />
                                                    @endif
                                                </td>
                                                <td class="column-subprovider-body ">
                                                    @if ($item['data']?->connectivitySupplierGroup)
                                                        <img class="img-provider"
                                                            style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                            src="{{ $item['image'] ?? public_path() . '/images/providers/' . strtolower($item['data']?->connectivitySupplierGroup) . '.png' }}" />
                                                    @endif
                                                </td>
                                            @endif
                                            <td class="column-name-body">
                                                {{ $item['name'] }}
                                                @if (isset($item['data']->additionalInfo->{'Primary Router Model'}))
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <i class="fa-solid fa-plug"></i>
                                                        {{ $item['data']->additionalInfo->{'Primary Router Model'} }}
                                                    </span>
                                                @endif
                                                @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Contended" v-tooltip>
                                                        <i class="fa-solid fa-network-wired"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            @if ($isConnectivity)
                                                <td class="column-speed-body">
                                                    {{ formatBytes($item['data']?->speed) }}
                                                </td>
                                                <td class="column-bearer-body">
                                                    {{ formatBytes($item['data']?->bearer) }}
                                                </td>
                                            @endif
                                            <td class="column-quantity-body">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="column-price-body">
                                                £{{ number_format($item['price'], 2) }}
                                            </td>
                                            <td class="column-discount-body">
                                                {{ number_format((float) $item['discount']) }}%
                                            </td>
                                            <td class="column-total-body">
                                                £{{ number_format($item['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                            <h3>Totals</h3>
                        </div>
                        <table class="table table-dealmaker table-bordered text-end">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>NET</th>
                                    <th>VAT (20%)</th>
                                    <th>GROSS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>
                                        <p class="small text-start">Monthly Total</p>
                                    </th>
                                    <td id="total_monthly_net">
                                        £{{ number_format(quoteTotal('MONTHLY', 'total', $quote['items']), 2) }}
                                    </td>
                                    <td id="total_monthly_vat">
                                        £{{ number_format(quoteTotal('MONTHLY', 'total_tax', $quote['items']) * 0.2, 2) }}
                                    </td>
                                    <td id="total_monthly_gross">
                                        £{{ number_format(quoteTotal('MONTHLY', 'total_gross', $quote['items']) * 1.2, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <p class="small text-start">One Off Total</p>
                                    </th>
                                    <td id="total_single_net">
                                        £{{ number_format(quoteTotal('SINGLE', 'total', $quote['items']), 2) }}
                                    </td>
                                    <td id="total_single_vat">
                                        £{{ number_format(quoteTotal('SINGLE', 'total_tax', $quote['items']), 2) }}
                                    </td>
                                    <td id="total_single_gross">
                                        £{{ number_format(quoteTotal('SINGLE', 'total_gross', $quote['items']), 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>
                                        <p class="small text-start">Total</p>
                                    </th>
                                    <td id="total_net">
                                        £{{ number_format($quote['total'], 2) }}
                                    </td>
                                    <td id="total_vat">
                                        £{{ number_format($quote['total_tax'], 2) }}
                                    </td>
                                    <td id="total_gross">
                                        £{{ number_format($quote['total_gross'], 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row px-3 mb-4" id="terms">
            <div class="col">
                <p>Terms & Conditions:</p>
                <p>Lorem ipsum dolor sit amet...</p>
            </div>
        </div>

    </div>
</body>

</html>
