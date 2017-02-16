<?php

namespace App\Http\Controllers;

use App\Rate;
use App\Request;
use App\Source;

class TickerController extends Controller
{
    public function index()
    {
        $active_sources = Source::where('active', true)->get();

        $source_ids = $active_sources->pluck('id')->unique()->toArray();

        $request_query = Request::whereIn('source_id', $source_ids)
            ->select(['id', 'source_id'])
            ->orderBy('id', 'desc')
            ->groupBy('source_id')
            ->get();

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

            foreach ($rates as $rate) {
                $avg[$currency_key] += (float) $rate->rate;
            }

            echo round($avg[$currency_key] / count($rates), 4).'<br>';
        }

        exit();
        //return json_decode($output, true);
    }
}
