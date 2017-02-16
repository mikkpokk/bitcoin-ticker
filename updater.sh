#!/bin/bash
for (( i = 0 ; i <= 59 ; i++ )); do
    php artisan bitcoin_ticker
    sleep 1
done
