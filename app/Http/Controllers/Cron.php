<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Cron extends Controller
{
    public function getFattureToday()
    {
        $fic = new FattureInCloudAPI();
        $fic->getFattureToday();
    }
}
