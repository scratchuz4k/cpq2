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
    function getCss()
    {
        $directory = 'build/assets/';
        if (!is_dir($directory)) {
            return null;
        }
        $files = scandir($directory);
        foreach ($files as $file) {
            if (
                is_file($directory . '/' . $file) &&
                pathinfo($file, PATHINFO_EXTENSION) === 'css' &&
                strpos($file, 'app-') === 0
            ) {
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

        if ($temp < 100) {
            $number = ceil($temp / 10) * 10;
        } elseif ($temp < 1000) {
            $number = ceil($temp / 100) * 100;
        } else {
            $number = round($temp / 1000, 1);
            $suffix = 'Gb';
        }

        return $number . ' ' . $suffix;
    }

    $isConnectivity = !is_null($quote['solution_type']) && $quote['solution_type']['code'] == 3 ? true : false;

    // $quote['items']  = getLeafNodesWithChildren($quote['items']);

    $warningProviders = [
        'its' => array_reduce(
            $quote['items'],
            function ($carry, $item) {
                return $carry || (isset($item['data']) && $item['data']->providerName === 'ITS');
            },
            false,
        ),
        'pxc' => array_reduce(
            $quote['items'],
            function ($carry, $item) {
                return $carry || (isset($item['data']) && $item['data']->providerName === 'PXC');
            },
            false,
        ),
    ];

@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote #{{ $quote['id'] }}</title>
    <link href="{{ getCss() }}" rel="stylesheet">
    <style>
        body {
            background: none !important;
            -webkit-print-color-adjust: exact !important;
        }

        html {
            -webkit-print-color-adjust: exact;
        }

        .child-bg>td {
            background-color: whitesmoke;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
    <div id="print">
        <div class="row" id="header">
            <div class="col" id="header_left">
                @if (!is_null($tenant['logo']))
                    <div class="preview-logo">
                        <img src="{{ public_path($tenant['logo']['route']) }}" alt="{{ $tenant['name'] }}"
                            class="img-fluid" />
                    </div>
                @endif
            </div>
            <div class="col text-end" id="header_right">
                <p>{{ $quote['account']->name }}<br>{{ $quote['address_data']?->label }}</p>
                <p>Tel: +44 (0) 191 300 700</p>
                <p>Email: sales@example.com</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <h1 class="mb-1">Quote Q-{{ $quote['id'] }}</h1>
                <div class="row mb-3">
                    <div class="col">
                        <p>
                            Quotation Date: {{ date('d/m/Y') }}<br>
                            Valid Until: {{ date('d/m/Y', strtotime('+30 days')) }}
                        </p>
                    </div>
                </div>
                @if (count(array_filter($warningProviders)) > 0)
                    <div class="row">
                        <div class="col">
                            <InfoBox class="m-4 p-0" severity="error">
                                <div class="small">
                                    <div class="mb-2 fw-bold" style="color:#dc3545">Important Information:</div>
                                    @foreach ($warningProviders as $providerName => $hasWarning)
                                        @if ($hasWarning)
                                            <div class="mb-2" style="color:#dc3545; line-height: 1.2">
                                                <span
                                                    class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize">
                                                    {{ strtoupper($providerName) }}
                                                </span>
                                                <i>
                                                    Please note, quote references for
                                                    {{ strtoupper($providerName) }} quotes cannot currently
                                                    be used to place an order. To obtain a valid quote for ordering
                                                    please generate the quote directly on the
                                                    {{ strtoupper($providerName) }}
                                                    portal.
                                                </i>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                        </div>
                        </InfoBox>
                    </div>
            </div>
            @endif
            <div class="row mb-4">
                <div class="col">
                    <div class="mb-5">
                        <table id="table-recurrring" class="table table-dealmaker table-bordered  ">
                            <!-- Headers -->
                            <thead>
                                <tr>
                                    @if ($isConnectivity)
                                        <th class="column-quote_ref-header w-20">Quote Reference</th>
                                        <th class="column-speed-header w-20">Speed</th>
                                        <th class="column-bearer-header w-20">Bearer</th>
                                    @else
                                        <th class="column-name-header w-20">Name</th>
                                    @endif
                                    <th class="column-contract_lenght-header w-5">Months</th>
                                    <th class="column-price_plan-header w-20">Price Plan</th>
                                    <th class="column-quantity-header w-5">Quantity</th>
                                    <th class="column-price-header w-10">Price</th>
                                    <th class="column-total-header">Total </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($quote['items'] as $key => $group)
                                    @if ($isConnectivity)
                                        <tr class="group-row" style="vertical-align: middle;">
                                            <td class="group-body" colspan="8">
                                                <div class="d-flex py-2">
                                                    <div class="me-3">
                                                        @if ($group['data']?->providerKey)
                                                            <img class="img-provider"
                                                                style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                                src="{{ $group['image'] ?? public_path() . '/images/providers/' . $group['data']?->providerKey . '.png' }}" />
                                                        @endif
                                                    </div>
                                                    <div class="me-3">
                                                        @if ($group['data']?->connectivitySupplierGroup)
                                                            <img class="img-provider"
                                                                style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                                src="{{ $group['image'] ?? public_path() . '/images/providers/' . str_replace(' ', '', strtolower($group['data']?->connectivitySupplierGroup)) . '.png' }}" />
                                                        @endif
                                                    </div>
                                                    <div class="me-3 d-flex">
                                                        <p class="m-0">{{ $group['name'] }}
                                                            @if (isset($group['data']->additionalInfo->{'Primary Router Model'}))
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                                    <i class="fa-solid fa-plug"></i>
                                                                    {{ $group['data']->additionalInfo->{'Primary Router Model'} }}
                                                                </span>
                                                            @endif
                                                            @if (isset($group['data']->isContended) and $group['data']->isContended)
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                                    v-tooltip>
                                                                    Contended Service
                                                                </span>
                                                            @endif
                                                            @if (isset($group['data']->additionalInfo->{'Type'}) && $group['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                                    v-tooltip>
                                                                    Burstable Service
                                                                </span>
                                                            @endif
                                                        <p>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @if (count($group['children']))
                                            @foreach ($group['children'] as $key => $item)
                                                <tr class="item-row child-bg" style="vertical-align: middle;">
                                                    <td class="column-quote_ref-body" style="">
                                                        {{ $item['data']?->quoteId }}
                                                    </td>
                                                    <td class="column-speed-body">
                                                        {{ formatBytes($item['data']?->speed) }}
                                                    </td>
                                                    <td class="column-bearer-body">
                                                        {{ formatBytes($item['data']?->bearer) }}
                                                    </td>
                                                    <td class="column-price_plan-body">
                                                        {{ $item['price_plan']?->name }}
                                                    </td>
                                                    <td class="column-contract_length-body">
                                                        {{ $item['price_plan']['code'] == 'MONTHLY' ? $item['contract_length'] : '' }}
                                                    </td>
                                                    <td class="column-quantity-body">
                                                        {{ $item['quantity'] }}
                                                    </td>
                                                    <td class="column-price-body">
                                                        £{{ number_format($item['price'], 2) }}
                                                    </td>
                                                    <td class="column-total-body">
                                                        £{{ number_format($item['total'], 2) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="item-row child-bg" style="vertical-align: middle;">
                                                <td class="column-quote_ref-body">
                                                    {{ $group['data']?->quoteId }}
                                                </td>
                                                <td class="column-speed-body">
                                                    {{ formatBytes($group['data']?->speed) }}
                                                </td>
                                                <td class="column-bearer-body">
                                                    {{ formatBytes($group['data']?->bearer) }}
                                                </td>
                                                <td class="column-price_plan-body">
                                                    {{ $group['price_plan']?->name }}
                                                </td>
                                                <td class="column-contract_length-body">
                                                    {{ $group['contract_length'] > 1 ? $group['contract_length'] : '' }}
                                                </td>
                                                <td class="column-quantity-body">
                                                    {{ $group['quantity'] }}
                                                </td>
                                                <td class="column-price-body">
                                                    £{{ number_format($group['price'], 2) }}
                                                </td>
                                                <td class="column-total-body">
                                                    £{{ number_format($group['total'], 2) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @else
                                        <tr class="item-row" style="vertical-align: middle;">
                                            <td class="column-quote_ref-body">
                                                {{ $group['name'] }}
                                                @if (isset($group['data']->additionalInfo->{'Primary Router Model'}))
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <i class="fa-solid fa-plug"></i>
                                                        {{ $group['data']->additionalInfo->{'Primary Router Model'} }}
                                                    </span>
                                                @endif
                                                @if (isset($group['data']->isContended) and $group['data']->isContended)
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                        v-tooltip>
                                                        Contended Service
                                                    </span>
                                                @endif
                                                @if (isset($group['data']->additionalInfo->{'Type'}) && $group['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                        v-tooltip>
                                                        Burstable Service
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="column-price_plan-body">
                                                {{ $group['price_plan']?->name }}
                                            </td>
                                            <td class="column-contract_length-body">
                                                {{ $group['contract_length'] > 1 ? $group['contract_length'] : '' }}
                                            </td>
                                            <td class="column-quantity-body">
                                                {{ $group['quantity'] }}
                                            </td>
                                            <td class="column-price-body">
                                                £{{ number_format($group['price'], 2) }}
                                            </td>
                                            <td class="column-total-body">
                                                £{{ number_format($group['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endif
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
                                    £{{ number_format(quoteQuantityPrice('MONTHLY', $quote['items']), 2) }}
                                </td>
                                <td id="total_monthly_vat">
                                    £{{ number_format(quoteQuantityPrice('MONTHLY', $quote['items'], 'tax'), 2) }}
                                </td>
                                <td id="total_monthly_gross">
                                    £{{ number_format(quoteQuantityPrice('MONTHLY', $quote['items'], 'total'), 2) }}
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

    </div>
</body>

</html>
