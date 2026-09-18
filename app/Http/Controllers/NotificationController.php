<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Ambil daftar notifikasi milik user yang sedang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $perPage = $request->get('per_page', 20);
        $onlyUnread = $request->boolean('unread');

        $query = $user->notifications();

        if ($onlyUnread) {
            $query->whereNull('read_at');
        }

        $dbNotifications = $query->latest()->paginate($perPage);

        $items = $dbNotifications->items();
        $unreadCount = $user->unreadNotifications()->count();

        // Cek kelengkapan profil untuk notifikasi virtual
        $filledFields = 0;
        $profileFields = [
            'name', 'email', 'phone', 'gender',
            'date_of_birth', 'place_of_birth_code', 'marital_status',
            'occupation', 'nationality'
        ];
        
        foreach ($profileFields as $field) {
            if (!empty($user->{$field})) {
                $filledFields++;
            }
        }
        
        if (!empty($user->profile_photo) || !empty($user->avatar)) {
            $filledFields++;
        }

        if ($filledFields < 10 && $user->role !== 'admin') {
            $unreadCount += 1;
            $virtualNotification = [
                'id' => 'virtual-profile-completion',
                'type' => 'App\\Notifications\\ProfileCompletionNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $user->id,
                'data' => [
                    'title' => 'Lengkapi Profil Anda',
                    'message' => 'Pastikan profil Anda jelas dan lengkap agar lebih disukai oleh pemilik aset.',
                    'action_url' => '/profile',
                ],
                'read_at' => null,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];

            // Sisipkan di urutan teratas
            array_unshift($items, $virtualNotification);
        }

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $dbNotifications->currentPage(),
                'last_page'    => $dbNotifications->lastPage(),
                'total'        => $dbNotifications->total() + (($filledFields < 10 && $user->role !== 'admin') ? 1 : 0),
            ],
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Tandai satu notifikasi telah dibaca.
     */
    public function markAsRead(string $id): JsonResponse
    {
        // Abaikan jika ini adalah notifikasi virtual
        if ($id === 'virtual-profile-completion') {
            return response()->json(['message' => 'Notifikasi virtual tidak bisa di-mark as read secara manual. Selesaikan tugas untuk menghilangkan notifikasi.']);
        }

        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notifikasi telah ditandai sudah dibaca.']);
    }

    /**
     * Tandai semua notifikasi telah dibaca.
     */
    public function markAllAsRead(): JsonResponse
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Semua notifikasi telah ditandai sudah dibaca.']);
    }

    /**
     * Ambil jumlah notifikasi yang belum dibaca (untuk badge di navbar).
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $unreadCount = $user->unreadNotifications()->count();
        
        $filledFields = 0;
        $profileFields = [
            'name', 'email', 'phone', 'gender',
            'date_of_birth', 'place_of_birth_code', 'marital_status',
            'occupation', 'nationality'
        ];
        
        foreach ($profileFields as $field) {
            if (!empty($user->{$field})) {
                $filledFields++;
            }
        }
        
        if (!empty($user->profile_photo) || !empty($user->avatar)) {
            $filledFields++;
        }

        if ($filledFields < 10 && $user->role !== 'admin') {
            $unreadCount += 1;
        }

        return response()->json([
            'count' => $unreadCount,
        ]);
    }

    /**
     * Hapus satu notifikasi.
     */
    public function destroy(string $id): JsonResponse
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        return response()->json(['message' => 'Notifikasi dihapus.']);
    }
}
