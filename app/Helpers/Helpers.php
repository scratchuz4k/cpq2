<?php

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

if (! function_exists('format_date')) {
    /**
     * Format a date to string
     *
     * @param  string  $date  The date to format
     * @return string The formatted date
     */
    function format_date($date): string
    {
        if (! $date) {
            return '';
        }

        if (strtotime($date) >= strtotime('-1 minutes')) {
            return __('just now');
        }
        if (strtotime($date) >= strtotime('-1 day')) {
            return \Carbon\Carbon::parse($date)->diffForHumans();
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
        if (! empty($order->address_data)) {
            return 'PRICE';
        }

        return 'CONFIGURE';
    }
}

if (! function_exists('format_currency')) {
    function format_currency($value)
    {
        return round((float) $value, config('currency.precision'));
    }
}

if (! function_exists('formated_date_range')) {
    function formated_date_range(array $array, $model): array
    {
        $start_date = Carbon::parse($array['start'] ?? $model::min('created_at'));
        $end_date = Carbon::parse($array['end'] ?? Carbon::now());

        if ($start_date->greaterThan($end_date)) {
            [$start_date, $end_date] = [$end_date, $start_date];
        }

        return $start_date->greaterThan($end_date)
            ? [$end_date, $start_date]
            : [$start_date, $end_date];
    }
}

if (! function_exists('get_tenant_config')) {
    function get_tenant_config(string $key): ?string
    {
        $configs = auth()->user()->currentTenant->configs ?? [];

        return ! empty($configs[$key]) ? $configs[$key] : null;
    }
}

if (! function_exists('safeXmlParse')) {
    function safeXmlParse(string $xmlString)
    {
        // TODO When available use LIBXML_NO_XXE verification to prevent xxe injection (Ubuntu 22.04 LTS only provides
        // XML Lib version 2.9.4 and LIBXML_NO_XXE needs XML Lib version >2.13.0 which is not stable yet)
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument;
        $dom->resolveExternals = false;
        $dom->substituteEntities = false;

        if (! $dom->loadXML($xmlString, LIBXML_NONET)) {
            foreach (libxml_get_errors() as $error) {
                Log::warning(sprintf(
                    'XML parse error (level %d): %s at line %d column %d',
                    $error->level,
                    trim($error->message),
                    $error->line,
                    $error->column
                ));
            }
            libxml_clear_errors();

            return null;
        }

        return simplexml_import_dom($dom)->asXML() ?: null;
    }
}

if (! function_exists('handleException')) {
    function handleException(\Exception $e, string $message = 'Something went wrong.')
    {
        Log::debug($e->getMessage());

        if (config('app.debug')) {
            return $e->getMessage();
        }

        return response()->json($message, 500);
    }
}
