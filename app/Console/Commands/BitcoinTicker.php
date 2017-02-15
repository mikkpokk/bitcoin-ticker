<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BitcoinTicker extends Command
{
    protected $signature = 'bitcoin_ticker';
    protected $description = 'Crawls latest BitCoin - EUR/USD rates and inserts to the database.';

    public function handle()
    {
        $this->comment(PHP_EOL.'Here we are'.PHP_EOL);
    }
}
