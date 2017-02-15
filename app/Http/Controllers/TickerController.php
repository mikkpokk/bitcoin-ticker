<?php

namespace App\Http\Controllers;

class TickerController extends Controller
{
    public function index()
    {
        $output = trim(file_get_contents('https://blockchain.info/ticker?cors=true'));
        

        return json_decode($output, true);
    }
}
