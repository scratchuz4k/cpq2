<?php

use App\Models\Quote;
use App\Models\Order;
use App\Enums\OrderStatusesEnum;

if (! function_exists('format_date')) {
    /**
     * Format a date to string
     *
     * @param  string  $date  The date to format
     * @return string The formatted date
     */
    function format_date($date): string
    {
        if (!$date) {
            return '';
        }

        if (strtotime($date) >= strtotime('-1 minutes')) {
            return  __('just now');
        }
        if (strtotime($date) >= strtotime('-1 day')) {
            return  \Carbon\Carbon::parse($date)->diffForHumans();
        }
        $dt = \Carbon\Carbon::create($date);

        $dt->settings([
            'toStringFormat' => 'Y-m-d   H:i',
        ]);

        return $dt;
    }
}

if (! function_exists('get_order_status')) {
    function get_order_status(Order $order)
    {
        if ($order->items->count()) {
            return 'QUOTE';
        }
        if (!empty($order->address_data)) {
            return 'PRICE';
        }

        return 'CONFIGURE';
    }
}
