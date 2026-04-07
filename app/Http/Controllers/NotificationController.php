<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            $notifications = $user->notifications()->latest()->limit(50)->get();
            
            return Response::Ok('Berhasil mengambil notifikasi', [
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count()
            ]);
        } catch (\Throwable $th) {
            return Response::Error('Gagal mengambil notifikasi', $th->getMessage());
        }
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(string $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->markAsRead();

            return Response::Ok('Notifikasi ditandai telah dibaca', null);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menandai notifikasi', $th->getMessage());
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();

            return Response::Ok('Semua notifikasi telah dibaca', null);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menandai semua notifikasi', $th->getMessage());
        }
    }

    /**
     * Delete a specific notification.
     */
    public function destroy(string $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->delete();

            return Response::Ok('Notifikasi berhasil dihapus', null);
        } catch (\Throwable $th) {
            return Response::Error('Gagal menghapus notifikasi', $th->getMessage());
        }
    }
}
