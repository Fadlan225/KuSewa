<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->when($request->role && $request->role !== 'Semua', function ($q) use ($request) {
                if ($request->role === 'Pemilik') {
                    $q->whereHas('ownerProfile');
                } elseif ($request->role === 'Penyewa') {
                    $q->whereDoesntHave('ownerProfile');
                }
            })
            ->with('ownerProfile')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total'    => User::count(),
            'active'   => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
        ];

        return Inertia::render('admin/UserAccountManagement', [
            'users'   => $query,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Toggle status aktif/nonaktif user (suspend).
     */
    public function toggleStatus(User $user)
    {
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        return back()->with('success', $newStatus === 'active'
            ? "Akun {$user->name} telah diaktifkan."
            : "Akun {$user->name} telah dinonaktifkan."
        );
    }

    /**
     * Hapus user permanen.
     * Akun dengan role admin tidak dapat dihapus.
     */
    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', "Akun administrator tidak dapat dihapus.");
        }

        $name = $user->name;

        try {
            $user->delete();
            return back()->with('success', "Akun {$name} berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return back()->with('error', "Akun {$name} tidak dapat dihapus karena masih memiliki data yang terhubung (misal: aset atau transaksi). Silakan hapus atau nonaktifkan data terkait terlebih dahulu.");
            }
            return back()->with('error', "Terjadi kesalahan saat menghapus akun {$name}.");
        }
    }
}
