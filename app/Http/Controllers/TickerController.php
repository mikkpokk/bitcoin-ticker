<?php

namespace App\Http\Controllers;

use App\Rate;
use App\Source;
use Illuminate\Http\Request;

class TickerController extends Controller
{
    public function index(Request $request)
    {
        $active_sources = Source::where('active', true)->get();

        $source_ids = $active_sources->pluck('id')->unique()->toArray();

        if (count($source_ids)) {
            $request_query = app('db')->select('SELECT `r1`.`source_id`, `r1`.`id` FROM `requests` `r1` INNER JOIN (
            SELECT max(`id`) `MaxId`, `source_id`
            FROM `requests`
            GROUP BY `source_id`
            ) `r2`
            ON `r1`.`source_id` = `r2`.`source_id` AND `r1`.`id` = `r2`.`MaxId`
            WHERE `r1`.`source_id` IN ('.implode(', ', $source_ids).')
            ORDER BY `r1`.`id` DESC');

            $request_query = collect($request_query);
            $request_ids = $request_query->pluck('id')->toArray();
            $source_ids = $request_query->pluck('source_id')->toArray();

            $grouped_rates = Rate::whereIn('request_id', $request_ids)
                ->whereIn('source_id', $source_ids)
                ->get()
                ->sortBy('currency')
                ->groupBy('currency');

            $avg = [];

            $output = [];

            foreach ($grouped_rates as $currency_key => $rates) {
                if (! isset($avg[$currency_key])) {
                    $avg[$currency_key] = [
                        'rate' => 0,
                        'active_sources' => 0,
                    ];
                }

                $output[$currency_key] = [];

                if ($rates && count($rates)) {
                    foreach ($rates as $rate) {
                        $avg[$currency_key]['rate'] += (float) $rate->rate;
                        ++$avg[$currency_key]['active_sources'];
                    }
                }

                if ($avg[$currency_key]['rate'] > 0 && count($rates) > 0) {
                    $rate = round($avg[$currency_key]['rate'] / count($rates), 4);
                } else {
                    $rate = 'Unknown';
                }

                $output[$currency_key]['active_sources'] = $avg[$currency_key]['active_sources'];
                $output[$currency_key]['rate'] = $rate;
                $output[$currency_key]['rate_object'] = sha1('rate_'.$currency_key);
                $output[$currency_key]['source_object'] = sha1('source_'.$currency_key);
            }

            if ($request->ajax()) {
                return $output;
            } else {
                return view('index', [
                    'output' => $output,
                ]);
            }
        }
    }
}
