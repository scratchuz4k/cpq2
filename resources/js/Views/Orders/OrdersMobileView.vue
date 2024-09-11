<script setup>
import axios from 'axios';
import { ref, reactive, watch } from 'vue'
import MultiSelect from 'primevue/multiselect';


const props = defineProps({
    modelValue: Object
})


</script>
<template>
    <div class="row mb-4">
        <div class="col">
            <Loader v-if="isLoading"></Loader>

            <template v-else>
                <div class="row">
                    <div class="col-lg-7 mb-5">
                        <div class="step">
                            <div class="d-flex justify-content-between mb-3">
                                <h2>Select some products to get started</h2>
                                <p class="m-0 mb-2 text-primary bold">STEP 1/3</p>
                            </div>


                            <div class="mobile-item mobile-product mb-3 panel">
                                <div class="d-flex justify-content-between">
                                    <h4>Product<span class="ms-2">data / mins / £ </span></h4>
                                    <i class="fa fa-circle-info mobile-item-info"></i>
                                </div>
                                <div class="mobile-product-quantity">
                                    <label>Quantity</label>

                                    <div class="d-flex align-items-center pb-3">
                                        <button class="btn btn-secondary btn-sm me-1" type="button">
                                            <font-awesome-icon :icon="['fas', 'minus']" />
                                        </button>
                                        <input type="number"
                                            class="form-control form-control-sm text-center w-25 me-1" value="0" />
                                        <input type="hidden" />
                                        <button class="btn btn-secondary btn-sm me-3" type="button">
                                            <font-awesome-icon :icon="['fas', 'plus']" />
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm">+25</button>
                                        <button type="button" class="btn btn-secondary btn-sm mx-2">+50</button>
                                        <button type="button" class="btn btn-secondary btn-sm">+100</button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center">
                                <span id="total_mobile_users">Add 0 mobile users</span> <button
                                    class="ms-3 btn btn-primary step-nav" type="button" data-direction="next">Next
                                    <i class="fa fa-arrow-right"></i></button>
                            </div>
                        </div>
                        <!-- <div id="mobile_step2" class="step" <?=$activeStep==2 ? '' : ' style="display: none;"' ?>>
                            <h2 class="mb-4">Create a quote</h2>

                            <x-info-box type="error" :account="$order -> account" />

                            <div class="d-flex justify-content-between mb-3">
                                <h3>Next, choose your bolt-ons</h3>
                                <p class="m-0 mb-2 text-primary bold">STEP 2/3</p>
                            </div>

                            @foreach ($bolt_ons as $bolt_on)
                            <div class="mobile-item mobile-bolt-on mb-3">
                                <div class="d-flex justify-content-between">
                                    <h4>{{ $bolt_on['name'] }} <span class="ms-2">£{{ $bolt_on['price'] }}</span>
                                    </h4>
                                    <i class="fa fa-circle-info mobile-item-info"></i>
                                </div>
                                <div class="mobile-product-quantity">
                                    <label>Quantity</label>

                                    <div class="d-flex align-items-center pb-3">
                                        <button class="btn btn-secondary btn-minus btn-sm me-1" type="button"><i
                                                class="fa-solid fa-minus"></i></button>
                                        <input type="number" name="mobile[bolt_on][{{ $bolt_on['name'] }}][quantity]"
                                            class="form-control text-center input-qty me-1" value="0">
                                        <input type="hidden" name="mobile[bolt_on][{{ $bolt_on['name'] }}][price]"
                                            value="{{ $bolt_on['price'] }}">
                                        <button class="btn btn-secondary btn-plus btn-sm me-3" type="button"><i
                                                class="fa-solid fa-plus"></i></button>
                                        <span class="badge text-bg-secondary badge-qty-add me-2"
                                            data-qty="25">+25</span>
                                        <span class="badge text-bg-secondary badge-qty-add me-2"
                                            data-qty="50">+50</span>
                                        <span class="badge text-bg-secondary badge-qty-add" data-qty="100">+100</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-light step-nav btn-sm me-3" type="button"
                                    data-direction="prev"><i class="fa fa-arrow-left"></i> Back</button>
                                <div>
                                    <span id="total_bolt_ons">Add <span>0</span> bolt-ons</span> <button
                                        class="ms-3 btn btn-primary step-nav" type="button" data-direction="next">Next
                                        <i class="fa fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div> -->
                        <!-- <div id="mobile_step3" class="step" <?=$activeStep==3 ? '' : ' style="display: none;"' ?>>
                            <h2 class="mb-4">Create a quote</h2>

                            <x-info-box type="error" :account="$order -> account" />

                            <div class="d-flex justify-content-between mb-3">
                                <h3>Finally, choose your add-ons</h3>
                                <p class="m-0 mb-2 text-primary bold">STEP 3/3</p>
                            </div>

                            @foreach ($add_ons as $add_on)
                            @foreach ($add_on['categories'] as $category)
                            @if (!empty($category['products']))
                            <div class="mobile-item mobile-add-on mb-3">
                                <div class="d-flex justify-content-between">
                                    <h4>{{ $category['name'] }}</h4>
                                    <button class="btn btn-secondary btn-sm btn-clear-filters" type="button"
                                        disabled>Clear filters</button>
                                </div>

                                <div class="hardware-labels mb-3">
                                    <p class="mb-1">Models</p>
                                    @foreach ($category['products']['labels'] as $label => $count)
                                    <div class="badge badge-label text-bg-secondary" data-type="label"
                                        style="display:none">{{ $label }}</div>
                                    @endforeach
                                </div>

                                <div class="hardware-filters mb-3">
                                    <p class="mb-1">Filters</p>
                                    <div class="mobile-item-filter">
                                        <label>Storage</label>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="1 TB">1 TB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="512 GB">512 GB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="256 GB">256 GB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="128 GB">128 GB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="64 GB">64 GB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="32 GB">32 GB</div>
                                        <div class="badge badge-filter text-bg-dark" data-type="filter"
                                            data-field="storage" value="16 GB">16 GB</div>
                                    </div>
                                </div>

                                <div class="hardware-products pb-3">
                                    @foreach ($category['products']['products'] as $product)
                                    <div class="hardware-product">
                                        <div class="text-center"><a href="{{ $product['url'] }}" target="_blank"><img
                                                    src="{{ Str::of($product['primary_image'])->replace('1024x1024','256x256')->replace('/fit', '/resize') }}"></a>
                                        </div>
                                        <b class="label">{{ $product['label'] }}</b>
                                        <span>{{ $product['sku'] }}</span>
                                        <span class="name mb-1" data-full-name="{{ $product['name'] }}">{{
                                            Str:: of($product['name']) -> words(8) }}</span>
                                        <span class="mb-2"><span class="price">£{{ $product['price']
                                                }}</span></span>
                                        @if ($product['in_stock'])
                                        <span data-stock="{{ $product['stock'] }}" class="badge text-bg-warning">{{
                                            $product['stock'] }} in stock</span>
                                        @else
                                        <span data-stock="0" class="out-of-stock badge text-bg-danger"><i
                                                class="fa fa-warning"></i> OUT OF STOCK</span>
                                        @endif

                                        <div class="mobile-product-quantity pt-2">
                                            <label>Quantity</label>
                                            <div class="d-flex align-items-center mb-2">
                                                <button class="btn btn-secondary btn-minus btn-sm me-1" type="button"><i
                                                        class="fa-solid fa-minus"></i></button>
                                                <input type="number"
                                                    name="mobile[add_on][hardware][{{ $product['sku'] }}][quantity]"
                                                    class="form-control text-center input-qty me-1" value="0" disabled>
                                                <input type="hidden"
                                                    name="mobile[add_on][hardware][{{ $product['sku'] }}][name]"
                                                    value="{{ $product['name'] }}" disabled>
                                                <input type="hidden"
                                                    name="mobile[add_on][hardware][{{ $product['sku'] }}][price]"
                                                    value="{{ $product['price'] }}" disabled>
                                                <button class="btn btn-secondary btn-plus btn-sm" type="button"><i
                                                        class="fa-solid fa-plus"></i></button>
                                            </div>

                                            <span class="badge text-bg-secondary badge-qty-add me-1"
                                                data-qty="25">+25</span>
                                            <span class="badge text-bg-secondary badge-qty-add me-1"
                                                data-qty="50">+50</span>
                                            <span class="badge text-bg-secondary badge-qty-add"
                                                data-qty="100">+100</span>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            @endforeach
                            @endforeach

                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-light step-nav btn-sm me-3" type="button"
                                    data-direction="prev"><i class="fa fa-arrow-left"></i> Back</button>
                                <div>
                                    <span id="total_add_ons">Add <span>0</span> add-ons</span> <button
                                        class="ms-3 btn btn-primary" data-direction="next">Next <i
                                            class="fa fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    <!-- <div class="col-2"></div> -->
                    <!-- <div class="col-lg-3">
                        <div class="configure-mobile-sidebar px-4 py-4">
                            <h4 id="sidebar_products" data-total="0">1. Products</h4>
                            <div class="sidebar_items mb-3">
                                @foreach ($products as $product)
                                <div class="item mb-2">
                                    <span>{{ $product['name'] }}</span><span class="badge bg-light-grey dark">0</span>
                                </div>
                                @endforeach
                            </div>
                            <h4>2. Bolt-ons</h4>
                            <div class="sidebar_bolt_ons mb-3">
                                @foreach ($bolt_ons as $bolt_on)
                                <div class="item mb-2">
                                    <span>{{ $bolt_on['name'] }}</span><span class="badge bg-light-grey dark">0</span>
                                </div>
                                @endforeach
                            </div>
                            <h4>3. Add-ons</h4>
                            <div class="sidebar_add_ons mb-3">
                                @foreach ($add_ons as $add_on)
                                <div class="item mb-2">
                                    <span>{{ $add_on['name'] }}</span><span class="badge bg-light-grey dark">0</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div> -->
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}
</style>