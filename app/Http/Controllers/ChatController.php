<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\room_chat;
use App\Models\message;
use App\Models\owner_profile;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;

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
            ->withCount(['messages as unread' => function($q) use ($userId) {
                $q->where('sender_id', '!=', $userId)->where('is_read', false);
            }])
            ->where(function($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhereHas('ownerProfile', function ($query) use ($userId) {
                      $query->where('user_id', $userId);
                  });
            })
            ->get()
            ->map(function ($chat) use ($userId) {
                // Menentukan lawan bicara
                $isOwner = $chat->ownerProfile->user_id === $userId;
                $contact = $isOwner ? $chat->user : $chat->ownerProfile->user;

                // Hitung pesan yang belum dibaca dari lawan bicara (menggunakan hasil withCount)
                $unread = $chat->unread;
                $lastMsg = $chat->messages->first();

                // Format sesuai kebutuhan UI frontend (Chat.vue dan FloatingChat.vue)
                return [
                    'id' => $chat->id,
                    'name' => $contact->name,
                    'isContactOwner' => !$isOwner,
                    'avatarText' => strtoupper(substr($contact->name, 0, 2)),
                    'avatar' => !empty($contact->profile_photo) ? asset('storage/'.$contact->profile_photo) : null,
                    'assetName' => $chat->asset->title ?? 'Aset Dihapus',
                    'assetSlug' => $chat->asset->slug ?? null,
                    'assetId' => $chat->asset_id,
                    'assetImage' => $chat->asset->images->first() ? $chat->asset->images->first()->image_url : null,
                    'isOnline' => true, // Dummy untuk UI
                    'lastMessage' => $lastMsg ? (
                        $lastMsg->message_type === 'image' ? 'Foto' :
                        ($lastMsg->message_type === 'file' ? preg_replace('/^\d+_/', '', basename($lastMsg->message)) : $lastMsg->message)
                    ) : '',
                    'lastMessageType' => $lastMsg ? $lastMsg->message_type : 'text',
                    'isLastMessageSelf' => $lastMsg ? $lastMsg->sender_id === $userId : false,
                    'isLastMessageRead' => $lastMsg ? (bool)$lastMsg->is_read : false,
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

        $messages = $room->messages()
            ->withTrashed()
            ->with(['attachments', 'replyTo' => function($q){ 
                $q->withTrashed()->with('sender'); 
            }])
            ->orderBy('created_at', 'asc')->get()->map(function($msg) use ($userId) {
            $hasAttachments = $msg->attachments->count() > 0;
            $attachments = $hasAttachments ? $msg->attachments->map(function($att) {
                return [
                    'id' => $att->id,
                    'file_url' => asset('storage/' . $att->file_path),
                    'file_name' => basename($att->file_path)
                ];
            })->toArray() : [];

            // Untuk backward compatibility dengan pesan lama yang menyimpannya di kolom message
            $isOldImage = $msg->message_type === 'image' && !$hasAttachments;
            $fileUrl = $isOldImage ? asset('storage/' . $msg->message) : null;
            $fileName = $isOldImage ? basename($msg->message) : null;

            $text = ($msg->message_type === 'text' || $hasAttachments) ? $msg->message : null;
            if ($hasAttachments && !empty($attachments) && $text === $msg->attachments->first()->file_path) {
                $text = null;
            }

            return [
                'id' => $msg->id,
                'type' => $msg->message_type,
                'text' => $text,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'attachments' => $attachments,
                'isSender' => $msg->sender_id === $userId,
                'isSelf' => $msg->sender_id === $userId, // alias untuk Vue
                'time' => $msg->created_at->format('H:i'),
                'timestamp' => $msg->created_at->toIso8601String(),
                'readTime' => $msg->is_read ? $msg->updated_at->format('H:i') : null,
                'dateLabel' => $this->formatDateLabel($msg->created_at),
                'isRead' => (bool)$msg->is_read,
                'isEdited' => (bool)$msg->is_edited,
                'isDeleted' => $msg->trashed(),
                'replyTo' => $msg->replyTo ? [
                    'id' => $msg->replyTo->id,
                    'text' => $msg->replyTo->trashed() ? 'Pesan ini telah dihapus' : ($msg->replyTo->message_type === 'image' ? 'Foto' : ($msg->replyTo->message_type === 'file' ? 'File' : $msg->replyTo->message)),
                    'sender_name' => $msg->replyTo->sender_id === $userId ? 'Anda' : ($msg->replyTo->sender->name ?? 'Lawan bicara'),
                    'isSelf' => $msg->replyTo->sender_id === $userId
                ] : null
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
        $userId = Auth::id();

        // Cek otorisasi
        if ($room->user_id !== $userId && $room->ownerProfile->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$request->message && !$request->hasFile('file') && !$request->hasFile('files')) {
            return response()->json(['error' => 'Message or files are required'], 422);
        }

        // Filter Regex untuk teks (caption/message)
        if ($request->message) {
            $text = $request->message;
            $hasPhone = preg_match('/(0|\+?62)[0-9\s\-]{8,15}/', $text);
            $hasEmail = preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text);
            $hasLink = preg_match('/(https?:\/\/[^\s]+)|(www\.[^\s]+)|([a-zA-Z0-9.-]+\.(com|id|net|org|co\.id|me)(\/[^\s]*)?)/i', $text);

            if ($hasPhone || $hasEmail || $hasLink) {
                return response()->json(['error' => 'Pesan melanggar kebijakan: Dilarang mengirimkan Nomor HP, Email, atau Link.'], 422);
            }
        }

        $messageContent = $request->message;
        $messageType = 'text';
        $uploadedPaths = [];

        // Support 'files[]' array or single 'file' for backward compat
        $files = $request->hasFile('files') ? $request->file('files') : ($request->hasFile('file') ? [$request->file('file')] : []);

        if (count($files) > 30) {
            return response()->json(['error' => 'Maksimal 30 gambar dalam satu kali kirim.'], 422);
        }

        if (count($files) > 0) {
            foreach ($files as $file) {
                // Validasi File Manual (Hanya Gambar)
                $extension = strtolower($file->getClientOriginalExtension());
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (!in_array($extension, $allowedExtensions)) {
                    return response()->json(['error' => 'File melanggar kebijakan: Hanya menerima gambar (JPG, PNG, WebP).'], 422);
                }
                if ($file->getSize() > 5 * 1024 * 1024) {
                    return response()->json(['error' => 'Ukuran salah satu gambar lebih dari 5MB.'], 422);
                }

                $originalName = $file->getClientOriginalName();
                $path = $file->storeAs('chat_attachments', time() . '_' . uniqid() . '_' . $originalName, 'public');
                $uploadedPaths[] = $path;
            }
            
            $messageType = 'image';
            if (empty($messageContent)) {
                $messageContent = $uploadedPaths[0];
            }
        }

        $msg = message::create([
            'room_chat_id' => $room->id,
            'sender_id' => $userId,
            'message' => $messageContent,
            'is_read' => false,
            'message_type' => $messageType,
            'reply_to_id' => $request->reply_to_id
        ]);

        // Simpan attachments
        $attachmentsResponse = [];
        foreach ($uploadedPaths as $path) {
            $att = $msg->attachments()->create([
                'file_path' => $path
            ]);
            $attachmentsResponse[] = [
                'id' => $att->id,
                'file_url' => asset('storage/' . $path),
                'file_name' => basename($path)
            ];
        }

        $msg->load('attachments');

        $startTime = microtime(true);
        broadcast(new MessageSent($msg, $room->id));
        $endTime = microtime(true);

        \Log::info("Broadcast took " . ($endTime - $startTime) . " seconds.");

        $hasAttachments = count($attachmentsResponse) > 0;
        $text = $msg->message;
        if ($hasAttachments && $text === $uploadedPaths[0]) {
            $text = null;
        }

        $msg->load(['attachments', 'replyTo.sender']);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $msg->id,
                'type' => $messageType,
                'text' => $text,
                'file_url' => null,
                'file_name' => null,
                'attachments' => $attachmentsResponse,
                'isSender' => true,
                'isSelf' => true,
                'time' => $msg->created_at->format('H:i'),
                'timestamp' => $msg->created_at->toIso8601String(),
                'readTime' => null,
                'isRead' => false,
                'dateLabel' => $this->formatDateLabel($msg->created_at),
                'isEdited' => false,
                'replyTo' => $msg->replyTo ? [
                    'id' => $msg->replyTo->id,
                    'text' => $msg->replyTo->message_type === 'image' ? 'Foto' : ($msg->replyTo->message_type === 'file' ? 'File' : $msg->replyTo->message),
                    'sender_name' => $msg->replyTo->sender_id === $userId ? 'Anda' : ($msg->replyTo->sender->name ?? 'Lawan bicara'),
                    'isSelf' => $msg->replyTo->sender_id === $userId
                ] : null
            ]
        ]);
    }

    /**
     * Endpoint API: Edit pesan
     */
    public function updateMessage(Request $request, room_chat $room, message $message)
    {
        $userId = Auth::id();

        if ($message->sender_id !== $userId) {
            return response()->json(['error' => 'Hanya bisa mengedit pesan sendiri'], 403);
        }

        if (now()->diffInMinutes($message->created_at) >= 5) {
            return response()->json(['error' => 'Pesan hanya dapat diedit dalam waktu 5 menit setelah dikirim.'], 403);
        }

        $hasReplies = \App\Models\message::where('reply_to_id', $message->id)->exists();
        if ($hasReplies) {
            return response()->json(['error' => 'Pesan yang sudah dibalas tidak dapat diedit'], 403);
        }

        if (!$request->message) {
            return response()->json(['error' => 'Message is required'], 422);
        }

        $text = $request->message;
        $hasPhone = preg_match('/(0|\+?62)[0-9\s\-]{8,15}/', $text);
        $hasEmail = preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text);
        $hasLink = preg_match('/(https?:\/\/[^\s]+)|(www\.[^\s]+)|([a-zA-Z0-9.-]+\.(com|id|net|org|co\.id|me)(\/[^\s]*)?)/i', $text);

        if ($hasPhone || $hasEmail || $hasLink) {
            return response()->json(['error' => 'Pesan melanggar kebijakan.'], 422);
        }

        // If it's an image with attachments, the text might be the caption.
        // We just update the `message` field, which acts as text/caption.
        $message->update([
            'message' => $text,
            'is_edited' => true
        ]);

        // Broadcast MessageUpdated
        broadcast(new \App\Events\MessageUpdated($message, $room->id));

        return response()->json([
            'success' => true,
            'message' => $message->message
        ]);
    }

    /**
     * Endpoint API: Delete pesan
     */
    public function deleteMessage(room_chat $room, message $message)
    {
        $userId = Auth::id();

        if ($message->sender_id !== $userId) {
            return response()->json(['error' => 'Hanya bisa menghapus pesan sendiri'], 403);
        }

        $message->delete(); // Soft delete

        // Broadcast MessageDeleted
        broadcast(new \App\Events\MessageDeleted($message->id, $room->id));

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Endpoint API: Tandai pesan di room telah dibaca
     */
    public function markAsRead(room_chat $room)
    {
        $userId = Auth::id();

        if ($room->user_id !== $userId && $room->ownerProfile->user_id !== $userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $room->messages()->where('sender_id', '!=', $userId)->update(['is_read' => true]);

        return response()->json(['success' => true]);
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
