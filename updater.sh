#!/bin/bash
for (( i = 0 ; i <= 59 ; i++ )); do
    #path/to/php path/to/project/artisan bitcoin_ticker

    /usr/local/Cellar/php70/7.0.12_5/bin/php /Users/mikkpokk/Documents/Sites/bitcoin-ticker/artisan bitcoin_ticker

    sleep 1
done
