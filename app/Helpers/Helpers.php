<?php

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
            $message = $e->getMessage();
        }

        return response()->json(['message' => $message], 500);
    }
}

if (! function_exists('handleWebException')) {
    function handleWebException(\Exception $e, string $message = 'Something went wrong.')
    {
        Log::error($e->getMessage());

        if (config('app.debug')) {
            $message = $e->getMessage();
        }

        return redirect()->back()->withErrors([$message]);
    }
}

if (! function_exists('handleBlockedQuote')) {
    function handleBlockedQuote()
    {
        return response()->json(['message' => 'Quote is blocked'], 400);
    }
}

if (! function_exists('mapProviderErrors')) {
    function mapProviderErrors(array $errors): array
    {
        $errorMessages = [
            'BadGateway' => 'A Bad Gateway error occurred, resulting in partial or no results.',
            'Request timed out' => 'The request timed out, resulting in partial or no results.',
            'InternalServerError' => 'An error occurred on the server.',
            'An error occurred while sending the request' => 'There was an issue sending your request.',
            'TooManyRequests' => 'The system is experiencing a high volume of requests.',
            'Too Many Attempts' => 'There are currently too many requests being processed.',
            'System.Text.Json.JsonException' => 'The request could not be processed due to a data format issue.',
            'Service Unavailable' => 'The service is temporarily unavailable.',
            'There is an error in XML document' => 'The request could not be processed due to a data format issue.',
            'No provider is configured' => 'No provider is configured, please add at least one provider with the required credentials to obtain a quote.',
            'Request failed with status Unauthorized' => 'Request failed with status Unauthorized (Please check login credentials for this provider).',
            'Address not found in cache' => 'No results were found for this address.',
            'At least one of Building Name or Number is required' => 'At least one of Building Name or Number is required.',
            'Products are not available at' => 'No products were found for this location.',
            'No results for this location' => 'No results were found for this location.',
            'VMB requires one of UPRN or Lat/Long for the request' => 'Virgin Media Business requires one of UPRN or Latitude/Longitude for the request.',
            'Street is not set' => 'There is no street set for this address.',
        ];

        $mappedErrors = [];

        foreach ($errors as $error) {
            if (($error['provider'] ?? null) === 'gamma') {
                continue;
            }

            $provider = $error['provider'] ?? null;
            $responseText = (string) ($error['response'] ?? '');

            $mappedMessage = null;
            foreach ($errorMessages as $key => $message) {
                if (str_contains($responseText, $key)) {
                    $mappedMessage = $message;
                    break;
                }
            }

            $mappedErrors[] = [
                'provider' => $provider,
                'response' => $mappedMessage ?? 'An error occurred, resulting in partial or no results',
            ];
        }

        $unique = [];
        foreach ($mappedErrors as $e) {
            $uniqKey = ($e['provider'] ?? '').'|'.($e['response'] ?? '');
            $unique[$uniqKey] = $e;
        }

        return array_values($unique);
    }
}
