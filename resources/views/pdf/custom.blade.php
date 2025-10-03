@php

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

    function quoteQuantityPrice($price_plan, $items, $operation = 'simple')
    {
        $filteredItems = array_filter($items, function ($item) use ($price_plan) {
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
        if ($temp < 1000) {
            $result = $number . ' ' . $suffix;
        } else {
            $result = ceil($number / $rounding) * $rounding . ' ' . $suffix;
        }

        return $result;
    }

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
    $blockedColumns = ['margin'];
    $columns = array_filter($columns, function ($item) use ($blockedColumns) {
        return !in_array($item['value'], $blockedColumns);
    });

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
                <p>{{ $quote['account']['name'] }}<br>{{ $quote['address_data']['label'] ?? '' }}</p>
                <p>Tel: +44 (0) 191 300 700</p>
                <p>Email: sales@example.com</p>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col">
                <div class="d-flex">
                    <h1 class="mb-1">Quote Q-{{ $quote['id'] }}</h1>
                    @if ($quote['status'] === 'EXPIRED')
                        <span class="badge border border-secondary text-secondary opacity-75 ms-4 lh-sm my-auto">
                            {{ $quote['status'] }}
                        </span>
                    @endif
                </div>
                <div class="row mb-3">
                    <div class="col">
                        <p>
                            Quotation Date: {{ \Carbon\Carbon::parse($quote['created_at'])->format('d/m/Y') }}<br />
                            Valid Date:
                            {{ \Carbon\Carbon::parse($quote['created_at'])->addDays((int) ($tenant['configurations']['quote_expiration_days'] ?? 30))->format('d/m/Y') }}
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
                                    @foreach ($columns as $column)
                                        <th class="column-{{ $column['value'] }}-header">
                                            {{ $column['name'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @if ($group)
                                    @foreach ($items as $key => $content)
                                        <tr class="group-row">
                                            <td colspan="{{ count($columns) }}">
                                                <p class="m-0 me-3 py-2">
                                                    @if ($groupName)
                                                        <span>{{ $groupName }} - </span>
                                                    @endif
                                                    {{ $content['name'] }}
                                                </p>
                                            </td>
                                        </tr>
                                        @foreach ($content['items'] as $item)
                                            <tr class="child-bg">
                                                @foreach ($columns as $column)
                                                    <td class="column-{{ $column['value'] }}-body ">
                                                        @component('components.table-item-custom', ['component' => $column['value'], 'item' => $item])
                                                        @endcomponent
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach ($items as $item)
                                        <tr>
                                            @foreach ($columns as $column)
                                                <td class="column-{{ $column['value'] }}-body ">
                                                    @component('components.table-item-custom', ['component' => $column['value'], 'item' => $item])
                                                    @endcomponent
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
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
                                {{-- <th>VAT (20%)</th> --}}
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
                                {{-- <td id="total_monthly_vat">
                                    £{{ number_format(quoteQuantityPrice('MONTHLY', $quote['items'], 'tax'), 2) }}
                                </td> --}}
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
                                {{-- <td id="total_single_vat">
                                    £{{ number_format(quoteTotal('SINGLE', 'total_tax', $quote['items']), 2) }}
                                </td> --}}
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
                                {{-- <td id="total_vat">
                                    £{{ number_format($quote['total_tax'], 2) }}
                                </td> --}}
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
