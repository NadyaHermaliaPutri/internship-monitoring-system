<?php

namespace App\Http\Controllers;

use App\Services\HeaderNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected HeaderNotificationService $headerNotificationService
    ) {}

    /**
     * Endpoint JSON ringan untuk live polling notifikasi in-app.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, 'Akun Anda tidak aktif.');

        $feed = $this->headerNotificationService->forUser($user, 8);

        return response()->json([
            'unread_count' => $feed['count'],
            'unread_db_count' => $feed['unread_db_count'],
            'items' => $feed['items'],
        ]);
    }
    /**
     * Menampilkan daftar notifikasi milik pengguna yang sedang login.
     */
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, 'Akun Anda tidak aktif.');

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        if ($request->wantsJson()) {
            return response()->json([
                'unread_count' => $unreadCount,
                'notifications' => $notifications,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Menandai satu notifikasi milik pengguna sebagai sudah dibaca.
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, 'Akun Anda tidak aktif.');

        // Otorisasi ketat: hanya boleh mengakses notifikasi milik dirinya sendiri (404 jika milik user lain)
        $notification = $user->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $actionUrl = $notification->data['action_url'] ?? null;

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'read_at' => $notification->fresh()->read_at,
                'action_url' => $actionUrl,
            ]);
        }

        return $actionUrl
            ? redirect()->to($actionUrl)
            : redirect()->back();
    }

    /**
     * Menandai seluruh notifikasi belum dibaca milik pengguna sebagai sudah dibaca.
     */
    public function markAllAsRead(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        abort_unless($user && $user->is_active, 403, 'Akun Anda tidak aktif.');

        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'Seluruh notifikasi telah ditandai sebagai dibaca.');
    }
}
