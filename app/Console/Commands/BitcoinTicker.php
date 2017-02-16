<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Rate;
use App\Request;
use App\Source;

class BitcoinTicker extends Command
{
    protected $signature = 'bitcoin_ticker {--update-sources}';
    protected $description = 'Crawls latest BitCoin - EUR/USD rates and inserts to the database.';

    protected $default_sources = [
        // BTC Rates
        1 => [
            'url' => 'https://blockchain.info/ticker?cors=true',
            'active' => true,
            'method' => 'block_chain_crawler',
        ],
        2 => [
            'url' => 'http://api.coindesk.com/v1/bpi/currentprice.json',
            'active' => true,
            'method' => 'coin_desk_crawler',
        ],
        3 => [
            'url' => 'https://www.live-rates.com/rates',
            'active' => true,
            'method' => 'live_rates_crawler',
        ],
    ];

    protected $currencies = [
        'EUR',
        'USD',
        'EUR/USD',
    ];

    public function handle()
    {
        $is_working = Cache::get('bitcoin_ticker_worker');

        if ($is_working) {
            $this->info('Other command is already in process.');
            $this->comment('Script goes to sleep for 950 millisecond.');
            usleep(950000);
            exit();
        } else {
            Cache::put('bitcoin_ticker_worker', time(), 1);

            $update_sources = $this->option('update-sources');

            if ($update_sources) {
                $this->update_sources_handle();
            } else {
                $active_sources = Source::where('active', true)->get();

                if (! $active_sources->count()) {
                    $this->update_sources_handle();

                    $active_sources = Source::where('active', true)->get();
                }

                if ($active_sources->count()) {
                    $default_context = stream_context_create([
                        'http' => [
                            'method' => 'GET',
                            'header' => 'Accept-language: en' . "\r\n",
                            'timeout' => 3,
                        ],
                    ]);

                    foreach ($active_sources as $source) {
                        try {
                            $info = file_get_contents($source->url, false, $default_context);

                            if ($info) {
                                $method = $source->method;

                                $this->$method($info, $source->id);
                            } else {
                                $this->error('Unable to crawl info from: ' . $source->url);
                            }
                        } catch (\Exception $e) {
                            $this->error('Error occurred with source: ' . $source->url);

                            continue;
                        }
                    }
                } else {
                    $this->info('Unable to find any active sources.');
                }
            }

            Cache::forget('bitcoin_ticker_worker');
        }
    }

    protected function block_chain_crawler($info, $source_id)
    {
        // BTC
        $this->info('Processing info via block chain crawler..');

        try {
            $info = json_decode(trim($info), true);

            if (is_array($info)) {
                $request = new Request;
                $request->source_id = (int) $source_id;
                $request->source_raw = json_encode($info);
                $request->save();

                foreach ($info as $key => $params) {
                    if (in_array(mb_strtoupper($key), $this->currencies)) {
                        $rate = new Rate;
                        $rate->request_id = $request->id;
                        $rate->source_id = $source_id;
                        $rate->currency = 'BTC/'.mb_strtoupper($key);
                        $rate->rate = (float) $params['last'];
                        $rate->save();
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }

    protected function coin_desk_crawler($info, $source_id)
    {
        // BTC
        $this->info('Processing info via coin desk crawler..');

        try {
            $info = json_decode(trim($info), true);

            if (is_array($info)) {
                $request = new Request;
                $request->source_id = (int) $source_id;
                $request->source_raw = json_encode($info);
                $request->updated_at = Carbon::createFromTimestampUTC(strtotime($info['time']['updatedISO']))->format('Y-m-d H:i:s');
                $request->save();

                foreach ($info['bpi'] as $key => $params) {
                    if (in_array(mb_strtoupper($key), $this->currencies)) {
                        $rate = new Rate;
                        $rate->request_id = $request->id;
                        $rate->source_id = $source_id;
                        $rate->currency = 'BTC/'.mb_strtoupper($key);
                        $rate->rate = (float) $params['rate_float'];
                        $request->updated_at = Carbon::createFromTimestampUTC(strtotime($info['time']['updatedISO']))->format('Y-m-d H:i:s');
                        $rate->save();
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }

    protected function live_rates_crawler($info, $source_id)
    {
        // BTC
        $this->info('Processing info via live rates crawler..');

        try {
            $info = json_decode(trim($info), true);

            if (is_array($info)) {
                $request = new Request;
                $request->source_id = (int) $source_id;
                $request->source_raw = json_encode($info);
                $request->save();

                foreach ($info as $params) {
                    if (! isset($params['error'])) {
                        if (in_array(mb_strtoupper($params['currency']), $this->currencies)) {
                            $rate = new Rate;
                            $rate->request_id = $request->id;
                            $rate->source_id = $source_id;
                            $rate->currency = mb_strtoupper($params['currency']);
                            $rate->rate = (float) $params['rate'];
                            $request->updated_at = Carbon::createFromTimestampUTC($params['timestamp'])->format('Y-m-d H:i:s');
                            $rate->save();
                        }
                    }
                }
            }
        } catch (\Exception $e) {

        }
    }

    protected function update_sources_handle()
    {
        try {
            if ($this->update_sources()) {
                $this->info('Sources updated!');
            }
        } catch (\Exception $e) {
            $this->error('Something went wrong.. (update_sources_handle method)');
        }
    }

    protected function update_sources()
    {
        $all_sources = Source::get();
        $all_urls = [];
        $add_sources = [];

        if ($all_sources->count()) {
            foreach ($all_sources as $source) {
                $key = array_search($source->url, array_column($this->default_sources, 'url'));
                $in_array = in_array($source->url, array_column($this->default_sources, 'url'));

                if ((! $in_array && $source->active) || ($source->active && $key && $in_array && ! $this->default_sources[$key]['active'])) {
                    $source->active = false;
                    $source->save();
                } elseif ((! $source->active && $key && $in_array && $this->default_sources[$key]['active'])) {
                    $source->active = true;
                    $source->save();
                }

                $all_urls[] = $source->url;
            }
        }

        if (count($this->default_sources)) {
            foreach ($this->default_sources as $key => $source) {
                if (! in_array($source['url'], $all_urls)) {
                    $add_sources[$key] = [];
                    $add_sources[$key]['url'] = $source['url'];
                    $add_sources[$key]['active'] = $source['active'];
                    $add_sources[$key]['method'] = $source['method'];
                }
            }
        }

        if (! empty($add_sources)) {
            foreach ($add_sources as $source) {
                $new_source = new Source;
                $new_source->url = $source['url'];
                $new_source->active = $source['active'];
                $new_source->method = $source['method'];
                $new_source->save();
            }
        }

        return true;
    }
}
