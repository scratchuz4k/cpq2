<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quote #{{$order->id}}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
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
    <div class="container-fluid">
        <div class="row" id="header">
            <div class="col" id="header_left">
                <img src="https://img.logoipsum.com/331.svg" alt="Sample Logo" class="img-fluid">
                <p>Insert Tagline</p>
            </div>
            <div class="col text-end" id="header_right">
                <p>{{ $order->account->name }}<br>{{ $order->address_data?->label}}</p>
                <p>Tel: +44 (0) 191 300 700</p>
                <p>Email: sales@example.com</p>
            </div>
        </div>
        <div class="cpq-summary row px-3" id="main">
            <div class="col">
                <h1 class="mb-3">Quote Q-{{ $order['id'] }}</h1>
                <div class="row mb-3">
                    <div class="col">
                        <p>Company address, city, state, post code<br>
                            sales@example.com<br>
                            +44 (0) 123 456 7890</p>
                    </div>
                    <div class="col">
                        <p>Quote #{{ $order['id'] }}<br>
                            Quotation Date: {{ date('d/m/Y') }}<br>
                            Valid Date: {{ date('d/m/Y', strtotime('+7 days')) }}</p>
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
                <table class="table mb-4">
                    <thead>
                        <tr>
                            <th scope="col">SR #</th>
                            <th class="text-center" scope="col">PROVIDER</th>
                            <th scope="col">ITEM DETAILS</th>
                            <th scope="col">SUPPLIER QUOTE REF.</th>
                            <th scope="col">TYPE</th>
                            <th scope="col">QTY</th>
                            <th class="text-end" scope="col">MONTHLY</th>
                            <th class="text-end" scope="col">INSTALL</th>
                            <th class="text-end" scope="col">LINE TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $key => $item)
                        <tr class="item-row">
                            <td>
                                {{$key}}
                            </td>
                            {{-- <td class="text-center"><img class="img-provider" style="max-width: 60px; max-height: 20px; width: auto; height: auto;" src="{{ $item->image ?? env('APP_URL').'/images/providers/' . $item->data?->providerKey. '.png' }}"></td>
                            <td>
                                {{ $item->name }}

                            </td>
                            <td>{{ $item->data?->quoteId }}</td>
                            <td>Pay Monthly</td>
                            <td>{{ $item->quantity }}</td>
                            <td class="text-end">
                                {{$item->data?->priceMonthly}}
                            </td>
                            <td class="text-end">
                                {{$item->data?->priceInstall}}
                            </td>
                            <td class="text-end">
                                {{$item->quantity * ($item->data?->priceMonthly?? 0) +  ($item->data?->priceInstall?? 0)}}
                            </td> --}}
                            {{-- !empty($item['data']['additionalInfo']['Primary Router Model']) ? '<br/><span data-bs-toggle="tooltip" data-bs-title="Primary Router Model" data-bs-custom-class="tooltip-small" class="badge border border-secondary text-secondary opacity-75"><i class="d-print-none fa-solid fa-plug me-1"></i>' . $item['data']['additionalInfo']['Primary Router Model'] . '</span>' : '' --}}
                        </tr>
                        @endforeach


                    </tbody>
                </table>

                <div class="d-flex justify-content-end">
                    <div class="col-6"></div>
                    <div class="col-6">
                        <table class="table">
                            <tr>
                                <th></th>
                                <th>NET</th>
                                <th>VAT (20%)</th>
                                <th>GROSS</th>
                            </tr>
                            <tr>
                                <th>Total Monthly</th>
                                <td class="text-end" id="total_monthly_net">£{{ $totals['monthly_net'] }}</td>
                                <td class="text-end" id="total_monthly_vat">£{{ $totals['monthly_vat'] }}</td>
                                <td class="text-end" id="total_monthly_gross">£{{ $totals['monthly_gross'] }}</td>
                            </tr>
                            <tr>
                                <th>Total One-off Costs</th>
                                <td class="text-end" id="total_oneoff_net">£{{ $totals['oneoff_net'] }}</td>
                                <td class="text-end" id="total_oneoff_vat">£{{ $totals['oneoff_vat'] }}</td>
                                <td class="text-end" id="total_oneoff_gross">£{{ $totals['oneoff_gross'] }}</td>
                            </tr>
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
        <!-- <div class="row mb-2" id="footer">
            <div class="col">
                <img src="https://img.logoipsum.com/331.svg">
                <p>Insert Tagline</p>
            </div>
            <div class="col text-end">
                <p>Lorem ipsum dolor sit amet...</p>
            </div>
        </div> -->
    </div>

</body>

</html>
