{{-- resources/views/participant/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard Participant')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Mes sessions -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Mes sessions</h3>
            <a href="{{ route('participant.sessions') }}" class="text-bcc-primary hover:text-blue-800">Voir tout</a>
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @foreach($mySessions as $session)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $session->formation->title }}</p>
                            <p class="text-sm text-gray-500">
                                Du {{ $session->start_date->format('d/m/Y') }} au {{ $session->end_date->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('participant.cours', $session->id) }}"
                                   class="text-bcc-primary hover:text-blue-800 text-sm">
                                    Cours
                                </a>
                                <a href="{{ route('participant.quizzes', $session->id) }}"
                                   class="text-bcc-primary hover:text-blue-800 text-sm">
                                    Quiz
                                </a>
                                <a href="{{ route('participant.assignments', $session->id) }}"
                                   class="text-bcc-primary hover:text-blue-800 text-sm">
                                    Devoirs
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Quiz à venir -->
    @if($upcomingQuizzes->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Quiz à venir</h3>
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @foreach($upcomingQuizzes as $quiz)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $quiz->title }}</p>
                            <p class="text-sm text-gray-500">{{ $quiz->description }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                {{ $quiz->time_limit }} min
                            </span>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Devoirs en attente -->
    @if($pendingAssignments->count() > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Devoirs en attente</h3>
        </div>
        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                @foreach($pendingAssignments as $assignment)
                <li class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $assignment->title }}</p>
                            <p class="text-sm text-gray-500">
                                À rendre avant le {{ $assignment->deadline->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <a href="{{ route('participant.assignments', $assignment->week->session_id) }}"
                               class="px-3 py-1 bg-bcc-primary text-white rounded-md text-sm hover:bg-blue-700">
                                Soumettre
                            </a>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif
</div>
@endsection
