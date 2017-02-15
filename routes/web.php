<?php

$app->get('/', [
    'as' => 'ticker',
    'uses' => 'TickerController@index',
]);
