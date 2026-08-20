<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use NotificationChannels\WebPush\PushSubscription;

class PushSubscriptionController extends Controller
{
    /**
     * Daftarkan subscription browser pengguna (dipanggil saat user klik "Izinkan Notifikasi").
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'    => 'required|url',
            'keys.auth'   => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $user = Auth::user();

        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'],
            $request->keys['auth'],
            $request->content_encoding ?? 'aesgcm'
        );

        return response()->json(['message' => 'Langganan notifikasi berhasil disimpan.']);
    }

    /**
     * Hapus subscription browser pengguna (dipanggil saat user menonaktifkan notifikasi).
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|url']);

        PushSubscription::findByEndpoint($request->endpoint)?->delete();

        return response()->json(['message' => 'Langganan notifikasi berhasil dihapus.']);
    }
}
