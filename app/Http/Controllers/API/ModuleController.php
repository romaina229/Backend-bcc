<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function show($id)
    {
        $module = Module::with(['course', 'lessons' => function($query) {
            $query->where('actif', true)->orderBy('ordre');
        }])->findOrFail($id);

        return response()->json($module);
    }

    public function store(Request $request, $courseId)
    {
        $request->validate([
            'titre' => 'required|string|max:200',
            'description' => 'nullable|string',
            'duree' => 'nullable|integer',
            'ordre' => 'nullable|integer',
        ]);

        $module = Module::create([
            'titre' => $request->titre,
            'slug' => Str::slug($request->titre) . '-' . Str::random(6),
            'description' => $request->description,
            'cours_id' => $courseId,
            'duree' => $request->duree,
            'ordre' => $request->ordre ?? 0,
            'objectifs' => $request->objectifs,
            'prerequis' => $request->prerequis,
            'statut' => 'brouillon',
        ]);

        return response()->json([
            'message' => 'Module créé avec succès',
            'module' => $module
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $module = Module::findOrFail($id);

        $module->update($request->only([
            'titre',
            'description',
            'duree',
            'ordre',
            'actif',
            'objectifs',
            'prerequis',
            'statut',
        ]));

        return response()->json([
            'message' => 'Module mis à jour avec succès',
            'module' => $module
        ]);
    }

    public function destroy($id)
    {
        $module = Module::findOrFail($id);
        $module->delete();

        return response()->json(['message' => 'Module supprimé avec succès']);
    }
}