<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class BookingController extends Controller
{
    /**
     * Menampilkan halaman Booking
     */
    public function BookingPage()
    {
        return Inertia::render('Home/Assets/BookingPage');
    }
}