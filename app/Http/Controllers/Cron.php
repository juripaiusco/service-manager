<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Cron extends Controller
{
    public function getDocToday()
    {
        $fic = new FattureInCloudAPI();
        $fic->getDocToday();
    }
}
