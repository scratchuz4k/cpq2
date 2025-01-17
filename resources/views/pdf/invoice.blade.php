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

function quoteTotal($key, $items)
{
    $filteredItems = array_filter($items, function ($item) use ($key) {
        return isset($item['price_plan']) && $item['price_plan']['code'] == $key;
    });

    $total = array_reduce(
        $filteredItems,
        function ($acc, $item) {
            return $acc + $item['total'];
        },
        0,
    );

    return $total;
}

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        :root {
            --pdf-header-logo-width: 30px;
            --pdf-footer-logo-width: 20px;
            --pdf-bg: #ffffff;
            --pdf-color: #16c095;
            --pdf-header-bg: #eeeeee;
            --pdf-header-border-color: #f4a9a9;
            --pdf-footer-bg: #eeeeee;
            --pdf-footer-border-color: #999999;
            --pdf-table-color: #000000;
            --pdf-table-totals-bg: #eeeeee;
            --pdf-table-grand-total-bg: #000000;
            --pdf-main-color: #000000;
            --pdf-terms-color: #ff0000;
            --pdf-header-border-width: 10px;
            --pdf-footer-border-width: 10px;
        }

        body {
            background: var(--pdf-bg);
            color: var(--pdf-color);
            font-family: arial;
            font-size: 14px;
        }

        html,
        body,
        div,
        p,
        span,
        li,
        ul,
        ol,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            padding: 0px;
            margin: 0px;
        }

        th,
        td {
            color: var(--pdf-table-color);
        }

        .text-right {
            text-align: right;
        }

        .row-totals {
            background: var(--pdf-table-totals-bg);
        }

        .row-totals-grand {
            background: var(--pdf-table-grand-total-bg);
        }

        #header {
            background: var(--pdf-header-bg);
            border-bottom: var(--pdf-header-border-color) var(--pdf-header-border-width) solid;
            padding: 30px;
            margin-bottom: 30px;
        }

        #header_right {
            font-size: 12px;
        }

        #main {
            color: var(--pdf-main-color);
        }

        #terms {
            color: var(--pdf-terms-color);
        }

        #footer {
            background: var(--pdf-footer-bg);
            border-bottom: var(--pdf-footer-border-color) var(--pdf-footer-border-width) solid;
            padding: 30px;
            margin-bottom: 30px;
            font-size: 12px;
        }

        .w-5 {
            width: 5% !important;
        }
        .w-10 {
            width: 10% !important;
        }
        .w-20 {
            width: 20% !important;
        }

        .tenant-logo{
            display: flex;
            flex-direction: column;
            height: 75px;
            width: 200px;
        }
        .tenant-logo>img{
            max-height: 75px;
            max-width: 200px;
            object-fit: contain;
            object-position: left;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row" id="header">
            @if($tenant->logo?->route)
                <div class="col" id="header_left">
                    <div class="tenant-logo">
                        <img src="{{ storage_path('app/public/images/').str_replace('storage/images/', '' , $tenant->logo?->route) }}" alt="{{ $tenant->name }}">
                    </div>
                </div>
            @endif
            <div class="col text-end" id="header_right">
                <p>{{ $quote['account']->name }}<br>{{ $quote['address_data']?->label }}</p>
                <p>Tel: +44 (0) 191 300 700</p>
                <p>Email: sales@example.com</p>
            </div>
        </div>
        <div class="cpq-summary row px-3" id="main">
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
                        <h2>BILL FROM</h2>
                        <p>Name Surname<br>
                            Company address, city, state, post code<br>
                            sales@example.com<br>
                            +44 (0) 123 456 7890</p>
                    </div>
                    <div class="col">
                        <h2>BILL TO</h2>
                        <p>Name Surname<br>
                            Company address, city, state, post code<br>
                            contact@account.test<br>
                            +44 (0) 234 567 8910</p>
                    </div>
                </div>
                <div class="panel">
                    <div class="row mb-4" style="min-height: 50vh;">
                        <div class="col">
                            <h3>Recurring charges</h3>
                            <table class="table">
                                <!-- Headers -->
                                <thead>
                                    <tr>
                                        <th class="w-5">ID</th>
                                        @if ($quote['solution_type_id'] === 1)
                                            <th class="text-center d-xl-table-cell w-5">Provider</th>
                                        @endif
                                        <th class="w-auto">Product</th>
                                        <th class="text-center w-5">Months</th>
                                        @if ($quote['solution_type_id'] === 1)
                                            <th class="d-xl-table-cell text-center w-10" style="white-space: nowrap;">
                                                Supplier Quote Ref.</th>
                                        @endif
                                        <th class="text-center w-10">Type</th>
                                        <th class="text-center w-5">Qty</th>
                                        <th class="text-center w-10">Price</th>
                                        <th class="text-end w-10" style="white-space: nowrap;">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($monthly_items as $key => $item)
                                        <tr class="item-row" style="vertical-align: middle;">
                                            <td class="position-relative">
                                                {{ $key + 1 }}
                                            </td>
                                            @if ($quote['solution_type_id'] === 1)
                                                <td class="text-center">
                                                    @if ($item['data']?->providerKey)
                                                        <img class="img-provider"
                                                        style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                        src="{{ $item['image'] ?? public_path() . '/images/providers/' . $item['data']?->providerKey . '.png' }}" />
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                {{ $item['name'] }}
                                                @if (isset($item['data']->additionalInfo->{'Primary Router Model'}))
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <i class="fa-solid fa-plug"></i>
                                                        {{ $item['data']->additionalInfo->{'Primary Router Model'} }}
                                                    </span>
                                                @endif
                                                @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                    <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                          data-bs-toggle="tooltip" data-bs-placement="top" title="Contended, wires only full fibre service with symmetrical upload and download speeds" v-tooltip>
                                                          Contended Service
                                                        </span>
                                                @endif
                                                @if (isset($item['data']->additionalInfo->{'Type'}) && $item['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                    <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                          data-bs-toggle="tooltip" data-bs-placement="top" title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps" v-tooltip>
                                                          Burstable Service
                                                        </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{ $item['contract_length'] }}
                                            </td>
                                            @if ($quote['solution_type_id'] === 1)
                                                <td class="d-xl-table-cell text-center">
                                                    {{ $item['data']?->productCode }}
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                Monthly
                                            </td>
                                            <td class="text-center">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="text-center">
                                                £{{ number_format($item['price'], 2) }}
                                            </td>
                                            <td class="text-end">
                                                £{{ number_format($item['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <br>
                            <h3>One off charges</h3>
                            <table class="table">
                                <!-- Headers -->
                                <thead>
                                    <tr>
                                        <th class="w-5">ID</th>
                                        @if ($quote['solution_type_id'] === 1)
                                            <th class="text-center d-xl-table-cell w-5">Provider</th>
                                        @endif
                                        <th class="w-auto">Product</th>
                                        @if ($quote['solution_type_id'] === 1)
                                            <th class="d-xl-table-cell w-10 text-center" style="white-space: nowrap;">
                                                Supplier
                                                Quote Ref.</th>
                                        @endif
                                        <th class="text-center w-10">Type</th>
                                        <th class="text-center w-5">Qty</th>
                                        <th class="text-center w-10">Price</th>
                                        <th class="text-end w-10" style="white-space: nowrap;">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($single_items as $key => $item)
                                        <tr class="item-row" style="vertical-align: middle;">
                                            <td claszooms="position-relative">
                                                {{ $key + 1 }}
                                            </td>
                                            @if ($quote['solution_type_id'] === 1)
                                                <td class="text-center">
                                                    @if ($item['data']?->providerKey)
                                                        <img class="img-provider"
                                                            style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                            src="{{ $item['image'] ?? public_path() . '/images/providers/' . $item['data']?->providerKey . '.png' }}" />
                                                    @endif
                                                </td>
                                            @endif
                                            <td>
                                                {{ $item['name'] }}
                                                @if (isset($item['data']->additionalInfo->{'Primary Router Model'}))
                                                    <span
                                                        class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model">
                                                        <i class="fa-solid fa-plug"></i>
                                                        {{ $item['data']->additionalInfo->{'Primary Router Model'} }}
                                                    </span>
                                                @endif
                                                @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                    <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                          data-bs-toggle="tooltip" data-bs-placement="top" title="Contended, wires only full fibre service with symmetrical upload and download speeds" v-tooltip>
                                                          Contended Service
                                                        </span>
                                                @endif
                                                @if (isset($item['data']->additionalInfo->{'Type'}) && $item['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                    <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                          data-bs-toggle="tooltip" data-bs-placement="top" title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps" v-tooltip>
                                                          Burstable Service
                                                        </span>
                                                @endif
                                            </td>
                                            @if ($quote['solution_type_id'] === 1)
                                                <td class="d-xl-table-cell text-center">
                                                    {{ $item['data']?->productCode }}
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                Single
                                            </td>
                                            <td class="text-center">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="text-center">
                                                £{{ number_format($item['price'], 2) }}
                                            </td>
                                            <td class="text-end">
                                                £{{ number_format($item['total'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <br>
                            <br>
                            <div class="text-right">
                                <div class="row">
                                    <div class="col"></div>
                                    <div class="col">
                                        <table class="table">
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
                                                    <th>Total Monthly</th>
                                                    <td id="total_monthly_net" class="text-end">
                                                        £{{ number_format(quoteTotal('MONTHLY', $quote['items']), 2) }}
                                                    </td>
                                                    <td id="total_monthly_vat" class="text-end">
                                                        £{{ number_format(quoteTotal('MONTHLY', $quote['items']) * 0.2, 2) }}
                                                    </td>
                                                    <td id="total_monthly_gross" class="text-end">
                                                        £{{ number_format(quoteTotal('MONTHLY', $quote['items']) * 1.2, 2) }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Total One-off Costs</th>
                                                    <td id="total_net" class="text-end">
                                                        £{{ number_format($quote['total'], 2) }}
                                                    </td>
                                                    <td id="total_vat" class="text-end">
                                                        £{{ number_format($quote['total_vat'], 2) }}
                                                    </td>
                                                    <td id="total_gross" class="text-end">
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
                </div>
            </div>
        </div>
        <div class="row px-3 mb-4" id="terms">
            <div class="col">
                <p>Terms & Conditions:</p>
                <p>Lorem ipsum dolor sit amet...</p>
            </div>
        </div>
        <div class="row mb-2" id="footer">

            @if($tenant->logo?->route)
                <div class="col">
                    <div class="tenant-logo">
                        <img src="{{ storage_path('app/public/images/').str_replace('storage/images/', '' , $tenant->logo?->route) }}" alt="{{ $tenant->name }}">
                    </div>
                </div>
            @endif
            <div class="col text-end">
                <p>Lorem ipsum dolor sit amet...</p>
            </div>
        </div>
    </div>
</body>

</html>
