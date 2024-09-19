<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'rebate') }} - @yield('title')</title>

    @vite(['resources/sass/app.scss'])
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

        #header img {
            width: var(--pdf-header-logo-width);
        }

        #footer img {
            width: var(--pdf-footer-logo-width);
        }
    </style>
</head>

<body>
    <div id="pdf">
        <div class="d-flex align-items-center mb-4">
            <div class="me-3">
                <i class="fa fa-file-lines fa-border" style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px"></i>
            </div>
            <div style="padding-top:1px">
                <p class="m-0">Q-{{ $order->id }} // <span id="account_name">{{ $order->account->name }}</span></p>
                <h1 class="m-0" style="font-size:18px;font-weight:bold">Quote</h1>
            </div>
            <div class="text-end flex-grow-1">
                <a class="btn btn-secondary" href="/quotes/pdf/{{ $order->id }}"><i class="fa fa-file-pdf"></i> Preview PDF</a>
                <button class="btn btn-primary" type="submit">Save Quote</button>
            </div>
        </div>

        <div class="panel">
            <div class="row">
                <div class="col">
                    <form>
                        <div class="row mb-3">
                            <div class="col">
                                <div id="price_header">
                                    <p class="mb-0"><b>Solution Type:</b> {{ $solution_types[$order->solution_type_id] }} <a class="btn btn-sm btn-secondary py-0" href="/quotes/configure/{{ $order->id }}">Edit</a>
                                        <br>
                                        <b>Address:</b> {{$order->address_data?->label}} <a class="btn btn-sm btn-secondary py-0" href="/quotes/configure/{{ $order->id }}">Edit address</a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 cpq-hidden-without-solution">
                                <div class="row">
                                    <div class="col">
                                        <!-- Global Discount -->
                                        <div class="form-group">
                                            <label for="globalDiscount">Global Discount (%)</label>
                                            <input type="number" class="form-control" id="globalDiscount" value="0">
                                            <div><small><a href="#" data-toggle="modal" data-target="#calculateDiscountModal" id="calculate_discount">Calculate discount for budget</a></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <!-- Subscription Term -->
                                        <div class="form-group">
                                            <label for="subscriptionTerm">Subscription Term (months)</label>
                                            <input type="number" class="form-control" id="subscriptionTerm" value="36">
                                        </div>
                                    </div>
                                </div>
                                <div class="collapse text-left" id="dealInfo">
                                    <div class="row">
                                        <div class="col">
                                            <div class="form-group">
                                                <label for="total">Margin</label>
                                                <input type="number" class="form-control" id="total" value="0" readonly>
                                            </div>
                                        </div>

                                        <div class="col">
                                            <div class="form-group">
                                                <label for="commission">Commission</label>
                                                <input type="number" class="form-control" id="commission" value="0" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="summary d-flex justify-content-between mb-3 d-none">
                            <div class="inner flex-grow-1 me-3">
                                <h2 class="mb-3">Cost Summary</h2>
                                <div class="d-flex">
                                    <div>
                                        <h3>Total one-off cost to you</h3>
                                        <p class="mb-0">£6,600</p>
                                    </div>
                                    <div>
                                        <h3>Total one-off cost to customer</h3>
                                        <p class="mb-0">£7,500</p>
                                    </div>
                                    <div>
                                        <h3>Total monthly cost to you</h3>
                                        <p class="mb-0">£645</p>
                                    </div>
                                    <div>
                                        <h3>Total monthly cost to customer</h3>
                                        <p class="mb-0">£750</p>
                                    </div>
                                </div>
                            </div>
                            <div class="inner flex-grow-1">
                                <h2 class="mb-3">Profitability</h2>
                                <div class="d-flex">
                                    <div>
                                        <h3>Total one-off profit</h3>
                                        <p class="mb-0">£900</p>
                                    </div>
                                    <div>
                                        <h3>Total monthly profit</h3>
                                        <p class="mb-0">£105</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="cpq-products cpq-summary mb-3">
                            <div class="mb-3">
                                <button type="button" class="btn btn-sm btn-danger" id="remove_items" disabled>Remove item(s)</button>
                            </div>

                            <!-- Product List -->
                            <!-- In a real application, this would be dynamically generated based on the selected solution type -->
                            <table id="table-quotes" class="table table-hover">
                                <!-- Headers -->
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="text-center">Image</th>
                                        <th>Name</th>
                                        <th>Supplier Quote Ref.</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th class="text-end">Monthly Price</th>
                                        <th class="text-end">Install Price</th>
                                        <!-- <th>Line Discount</th> -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->items as $item)
                                    <tr class="item-row">
                                        <td class="position-relative" style="width:40px">
                                            <label class="container-form-check-input custom-control custom-checkbox">
                                                <input type="checkbox" class="form-check-input" name="selected_items[]"
                                                    value="{{ $item['id'] }}">
                                            </label>
                                        </td>
                                        <td class="text-center"><img class="img-provider" src="<?= $item['image'] ?? '/mockup/images/providers/' . $item['data']['providerKey'] . '.png' ?>" width="50">
                                        </td>
                                        <td>
                                            <?= $item['name'] ?>
                                            <?= !empty($item['data']['additionalInfo']['Primary Router Model']) ? '<br/><span data-bs-toggle="tooltip" data-bs-title="Primary Router Model" data-bs-custom-class="tooltip-small" class="badge border border-secondary text-secondary opacity-75"><i class="fa-solid fa-plug me-1"></i>' . $item['data']['additionalInfo']['Primary Router Model'] . '</span>' : '' ?>
                                            <!-- <?php dump($item); ?> -->
                                        </td>
                                        <td><?= $item['data']['quoteId'] ? $item['data']['quoteId'] : '' ?></td>
                                        <td>
                                            Pay Monthly
                                        </td>
                                        <td>
                                            <div class="cpq-qty d-flex">
                                                <input type="number" min="0" max="99999" maxlength="5" name="item[{{ $item['id'] }}][quantity]" class="form-control input-qty me-1" value="{{ $item['quantity'] }}" onkeypress='return event.charCode >= 48 && event.charCode <= 57'>
                                                <button class="btn btn-secondary btn-plus me-1" type="button"><i class="fa-solid fa-plus"></i></button>
                                                <button class="btn btn-secondary btn-minus" type="button"><i class="fa-solid fa-minus"></i></button>
                                            </div>
                                        </td>
                                        <td class="text-end" data-price="<?= $item['data']['priceMonthly'] ?? '' ?>">
                                            <?= number_format($item['data']['priceMonthly'] ?? '', 2) ?>
                                        </td>
                                        <td class="text-end" data-oneoff="<?= $item['data']['priceInstall'] ?? '' ?>">
                                            <?= number_format($item['data']['priceInstall'] ?? '', 2) ?>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="cpq-total text-right">
                                <div class="row">
                                    <div class="col"></div>
                                    <div class="col">
                                        <table class="table table-hover">
                                            <tr>
                                                <th></th>
                                                <th>NET</th>
                                                <th>VAT (20%)</th>
                                                <th>GROSS</th>
                                            </tr>
                                            <tr>
                                                <th>Total Monthly</th>
                                                <td class="text-end" id="total_monthly_net">£0</td>
                                                <td class="text-end" id="total_monthly_vat">£0</td>
                                                <td class="text-end" id="total_monthly_gross">£0</td>
                                            </tr>
                                            <tr>
                                                <th>Total One-off Costs</th>
                                                <td class="text-end" id="total_oneoff_net">£0</td>
                                                <td class="text-end" id="total_oneoff_vat">£0</td>
                                                <td class="text-end" id="total_oneoff_gross">£0</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>