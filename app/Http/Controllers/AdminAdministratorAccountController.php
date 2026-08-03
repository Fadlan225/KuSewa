<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminAdministratorAccountController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::where('role', 'admin')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'email', 'role', 'status', 'created_at'])
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role === 'admin' ? 'Admin' : ucfirst($user->role),
                    'status' => $user->status === 'active' ? 'Aktif' : 'Tidak Aktif',
                    'joined' => $user->created_at->translatedFormat('j F Y'),
                ];
            });

        return Inertia::render('admin/AdministratorAccountManagement', [
            'admins' => $admins,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'string', 'max:50'],
            'status' => ['required', 'in:Aktif,Tidak Aktif'],
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('Password123!'),
            'role' => 'admin',
            'status' => $validated['status'] === 'Aktif' ? 'active' : 'inactive',
        ]);

        return redirect()->route('admin.admin-accounts')
            ->with('success', "Akun admin \"{$admin->name}\" berhasil dibuat.");
    }
}
