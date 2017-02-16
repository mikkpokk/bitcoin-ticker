<?php

namespace App\Http\Controllers;

use App\Rate;
use App\Request;
use App\Source;
use Illuminate\Support\Facades\DB;

class TickerController extends Controller
{
    public function index()
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
                ->groupBy('currency');

            $avg = [];

            foreach ($grouped_rates as $currency_key => $rates) {
                if (! isset($avg[$currency_key])) {
                    $avg[$currency_key] = 0;
                }

                echo $currency_key.': ';

                if ($rates && count($rates)) {
                    foreach ($rates as $rate) {
                        $avg[$currency_key] += (float) $rate->rate;
                    }
                }

                echo round($avg[$currency_key] / count($rates), 4).'<br>';
            }
        }

        //return json_decode($output, true);
    }
}
