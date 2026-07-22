<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentController extends Controller
{
    public function show(Request $request)
    {
        return Inertia::render('Home/Assets/PaymentPage', [
            'bookingId' => $request->query('booking_id', '1377346270'),
        ]);
    }
}