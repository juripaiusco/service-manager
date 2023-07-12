<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class Dashboard extends Controller
{
    public function index()
    {
        $services = \App\Models\CustomerService::with('customer')
            ->orderBy('expiration')
            ->get();

        return Inertia::render('Dashboard/Dashboard', [

            'services' => $services

        ]);
    }
}
