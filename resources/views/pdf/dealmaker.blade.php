@php
    //$isConnectivity = !is_null($quote['solution_type']) && $quote['solution_type']['code'] == 3 ? true : false;
    $isConnectivity = false;

    $leafNodes = getLeafNodesWithChildren($quote['items']);
    $quote['items'] = $leafNodes;

    $single_items = array_filter($quote['items'], function ($item) {
        return isset($item['price_plan']) && $item['price_plan']->code === 'SINGLE';
    });

    $monthly_items = array_filter($quote['items'], function ($item) {
        return isset($item['price_plan']) && $item['price_plan']->code === 'MONTHLY';
    });

    $monthly_items_total = array_reduce(
        $monthly_items,
        function ($acc, $item) {
            return $acc + $item['quantity'] * $item['price'];
        },
        0,
    );

    function getDiscount($quote, $model, $id)
    {
        $temp = null;
        if ($model === 'App\Models\Quote') {
            $temp = collect($quote['discounts'])
                ->where('discountable_type', $model)
                ->where('entity.uuid', $id)
                ->first();
        }

        if ($model === 'App\Models\PricePlan') {
            $temp = collect($quote['discounts'])
                ->where('discountable_type', $model)
                ->where('entity.code', $id)
                ->first();
        }

        return $temp ? $temp : ['discount' => 0, 'value' => 0, 'total' => 0];
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

    function formatUKPhone(string $number): string
    {
        $digits = preg_replace('/\D/', '', $number);
        $withoutLeadingZero = str_starts_with($digits, '0') ? substr($digits, 1) : $digits;
        return "+44 (0){$withoutLeadingZero}";
    }

    $discounts = [
        'quote' => getDiscount($quote, 'App\Models\Quote', $quote['uuid']),
        'one_off' => getDiscount($quote, 'App\Models\PricePlan', 'SINGLE'),
        'monthly' => getDiscount($quote, 'App\Models\PricePlan', 'MONTHLY'),
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
            -webkit-print-color-adjust: exact !important;
        }

        .child-bg>td {
            background-color: whitesmoke;
        }

        .text-primary {
            color: var(--color-purple) !important;
        }

        .column-name-header,
        .column-name-body {
            width: 50%;
        }

        #table-one_off .column-name-header,
        #table-one_off .column-name-body {
            width: 60% !important;
        }

        .column-quantity-body,
        .column-quantity-header,
        .column-billing_frequency-header,
        .column-billing_frequency-body {
            width: 10% !important;
            text-align: center;
        }

        .column-discount-body,
        .column-discount-header {
            width: 10% !important;
            text-align: center;
        }

        .column-margin-header,
        .column-margin-body,
        .column-price-header,
        .column-price-body {
            text-align: center;
        }

        .column-speed-body,
        .column-bearer-body {
            white-space: nowrap;
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
                <p>{{ $quote['account']['main_contact']['primary_contact_name'] ?? $quote['account']['name'] }}<br>{{ $quote['address_data']['label'] ?? '' }}
                </p>
                @if ($quote['account']['main_contact']['phone'] ?? false)
                    <p>Tel: {{ $quote['account']['main_contact']['phone'] }}</p>
                @endif
                @if ($quote['account']['main_contact']['email'] ?? false)
                    <p>Email: {{ $quote['account']['main_contact']['email'] }}</p>
                @endif
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
                        <div class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                            <h3>Recurring charges</h3>
                        </div>
                        <table id="table-recurring" class="table table-dealmaker table-bordered  ">
                            <!-- Headers -->
                            <thead>
                                <tr>
                                    @if ($isConnectivity)
                                        <th class="column-provider-header d-xl-table-cell">Provider</th>
                                        <th class="column-subprovider-header d-xl-table-cell">SubProvider</th>
                                    @endif
                                    <th class="column-name-header">Name</th>
                                    <th class="column-contract_lenght-header">Months</th>
                                    @if ($isConnectivity)
                                        <th class="column-speed-header">Speed</th>
                                        <th class="column-bearer-header">Bearer</th>
                                    @endif
                                    <th class="column-quantity-header">Quantity</th>
                                    <th class="column-price-header">Price</th>
                                    <th class="column-discount-header">Discount</th>
                                    <th class="column-total-header text-end" style="white-space: nowrap;">Total </th>
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
                                                        src="{{ $item['image'] ?? public_path() . '/images/providers/' . strtolower($item['data']?->providerKey) . '.png' }}" />
                                                @endif
                                            </td>
                                            <td class="column-subprovider-body ">
                                                @if (isset($item['data']?->connectivitySupplierGroup))
                                                    <img class="img-provider"
                                                        style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                        src="{{ $item['image'] ?? public_path() . '/images/providers/' . str_replace(' ', '', strtolower($item['data']?->connectivitySupplierGroup)) . '.png' }}" />
                                                @endif
                                            </td>
                                        @endif
                                        <td class="column-name-body">
                                            <div class="d-flex align-baseline justify-content-between">
                                                <p class="small semi-bold">
                                                    {{ $item['name'] }}

                                                    @if (isset($item['data']->additionalInfo->{'router'}))
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-html="true"
                                                            data-bs-original-title="<b>Primary Router Model:</b> {{ $item['data']->additionalInfo->{'router'} }}"
                                                            v-tooltip>
                                                            <i class="fa-solid fa-plug"></i>
                                                        </span>
                                                    @endif
                                                    {{-- @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                <span
                                                    class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                                                    title="<b>Contended Service:</b> Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                    v-tooltip>
                                                    <i class="fa-solid fa-server"></i>
                                                </span>
                                            @endif --}}
                                                    @if (isset($item['data']->additionalInfo->{'Type'}) && $item['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-burstable"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-html="true"
                                                            title="<b>Burstable Service:</b> This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                            v-tooltip>
                                                            <i class="fa-solid fa-bolt"></i>
                                                        </span>
                                                    @endif
                                                    @if (isset($item['data']->additionalInfo->{'addon_ipv4'}))
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            data-bs-html="true"
                                                            title="{{ $item['data']->additionalInfo->{'addon_ipv4'} }}"
                                                            v-tooltip>
                                                            <i class="fa-solid fa-network-wired"></i>
                                                        </span>
                                                    @endif
                                                </p>
                                                @if (isset($item['data']->bundle_items))
                                                    <span class="badge badge-bundle">
                                                        Bundle
                                                    </span>
                                                @endif
                                                @if (getMainProductName($quote, $item))
                                                    <p class="main-product-reference">
                                                        {{ getMainProductName($quote, $item) }}
                                                    </p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="column-billing_frequency-body">
                                            {{ billingFrequencyLabel($item['renewal_terms_value'], $item['renewal_terms_unit'], $item['billing_frequency_value'], $item['billing_frequency_unit']) }}
                                        </td>
                                        @if ($isConnectivity)
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
                                            £{{ number_format($item['price'] * $item['quantity'], 2) }}
                                        </td>
                                    </tr>
                                    @if ($item['data'] && isset($item['data']?->bundle_items))
                                        @foreach ($item['data']->bundle_items as $bundle_item)
                                            <tr class="item-row child-bg" style="vertical-align: middle;">
                                                <td colspan="100%">
                                                    <p class="small ps-2 mb-1">{{ $bundle_item->name }}
                                                        x{{ $bundle_item->quantity }}</p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                @if ($discounts['monthly']['discount'])
                                    <tr class="item-row" style="vertical-align: middle;">
                                        <td class="column-name-body text-primary"
                                            colspan="{{ $isConnectivity ? '8' : '4' }}">
                                            Recurring Discount
                                        </td>
                                        <td class="text-center text-primary">
                                            {{ number_format($discounts['monthly']['discount'], 2) }} %
                                        </td>
                                        <td class="text-end text-primary">
                                            <b>-£{{ number_format((float) $discounts['monthly']['value'], 2) }}</b>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th class="text-start" colspan="{{ $isConnectivity ? '9' : '5' }}">
                                        Monthly Subtotal
                                    </th>
                                    <th class="text-end">
                                        £{{ number_format($monthly_items_total - $discounts['monthly']['value'], 2) }}
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="mb-5">
                        <div class="mobile-price-group-heading mt-3 d-flex justify-content-between align-items-center">
                            <h3>One-off charges</h3>
                        </div>
                        <table id="table-one_off" class="table table-dealmaker table-bordered">
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
                                    <th class="column-total-header text-end" style="white-space: nowrap;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($single_items as $key => $item)
                                    <tr class="item-row" style="vertical-align: middle;">
                                        @if ($isConnectivity)
                                            <td class="column-provider-body">
                                                @if (isset($item['data']?->providerKey))
                                                    <img class="img-provider"
                                                        style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                        src="{{ $item['image'] ?? public_path() . '/images/providers/' . strtolower($item['data']?->providerKey) . '.png' }}" />
                                                @endif
                                            </td>
                                            <td class="column-subprovider-body ">
                                                @if (isset($item['data']?->connectivitySupplierGroup))
                                                    <img class="img-provider"
                                                        style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                                                        src="{{ $item['image'] ?? public_path() . '/images/providers/' . str_replace(' ', '', strtolower($item['data']?->connectivitySupplierGroup)) . '.png' }}" />
                                                @endif
                                            </td>
                                        @endif
                                        <td class="column-name-body">
                                            <div class="d-flex align-baseline justify-content-between">
                                                <p class="small semi-bold">
                                                    {{ $item['name'] }}
                                                    @if ($item['data'] && $item['data']->type == 'addon_product')
                                                        ({{ collect($quote['items'])->first(fn($i) => $i['group'] == $item['group'] && $i['data'] && $i['data']->type == 'main_product')['name'] ?? '' }})
                                                    @endif
                                                    @if (isset($item['data']->additionalInfo->{'router'}))
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Primary Router Model" v-tooltip>
                                                            <i class="fa-solid fa-plug"></i>
                                                            {{ $item['data']->additionalInfo->{'router'} }}
                                                        </span>
                                                    @endif
                                                    {{-- @if (isset($item['data']->isContended) and $item['data']->isContended)
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="Contended, wires only full fibre service with symmetrical upload and download speeds"
                                                            v-tooltip>
                                                            Contended Service
                                                        </span>
                                                    @endif --}}
                                                    @if (isset($item['data']->additionalInfo->{'Type'}) && $item['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                                                        <span
                                                            class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                                                            v-tooltip>
                                                            Burstable Service
                                                        </span>
                                                    @endif
                                                </p>
                                                @if (isset($item['data']->bundle_items))
                                                    <span class="badge badge-bundle">
                                                        Bundle
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        @if ($isConnectivity)
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

                                    @if ($item['data'] && isset($item['data']?->bundle_items))
                                        @foreach ($item['data']->bundle_items as $bundle_item)
                                            <tr class="item-row child-bg" style="vertical-align: middle;">
                                                <td colspan="100%">
                                                    <p class="small ps-2 mb-1">{{ $bundle_item->name }}
                                                        x{{ $bundle_item->quantity }}</p>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach
                                @if ($discounts['one_off']['discount'])
                                    <tr class="item-row" style="vertical-align: middle;"></tr>
                                    <td class="column-name-body text-primary"
                                        colspan="{{ $isConnectivity ? '7' : '3' }}">
                                        One-Off Discount
                                    </td>
                                    <td class="text-center text-primary">
                                        {{ number_format($discounts['one_off']['discount'], 2) }} %
                                    </td>
                                    <td class="text-end  text-primary">
                                        <b>-£{{ number_format((float) $discounts['one_off']['value'], 2) }}</b>
                                    </td>
                                    </tr>
                                @endif
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-start" colspan="{{ $isConnectivity ? '8' : '4' }}">Subtotal
                                    </th>
                                    <th class="text-end">
                                        £{{ number_format(quoteTotal('SINGLE', 'total', $quote['items']) - $discounts['one_off']['value'], 2) }}
                                    </th>
                                </tr>
                            </tfoot>
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
                                <td>
                                    <p class="small text-start">Recurring Total</p>
                                </td>
                                <td></td>
                                <td id="total_monthly_net">
                                    <b>£{{ number_format(quoteTotal('MONTHLY', 'total', $quote['items']) - $discounts['monthly']['total'], 2) }}</b>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="small text-start">One-Off Total</p>
                                </td>
                                <td>
                                </td>
                                <td id="total_single_net">
                                    <b>£{{ number_format(quoteTotal('SINGLE', 'total', $quote['items']) - $discounts['one_off']['total'], 2) }}</b>
                                </td>
                            </tr>
                            @if ($discounts['quote']['discount'])
                                <tr class="item-row" style="vertical-align: middle;">
                                    <td>
                                        <p class="small text-start text-primary">Overall Discount</p>
                                    </td>
                                    <td class="text-end text-primary">
                                        {{ number_format($discounts['quote']['discount'], 2) }} %
                                    </td>
                                    <td class="text-end text-primary">
                                        <b>-£{{ number_format((float) $discounts['quote']['total'], 2) }}</b>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td>
                                    <p class="small text-start">Total</p>
                                </td>
                                <td></td>
                                <td id="total_net">
                                    <b>£{{ number_format($quote['total'], 2) }}</b>
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
