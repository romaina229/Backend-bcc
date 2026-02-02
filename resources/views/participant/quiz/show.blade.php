{{-- resources/views/participant/quiz/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- En-tête avec timer -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $quiz->title }}</h1>
            @if($quiz->time_limit)
            <div id="quiz-timer" class="bg-red-100 text-red-800 px-4 py-2 rounded-lg font-bold">
                Temps restant: <span id="time">00:{{ str_pad($quiz->time_limit, 2, '0', STR_PAD_LEFT) }}:00</span>
            </div>
            @endif
        </div>

        <!-- Progression -->
        <div class="mb-6">
            <div class="flex justify-between mb-2">
                <span>Question <span id="current-question">1</span>/{{ $questions->count() }}</span>
                <span id="progress-percentage">0%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div id="progress-bar" class="bg-bcc-primary h-2 rounded-full" style="width: 0%"></div>
            </div>
        </div>

        <form id="quiz-form" action="{{ route('participant.quiz.submit', $attempt) }}" method="POST">
            @csrf

            @foreach($questions as $index => $question)
            <div class="question-container mb-8 {{ $index !== 0 ? 'hidden' : '' }}" data-question="{{ $index + 1 }}">
                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">
                        Question {{ $index + 1 }}: {{ $question->question }}
                    </h3>
                    @if($question->points > 1)
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                        {{ $question->points }} points
                    </span>
                    @endif
                </div>

                <div class="space-y-3">
                    @if($question->type === 'unique')
                        @foreach(json_decode($question->options) as $key => $option)
                        <div class="flex items-center">
                            <input type="radio"
                                   id="q{{ $question->id }}_o{{ $key }}"
                                   name="answers[{{ $question->id }}]"
                                   value="{{ $key }}"
                                   class="h-4 w-4 text-bcc-primary focus:ring-bcc-primary">
                            <label for="q{{ $question->id }}_o{{ $key }}"
                                   class="ml-3 text-gray-700">
                                {{ $option }}
                            </label>
                        </div>
                        @endforeach

                    @elseif($question->type === 'multiple')
                        @foreach(json_decode($question->options) as $key => $option)
                        <div class="flex items-center">
                            <input type="checkbox"
                                   id="q{{ $question->id }}_o{{ $key }}"
                                   name="answers[{{ $question->id }}][]"
                                   value="{{ $key }}"
                                   class="h-4 w-4 text-bcc-primary rounded focus:ring-bcc-primary">
                            <label for="q{{ $question->id }}_o{{ $key }}"
                                   class="ml-3 text-gray-700">
                                {{ $option }}
                            </label>
                        </div>
                        @endforeach

                    @elseif($question->type === 'texte')
                        <textarea name="answers[{{ $question->id }}]"
                                  rows="4"
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:border-bcc-primary focus:ring focus:ring-bcc-primary focus:ring-opacity-50"
                                  placeholder="Votre réponse..."></textarea>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Navigation -->
            <div class="flex justify-between mt-8">
                <button type="button"
                        id="prev-btn"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    ← Précédent
                </button>

                <button type="button"
                        id="next-btn"
                        class="px-4 py-2 bg-bcc-primary text-white rounded-lg hover:bg-blue-800">
                    Suivant →
                </button>

                <button type="submit"
                        id="submit-btn"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 hidden">
                    Soumettre le quiz
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Timer JavaScript
@if($quiz->time_limit)
let timeLimit = {{ $quiz->time_limit * 60 }}; // en secondes
let timer = setInterval(() => {
    timeLimit--;
    let minutes = Math.floor(timeLimit / 60);
    let seconds = timeLimit % 60;
    document.getElementById('time').textContent =
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

    if (timeLimit <= 0) {
        clearInterval(timer);
        document.getElementById('quiz-form').submit();
    }
}, 1000);
@endif

// Navigation entre questions
let currentQuestion = 1;
const totalQuestions = {{ $questions->count() }};

function updateProgress() {
    const percentage = (currentQuestion / totalQuestions) * 100;
    document.getElementById('current-question').textContent = currentQuestion;
    document.getElementById('progress-percentage').textContent = `${Math.round(percentage)}%`;
    document.getElementById('progress-bar').style.width = `${percentage}%`;

    // Afficher/masquer les boutons
    document.getElementById('prev-btn').disabled = currentQuestion === 1;
    document.getElementById('next-btn').classList.toggle('hidden', currentQuestion === totalQuestions);
    document.getElementById('submit-btn').classList.toggle('hidden', currentQuestion !== totalQuestions);
}

document.getElementById('next-btn').addEventListener('click', () => {
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.add('hidden');
    currentQuestion++;
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.remove('hidden');
    updateProgress();
});

document.getElementById('prev-btn').addEventListener('click', () => {
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.add('hidden');
    currentQuestion--;
    document.querySelector(`.question-container[data-question="${currentQuestion}"]`).classList.remove('hidden');
    updateProgress();
});

updateProgress();
</script>
@endpush
