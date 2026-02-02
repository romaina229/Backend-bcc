<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->get('per_page', 15);

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($notifications);
    }

    public function unread(Request $request)
    {
        $user = $request->user();

        $notifications = $user->unreadNotifications()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifications->count(),
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        // Vérifier que la notification appartient à l'utilisateur
        if ($notification->notifiable_id !== $request->user()->id) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marquée comme lue',
            'notification' => $notification
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Toutes les notifications ont été marquées comme lues'
        ]);
    }

    public function destroy($id)
    {
        $notification = DatabaseNotification::findOrFail($id);

        // Vérifier que la notification appartient à l'utilisateur
        if ($notification->notifiable_id !== auth()->id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification supprimée avec succès'
        ]);
    }

    public function destroyAll(Request $request)
    {
        $user = $request->user();
        
        $user->notifications()->delete();

        return response()->json([
            'message' => 'Toutes les notifications ont été supprimées'
        ]);
    }
}