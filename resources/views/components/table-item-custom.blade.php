@switch($component)
    @case('provider')
        <div class="me-3">
            @if ($item['data']?->providerKey)
                <img class="img-provider" style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                    src="{{ $item['image'] ?? public_path() . '/images/providers/' . strtolower($item['data']?->providerKey) . '.png' }}" />
            @endif
        </div>
    @break

    @case('subprovider')
        @if ($item['data']?->connectivitySupplierGroup)
            <img class="img-provider" style="max-width: 60px; max-height: 20px; width: auto; height: auto;"
                src="{{ $item['image'] ?? public_path() . '/images/providers/' . str_replace(' ', '', strtolower($item['data']?->connectivitySupplierGroup)) . '.png' }}" />
        @endif
    @break

    @case('name')
        <p class="m-0">{{ $item['name'] }}
            @if (isset($item['data']->additionalInfo->{'router'}))
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm router-model"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                    data-bs-original-title="<b>Primary Router Model:</b> {{ $item['data']->additionalInfo->{'router'} }}"
                    v-tooltip>
                    <i class="fa-solid fa-plug"></i>
                </span>
            @endif
            {{-- @if (isset($item['data']->isContended) and $item['data']->isContended)
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-contended"
                    data-bs-toggle="tooltip" data-bs-placement="top"data-bs-html="true"
                    title="<b>Contended Service:</b> Contended, wires only full fibre service with symmetrical upload and download speeds"
                    v-tooltip>
                    <i class="fa-solid fa-server"></i>
                </span>
            @endif --}}
            @if (isset($item['data']->additionalInfo->{'Type'}) && $item['data']->additionalInfo->{'Type'} == 'Fibre Flex 200+')
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm is-burstable"
                    data-bs-toggle="tooltip" data-bs-placement="top"data-bs-html="true"
                    title="<b>Burstable Service:</b> This service offers guaranteed upload and download speeds of 200Mbps and can burst up to a peak of 1Gbps"
                    v-tooltip>
                    <i class="fa-solid fa-bolt"></i>
                </span>
            @endif
            @if (isset($item['data']->additionalInfo->{'addon_ipv4'}))
                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm addon-ipv4"
                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="true"
                    title="{{ $item['data']->additionalInfo->{'addon_ipv4'} }}" v-tooltip>
                    <i class="fa-solid fa-network-wired"></i>
                </span>
            @endif
        <p>
        @break

        @case('quote_ref')
            {{ $item['data']?->productCode }}
        @break

        @case('speed')
            {{ formatBytes($item['data']?->speed) }}
        @break

        @case('bearer')
            {{ formatBytes($item['data']?->bearer) }}
        @break

        @case('contract_length')
            {{ $item->pricePlan['code'] == 'MONTHLY' ? $item['contract_length'] : '' }}
        @break

        @case('price_plan.name')
            {{ $item->pricePlan?->name }}
        @break

        @case('quantity')
            {{ $item['quantity'] }}
        @break

        @case('margin')
        @break

        @case('discount')
        @break

        @case('price')
            £{{ number_format($item['price'], 2) }}
        @break

        @case('total')
            £{{ number_format($item['total'], 2) }}
        @break

        @case('group')
            {{ $item['group'] }}
        @break

    @endswitch
