<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use App\Models\ForumDiscussion;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function store(Request $request, $discussionId)
    {
        $request->validate([
            'contenu' => 'required|string|min:10',
        ]);

        $discussion = ForumDiscussion::findOrFail($discussionId);

        // Vérifier que la discussion n'est pas verrouillée
        if ($discussion->est_verrouille) {
            return response()->json([
                'message' => 'Cette discussion est verrouillée'
            ], 403);
        }

        $post = ForumPost::create([
            'contenu' => $request->contenu,
            'user_id' => $request->user()->id,
            'discussion_id' => $discussionId,
            'est_premier_post' => false,
        ]);

        // Mettre à jour la discussion
        $discussion->update([
            'dernier_post_id' => $post->id,
            'updated_at' => now(),
        ]);

        $post->load('user');

        return response()->json([
            'message' => 'Réponse ajoutée avec succès',
            'post' => $post
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'contenu' => 'required|string|min:10',
        ]);

        $post = ForumPost::findOrFail($id);

        // Vérifier que l'utilisateur est l'auteur
        if ($post->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $post->update([
            'contenu' => $request->contenu,
        ]);

        return response()->json([
            'message' => 'Message mis à jour avec succès',
            'post' => $post
        ]);
    }

    public function destroy($id)
    {
        $post = ForumPost::findOrFail($id);

        // Vérifier que l'utilisateur est l'auteur ou admin
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Ne pas supprimer le premier post
        if ($post->est_premier_post) {
            return response()->json([
                'message' => 'Le premier message ne peut pas être supprimé. Supprimez plutôt la discussion.'
            ], 400);
        }

        $post->delete();

        return response()->json([
            'message' => 'Message supprimé avec succès'
        ]);
    }

    public function markAsBestAnswer(Request $request, $id)
    {
        $post = ForumPost::findOrFail($id);
        $discussion = $post->discussion;

        // Seul l'auteur de la discussion ou un admin peut marquer
        if ($discussion->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // Retirer l'ancienne meilleure réponse
        ForumPost::where('discussion_id', $discussion->id)
            ->where('est_meilleure_reponse', true)
            ->update(['est_meilleure_reponse' => false]);

        // Marquer la nouvelle
        $post->update(['est_meilleure_reponse' => true]);

        return response()->json([
            'message' => 'Marqué comme meilleure réponse',
            'post' => $post
        ]);
    }
}