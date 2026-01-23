<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomQuery
{
    // Table -> Available custom queries
    private static array $queries = [
        'quotes' => [
            'latestApproval.approver.name',
        ],
    ];

    public static function exists($table, $string)
    {
        return in_array($string, self::$queries[$table] ?? []);
    }

    public static function execute($table, $string)
    {
        if (! self::exists($table, $string)) {
            return null;
        }

        $function = Str::camel(str_replace('.', '_', $string));

        return self::{$function}();
    }

    public static function latestApprovalApproverName()
    {
        return DB::table('quote_approvals')
            ->leftJoin('users as approver', 'quote_approvals.approver_id', '=', 'approver.id')
            ->whereColumn('quote_approvals.quote_id', 'quotes.id')
            ->where('quote_approvals.tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('quote_approvals.id')
            ->limit(1)
            ->select('approver.name');
    }
}
