{{-- resources/views/formateur/submissions/grade.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- En-tête -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Correction du devoir</h1>
            <div class="text-gray-600">
                Participant: <strong>{{ $submission->participant->name }}</strong> |
                Devoir: <strong>{{ $submission->assignment->title }}</strong> |
                Soumis le: {{ $submission->submitted_at->format('d/m/Y H:i') }}
            </div>

            @if($submission->is_late)
            <div class="mt-2 bg-red-100 text-red-800 px-4 py-2 rounded-lg inline-block">
                ⚠️ Soumis en retard
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Fichier soumis -->
            <div class="lg:col-span-2">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Fichier soumis</h2>

                    @if(in_array($submission->file_extension, ['pdf', 'doc', 'docx', 'txt']))
                        @if($submission->file_extension === 'pdf')
                        <iframe src="{{ Storage::url($submission->file_path) }}"
                                class="w-full h-96 rounded-lg border"></iframe>
                        @else
                        <div class="p-4 bg-white rounded-lg border">
                            <a href="{{ Storage::url($submission->file_path) }}"
                               target="_blank"
                               class="flex items-center text-bcc-primary hover:underline">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Télécharger le fichier ({{ strtoupper($submission->file_extension) }})
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Prévisualisation non disponible
                        </div>
                    @endif

                    <!-- Commentaire du participant -->
                    @if($submission->comment)
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-700 mb-2">Commentaire du participant:</h3>
                        <div class="bg-blue-50 p-4 rounded-lg">
                            {{ $submission->comment }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Formulaire de notation -->
            <div class="lg:col-span-1">
                <form id="grading-form" action="{{ route('formateur.submissions.update', $submission) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="bg-gray-50 rounded-lg p-6">
                        <h2 class="text-xl font-semibold mb-4">Grille d'évaluation</h2>

                        @foreach($submission->assignment->rubrics as $rubric)
                        <div class="mb-6">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-semibold text-gray-800">{{ $rubric->criterion }}</h3>
                                    @if($rubric->description)
                                    <p class="text-sm text-gray-600">{{ $rubric->description }}</p>
                                    @endif
                                </div>
                                <span class="text-bcc-primary font-semibold">{{ $rubric->max_points }} pts</span>
                            </div>

                            <input type="number"
                                   name="grades[{{ $rubric->id }}][points]"
                                   min="0"
                                   max="{{ $rubric->max_points }}"
                                   step="0.5"
                                   value="{{ old('grades.' . $rubric->id . '.points', $submission->grades->where('rubric_id', $rubric->id)->first()->points_awarded ?? 0) }}"
                                   class="w-full border-gray-300 rounded-lg focus:border-bcc-primary focus:ring focus:ring-bcc-primary">

                            <div class="mt-2">
                                <textarea name="grades[{{ $rubric->id }}][feedback]"
                                          placeholder="Commentaire..."
                                          rows="2"
                                          class="w-full text-sm border-gray-300 rounded-lg focus:border-bcc-primary focus:ring focus:ring-bcc-primary">{{ old('grades.' . $rubric->id . '.feedback', $submission->grades->where('rubric_id', $rubric->id)->first()->feedback ?? '') }}</textarea>
                            </div>
                        </div>
                        @endforeach

                        <!-- Note globale -->
                        <div class="mb-6 p-4 bg-white rounded-lg border">
                            <div class="flex justify-between items-center mb-2">
                                <span class="font-semibold">Note totale</span>
                                <span id="total-score" class="text-2xl font-bold text-bcc-primary">0</span>
                            </div>
                            <div class="text-center">
                                <span id="percentage" class="text-lg font-semibold">0%</span>
                                <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                    <div id="percentage-bar" class="bg-bcc-primary h-2 rounded-full"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Feedback général -->
                        <div class="mb-6">
                            <label class="block font-semibold text-gray-700 mb-2">Feedback général</label>
                            <textarea name="general_feedback"
                                      rows="4"
                                      class="w-full border-gray-300 rounded-lg focus:border-bcc-primary focus:ring focus:ring-bcc-primary"
                                      placeholder="Commentaires généraux sur le devoir...">{{ old('general_feedback', $submission->feedback) }}</textarea>
                        </div>

                        <!-- Actions -->
                        <div class="space-y-3">
                            <button type="submit"
                                    class="w-full px-4 py-3 bg-bcc-primary text-white rounded-lg hover:bg-blue-800 font-semibold">
                                Enregistrer la note
                            </button>

                            <button type="button"
                                    id="save-draft"
                                    class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Enregistrer comme brouillon
                            </button>

                            <a href="{{ route('formateur.submissions.index') }}"
                               class="block text-center px-4 py-3 text-gray-600 hover:text-gray-800">
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Calcul dynamique de la note totale
function calculateTotalScore() {
    let total = 0;
    let maxTotal = 0;

    // Calculer la note actuelle
    document.querySelectorAll('input[type="number"][name^="grades"]').forEach(input => {
        const max = parseFloat(input.max);
        const value = parseFloat(input.value) || 0;
        total += value;
        maxTotal += max;
    });

    // Mettre à jour l'affichage
    document.getElementById('total-score').textContent = total.toFixed(1);

    const percentage = maxTotal > 0 ? (total / maxTotal) * 100 : 0;
    document.getElementById('percentage').textContent = percentage.toFixed(1) + '%';
    document.getElementById('percentage-bar').style.width = percentage + '%';
}

// Écouter les changements dans les champs de note
document.querySelectorAll('input[type="number"][name^="grades"]').forEach(input => {
    input.addEventListener('input', calculateTotalScore);
});

// Enregistrement comme brouillon
document.getElementById('save-draft').addEventListener('click', function() {
    const form = document.getElementById('grading-form');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Draft': 'true'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Brouillon enregistré avec succès', 'success');
        }
    });
});

// Calcul initial
calculateTotalScore();
</script>
@endpush
