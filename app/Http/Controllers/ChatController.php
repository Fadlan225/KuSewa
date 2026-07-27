<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\room_chat;
use App\Models\message;
use App\Models\owner_profile;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Tampilkan halaman chat (fullscreen)
     */
    public function index()
    {
        return Inertia::render('Home/Chat');
    }

    /**
     * Endpoint API: Ambil daftar percakapan (room chat) milik user yang sedang login
     */
    public function getChats()
    {
        $userId = Auth::id();

        // Cari room_chats dimana user ini adalah customer ATAU dia adalah owner dari profil tersebut
        $chats = room_chat::with(['user', 'ownerProfile.user', 'asset.images', 'messages' => function($q) {
                $q->latest()->limit(1);
            }])
            ->where('user_id', $userId)
            ->orWhereHas('ownerProfile', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->get()
            ->map(function ($chat) use ($userId) {
                // Menentukan lawan bicara
                $isOwner = $chat->ownerProfile->user_id === $userId;
                $contact = $isOwner ? $chat->user : $chat->ownerProfile->user;

                // Hitung pesan yang belum dibaca dari lawan bicara
                $unread = $chat->messages->where('sender_id', '!=', $userId)->where('is_read', 0)->count();
                $lastMsg = $chat->messages->first();

                // Format sesuai kebutuhan UI frontend (Chat.vue dan FloatingChat.vue)
                return [
                    'id' => $chat->id,
                    'name' => $contact->name,
                    'avatarText' => strtoupper(substr($contact->name, 0, 2)),
                    'avatar' => !empty($contact->profile_photo) ? asset('storage/'.$contact->profile_photo) : null,
                    'assetName' => $chat->asset->title ?? 'Aset Dihapus',
                    'assetImage' => $chat->asset->images->first() ? $chat->asset->images->first()->image_url : null,
                    'isOnline' => true, // Dummy untuk UI
                    'lastMessage' => $lastMsg ? $lastMsg->message : '',
                    'time' => $lastMsg ? $lastMsg->created_at->format('H:i') : '',
                    'unread' => $unread
                ];
            });

        return response()->json($chats);
    }

    /**
     * Endpoint API: Ambil pesan dari room tertentu
     */
    public function getMessages(room_chat $room)
    {
        $userId = Auth::id();
        
        // Cek otorisasi
        if ($room->user_id !== $userId && $room->ownerProfile->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Tandai sudah dibaca untuk pesan lawan bicara
        $room->messages()->where('sender_id', '!=', $userId)->update(['is_read' => true]);

        $messages = $room->messages()->orderBy('created_at', 'asc')->get()->map(function($msg) use ($userId) {
            return [
                'id' => $msg->id,
                'text' => $msg->message,
                'isSender' => $msg->sender_id === $userId,
                'isSelf' => $msg->sender_id === $userId, // alias untuk Vue
                'time' => $msg->created_at->format('H:i'),
                'dateLabel' => $this->formatDateLabel($msg->created_at)
            ];
        });

        // Price formatting helpers
        $periodLabel = [
            'hour' => 'jam',
            'day' => 'hari',
            'night' => 'malam',
            'month' => 'bulan'
        ];

        $lowestPrice = null;
        $priceLabel = '';
        if ($room->asset && $room->asset->pricings && $room->asset->pricings->count() > 0) {
            $lowestPrice = $room->asset->pricings->sortBy('price')->first();
            $priceLabel = 'Rp ' . number_format($lowestPrice->price, 0, ',', '.') . ' / ' . ($periodLabel[$lowestPrice->period] ?? 'opsi');
        }

        return response()->json([
            'messages' => $messages,
            'room' => $room->load(['asset']),
            'priceLabel' => $priceLabel
        ]);
    }

    /**
     * Endpoint API: Kirim pesan
     */
    public function sendMessage(Request $request, room_chat $room)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $userId = Auth::id();

        // Cek otorisasi
        if ($room->user_id !== $userId && $room->ownerProfile->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $msg = message::create([
            'room_chat_id' => $room->id,
            'sender_id' => $userId,
            'message' => $request->message,
            'is_read' => false,
            'message_type' => 'text'
        ]);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'text' => $msg->message,
                'isSender' => true,
                'isSelf' => true,
                'time' => $msg->created_at->format('H:i'),
                'dateLabel' => $this->formatDateLabel($msg->created_at)
            ]
        ]);
    }

    private function formatDateLabel($date) {
        if ($date->isToday()) {
            return 'Hari Ini';
        } elseif ($date->isYesterday()) {
            return 'Kemarin';
        } else {
            return $date->translatedFormat('d F Y');
        }
    }

    /**
     * Web Route: Mulai chat dari halaman detail aset (Hubungi Pemilik)
     */
    public function startChat(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'owner_profile_id' => 'required|exists:owner_profiles,id'
        ]);

        $userId = Auth::id();

        // Mencegah chat ke diri sendiri
        $owner = owner_profile::find($request->owner_profile_id);
        if ($owner->user_id === $userId) {
            return back()->with('error', 'Anda tidak bisa mengirim pesan ke diri sendiri.');
        }

        // Cari apakah room sudah ada
        $room = room_chat::where('user_id', $userId)
            ->where('asset_id', $request->asset_id)
            ->where('owner_profile_id', $request->owner_profile_id)
            ->first();

        // Jika belum ada, buat room baru
        if (!$room) {
            $room = room_chat::create([
                'user_id' => $userId,
                'asset_id' => $request->asset_id,
                'owner_profile_id' => $request->owner_profile_id
            ]);
        }

        // Opsional: Jika ada pesan awal ('message' via request), buat pesan
        if ($request->filled('message')) {
            message::create([
                'room_chat_id' => $room->id,
                'sender_id' => $userId,
                'message' => $request->message,
                'is_read' => false,
                'message_type' => 'text'
            ]);
        }

        return redirect()->route('chat.index', ['room_id' => $room->id]);
    }
}
