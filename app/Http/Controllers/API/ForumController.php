<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ForumDiscussion;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    public function categories(Request $request)
    {
        $categories = ForumCategory::withCount(['discussions', 'posts'])
            ->orderBy('ordre')
            ->get();
            
        return response()->json($categories);
    }
    
    public function categoryDiscussions(Request $request, $id)
    {
        $perPage = $request->get('per_page', 15);
        
        $discussions = ForumDiscussion::where('categorie_id', $id)
            ->with(['user:id,nom,prenom,avatar', 'lastPost.user:id,nom,prenom'])
            ->withCount('posts')
            ->orderByDesc('est_epingle')
            ->orderByDesc('updated_at')
            ->paginate($perPage);
            
        return response()->json($discussions);
    }
    
    public function discussions(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $categoryId = $request->get('categorie_id');
        $courseId = $request->get('cours_id');
        $search = $request->get('search');
        $filter = $request->get('filter', 'recent');
        
        $query = ForumDiscussion::query()
            ->with(['user:id,nom,prenom,avatar', 'category:id,nom', 'lastPost.user:id,nom,prenom'])
            ->withCount('posts');
            
        if ($categoryId) {
            $query->where('categorie_id', $categoryId);
        }
        
        if ($courseId) {
            $query->where('cours_id', $courseId);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('titre', 'LIKE', "%{$search}%")
                  ->orWhere('contenu', 'LIKE', "%{$search}%");
            });
        }
        
        // Appliquer les filtres
        switch ($filter) {
            case 'popular':
                $query->orderByDesc('nombre_vues');
                break;
            case 'unanswered':
                $query->having('posts_count', '=', 0);
                break;
            case 'my_discussions':
                $query->where('user_id', $request->user()->id);
                break;
            case 'pinned':
                $query->where('est_epingle', true);
                break;
            default: // recent
                $query->orderByDesc('est_epingle')->orderByDesc('updated_at');
        }
        
        $discussions = $query->paginate($perPage);
        
        return response()->json($discussions);
    }
    
    public function createDiscussion(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:200',
            'contenu' => 'required|string|min:10',
            'categorie_id' => 'required|exists:forum_categories,id',
            'cours_id' => 'nullable|exists:courses,id',
            'tags' => 'nullable|array'
        ]);
        
        DB::beginTransaction();
        
        try {
            $discussion = ForumDiscussion::create([
                'titre' => $request->titre,
                'slug' => Str::slug($request->titre) . '-' . Str::random(6),
                'contenu' => $request->contenu,
                'user_id' => $request->user()->id,
                'categorie_id' => $request->categorie_id,
                'cours_id' => $request->cours_id,
                'tags' => $request->tags
            ]);
            
            // Créer le premier post (le message initial)
            $post = ForumPost::create([
                'contenu' => $request->contenu,
                'user_id' => $request->user()->id,
                'discussion_id' => $discussion->id,
                'est_premier_post' => true
            ]);
            
            // Mettre à jour la discussion avec le dernier post
            $discussion->update(['dernier_post_id' => $post->id]);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Discussion créée avec succès',
                'discussion' => $discussion->load(['user', 'category', 'firstPost'])
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erreur lors de la création'], 500);
        }
    }
    
    public function showDiscussion(Request $request, $id)
    {
        $discussion = ForumDiscussion::with([
            'user:id,nom,prenom,avatar,created_at',
            'category:id,nom,couleur',
            'course:id,titre,image',
            'posts.user:id,nom,prenom,avatar'
        ])->findOrFail($id);
        
        // Incrémenter le nombre de vues
        $discussion->incrementViews();
        
        // Marquer comme lu pour l'utilisateur
        if ($request->user()) {
            $request->user()->forumReads()->updateOrCreate(
                ['discussion_id' => $id],
                ['read_at' => now()]
            );
        }
        
        return response()->json($discussion);
    }
}
