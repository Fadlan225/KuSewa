<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    /**
     * Menampilkan halaman daftar favorit.
     */
    public function index()
    {
        return Inertia::render('Home/Favorite');
    }

    /**
     * Menyimpan aset ke favorit.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Menghapus aset dari favorit.
     */
    public function destroy(Favorite $favorite)
    {
        //
    }
}