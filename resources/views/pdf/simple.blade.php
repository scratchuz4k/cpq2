@php
    $isConnectivity = !is_null($quote['solution_type']) && $quote['solution_type']['code'] == 3 ? true : false;

    // $quote['items']  = getLeafNodesWithChildren($quote['items']);

    $items = [];

    foreach ($quote['items'] as $key => $item) {
        $nr_groups = array_reduce(
            $quote['items'],
            function ($acc, $temp) use ($item) {
                if ($temp['group'] == $item['group']) {
                    $acc = $acc + 1;
                    return $acc;
                }
                return $acc;
            },
            0,
        );

        if ($nr_groups == 1) {
            array_push($items, [...$item, 'children' => []]);
            continue;
        }

        $index = array_search($item['group'], array_column($items, 'group'));

        if ($index === false) {
            array_push($items, [...$item, 'children' => [$item]]);
        } else {
            array_push($items[$index]['children'], $item);
        }
    }

    function getMainProductName($quote, $item)
    {
        if (!$item['data'] || $item['data']->type === 'main_product') {
            return null;
        }

        return collect($quote['items'])->first(
            fn($i) => $i['group'] == $item['group'] && $i['data'] && $i['data']->type == 'main_product',
        )['name'] ?? null;
    }
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

        .text-primary {
            color: var(--color-purple) !important;
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
                @if (!is_null($tenant['logo_docs']))
                    <div class="app-logo preview-logo">
                        <img src="{{ public_path($tenant['logo_docs']['route']) }}" alt="{{ $tenant['name'] }}" />
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
                    <h1 class="mb-1">Quote #{{ $quote['id'] }}</h1>
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
            </div>
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
                                    <th class="column-price_plan-header w-20">Price Plan</th>
                                    <th class="column-renewal_terms-header w-5">Renewal Term</th>
                                    <th class="column-billing_frequency-header w-5">Billing Frequency</th>
                                    <th class="column-quantity-header w-5">Quantity</th>
                                    <th class="column-price-header w-10">Price</th>
                                    <th class="column-total-header">Total </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($isConnectivity)
                                    @foreach ($items as $key => $group)
                                        <tr class="group-row" style="vertical-align: middle;">
                                            <td class="group-body" colspan="9">
                                                <div class="d-flex py-2">
                                                    <div class="me-3">
                                                        @if ($group['data']?->providerKey)
                                                            <img class="img-provider"
                                                                style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                                src="{{ $group['image'] ?? public_path() . '/images/providers/' . strtolower($group['data']?->providerKey) . '.png' }}"
                                                                onerror="this.src='/images/no_image.png'" />
                                                        @endif
                                                    </div>
                                                    <div class="me-3">
                                                        @if (isset($group['data']?->connectivitySupplierGroup))
                                                            <img class="img-provider"
                                                                style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                                src="{{ $group['image'] ?? public_path() . '/images/providers/' . str_replace(' ', '', strtolower($group['data']?->connectivitySupplierGroup)) . '.png' }}"
                                                                onerror="this.src='/images/no_image.png'" />
                                                        @endif
                                                    </div>
                                                    <div class="me-3 d-flex">
                                                        <p class="m-0">{{ $group['name'] }}
                                                            @if (isset($group['data']->additionalInfo->{'router'}))
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    data-bs-original-title="<b>Primary Router Model:</b> {{ $group['data']->additionalInfo->{'router'} }}"
                                                                    v-tooltip>
                                                                    <i class="fa-solid fa-plug"></i>

                                                                </span>
                                                            @endif
                                                            {{-- @if (isset($group['data']->isContended) and $group['data']->isContended)
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    title="<b>Contended Service:</b> Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                                    v-tooltip>
                                                                    <i class="fa-solid fa-server"></i>
                                                                </span>
                                                            @endif --}}
                                                            @if (isset($group['data']->additionalInfo->{'Type'}) && $group['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-burstable"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    title="<b>Burstable Service:</b> This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                                    v-tooltip>
                                                                    <i class="fa-solid fa-bolt"></i>
                                                                </span>
                                                            @endif
                                                            @if (isset($group['data']->additionalInfo->{'addon_ipv4'}))
                                                                <span
                                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    data-bs-html="true"
                                                                    title="{{ $group['data']->additionalInfo->{'addon_ipv4'} }}"
                                                                    v-tooltip>
                                                                    <i class="fa-solid fa-network-wired"></i>
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
                                                        @if (isset($item['data']?->speed))
                                                            {{ formatBytes($item['data']?->speed) }}
                                                        @endif
                                                    </td>
                                                    <td class="column-bearer-body">
                                                        @if (isset($item['data']?->bearer))
                                                            {{ formatBytes($item['data']?->bearer) }}
                                                        @endif
                                                    </td>
                                                    <td class="column-price_plan-body">
                                                        {{ $item['price_plan']?->name }}
                                                    </td>
                                                    <td class="column-renewal_terms-body">
                                                        {{ $item['price_plan']['code'] == 'MONTHLY' ? frequencyLabel($item['renewal_terms_value'], $item['renewal_terms_unit']) : '' }}
                                                    </td>
                                                    <td class="column-billing_frequency-body">
                                                        {{ $item['price_plan']['code'] == 'MONTHLY' ? frequencyLabel($item['billing_frequency_value'], $item['billing_frequency_unit']) : '' }}
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
                                                <td class="column-renewal_terms-body">
                                                    {{ $group['renewal_terms_value'] > 1 ? frequencyLabel($group['renewal_terms_value'], $group['renewal_terms_unit']) : '' }}
                                                </td>
                                                <td class="column-billing_frequency-body">
                                                    {{ $group['renewal_terms_value'] > 1 ? frequencyLabel($group['billing_frequency_value'], $group['billing_frequency_unit']) : '' }}
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
                                @else
                                    @foreach ($quote['items'] as $key => $group)
                                        <tr class="item-row" style="vertical-align: middle;">
                                            <td class="column-quote_ref-body">
                                                <p class="small semi-bold">
                                                    {{ $group['name'] }}
                                                    @if (isset($group['data']->additionalInfo->{'router'}))
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Primary Router Model" v-tooltip>
                                                            <i class="fa-solid fa-plug"></i>
                                                            {{ $group['data']->additionalInfo->{'router'} }}
                                                        </span>
                                                    @endif
                                                    {{-- @if (isset($group['data']->isContended) and $group['data']->isContended)
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                            v-tooltip>
                                                            Contended Service
                                                        </span>
                                                    @endif --}}
                                                    @if (isset($group['data']->additionalInfo->{'Type'}) && $group['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                            v-tooltip>
                                                            Burstable Service
                                                        </span>
                                                    @endif
                                                </p>
                                                @if (getMainProductName($quote, $group))
                                                    <p class="main-product-reference">
                                                        {{ getMainProductName($quote, $group) }}
                                                    </p>
                                                @endif
                                            </td>
                                            <td class="column-price_plan-body">
                                                {{ $group['price_plan']?->name }}
                                            </td>
                                            <td class="column-renewal_terms-body">
                                                {{ $group['renewal_terms_value'] > 1 ? frequencyLabel($group['renewal_terms_value'], $group['renewal_terms_unit']) : '' }}
                                            </td>
                                            <td class="column-billing_frequency-body">
                                                {{ $group['renewal_terms_value'] > 1 ? frequencyLabel($group['billing_frequency_value'], $group['billing_frequency_unit']) : '' }}
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
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                        <h3>Overall Totals</h3>
                    </div>
                    <table class="table table-dealmaker table-bordered text-end">
                        <thead>
                            <colgroup>
                                <col style="width: 76%" class="text-start" />
                                <col style="width: 8%" />
                                <col style="width: 17%" />
                            </colgroup>
                        </thead>
                        <tbody>
                            <tr>
                                <th>
                                    <p class="small text-start">Recurring Total</p>
                                </th>
                                <td></td>
                                <td id="total_monthly_net">
                                    £{{ number_format(quoteQuantityPrice('MONTHLY', $quote['items']), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <p class="small text-start">One-Off Total</p>
                                </th>
                                <td></td>
                                <td id="total_single_net">
                                    £{{ number_format(quoteTotal('SINGLE', 'total', $quote['items']), 2) }}
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <p class="small text-start">Total</p>
                                </th>
                                <td></td>
                                <td id="total_net">
                                    £{{ number_format($quote['total'], 2) }}
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
