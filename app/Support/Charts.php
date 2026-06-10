<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Charts
{
    /**
     * Build a { labels, data } payload bucketed by month for the last N months.
     *
     * @param  string  $table     Table name.
     * @param  string  $dateCol   Date/timestamp column to bucket by.
     * @param  string  $aggExpr   Aggregate SQL expression, e.g. 'COUNT(*)' or 'SUM(grand_total)'.
     * @param  int     $months    Number of trailing months (default 12).
     * @param  array   $wheres    Optional [[col, op, val], ...] extra filters.
     */
    public static function monthly(string $table, string $dateCol, string $aggExpr = 'COUNT(*)', int $months = 12, array $wheres = []): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);

        $buckets = [];
        $labels = [];
        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $buckets[$m->format('Y-m')] = 0;
            $labels[] = $m->locale('id')->isoFormat('MMM YY');
        }

        $query = DB::table($table)
            ->select(DB::raw("to_char($dateCol, 'YYYY-MM') as ym"), DB::raw("$aggExpr as val"))
            ->whereRaw("$dateCol >= ?", [$start->format('Y-m-d')])
            ->groupBy('ym');

        foreach ($wheres as $w) {
            $query->where($w[0], $w[1], $w[2]);
        }

        foreach ($query->get() as $row) {
            if (array_key_exists($row->ym, $buckets)) {
                $buckets[$row->ym] = (float) $row->val;
            }
        }

        return ['labels' => $labels, 'data' => array_values($buckets)];
    }
}
