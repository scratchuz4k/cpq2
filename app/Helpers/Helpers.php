<?php

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
            'toStringFormat' => 'jS M Y H:i',
        ]);

        return $dt;
    }
}
