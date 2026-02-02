{{-- resources/views/participant/forum.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Liste des threads -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow-lg">
                <!-- En-tête -->
                <div class="p-6 border-b">
                    <div class="flex justify-between items-center">
                        <h1 class="text-2xl font-bold text-gray-800">Forum - Semaine {{ $week->week_number }}</h1>
                        <button id="new-thread-btn"
                                class="px-4 py-2 bg-bcc-primary text-white rounded-lg hover:bg-blue-800">
                            Nouveau sujet
                        </button>
                    </div>
                    <p class="text-gray-600 mt-2">{{ $week->theme }}</p>
                </div>

                <!-- Liste des discussions -->
                <div id="threads-container" class="p-6">
                    <!-- Chargé dynamiquement -->
                    <div class="text-center py-8">
                        <div class="spinner"></div>
                        <p class="text-gray-500 mt-2">Chargement des discussions...</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div id="pagination-container" class="p-6 border-t">
                    <!-- Pagination dynamique -->
                </div>
            </div>
        </div>

        <!-- Participants en ligne -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <span id="online-count">0</span> Participants en ligne
                </h2>
                <div id="online-users" class="space-y-3">
                    <!-- Liste dynamique -->
                </div>

                <!-- Statistiques -->
                <div class="mt-8 pt-6 border-t">
                    <h3 class="font-semibold text-gray-700 mb-3">Statistiques du forum</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Sujets</span>
                            <span id="threads-count" class="font-semibold">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Réponses</span>
                            <span id="replies-count" class="font-semibold">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Votre participation</span>
                            <span id="user-posts" class="font-semibold">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal nouveau thread -->
<div id="new-thread-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Nouveau sujet de discussion</h3>
                <form id="new-thread-form">
                    @csrf
                    <input type="hidden" name="week_id" value="{{ $week->id }}">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Titre</label>
                        <input type="text"
                               name="title"
                               required
                               class="w-full border-gray-300 rounded-lg focus:border-bcc-primary focus:ring focus:ring-bcc-primary">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Contenu</label>
                        <textarea name="content"
                                  rows="6"
                                  required
                                  class="w-full border-gray-300 rounded-lg focus:border-bcc-primary focus:ring focus:ring-bcc-primary"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button"
                                id="cancel-thread"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Annuler
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-bcc-primary text-white rounded-lg hover:bg-blue-800">
                            Publier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Initialiser Echo
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    wsHost: window.location.hostname,
    wsPort: 6001,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
});

// Rejoindre le canal du forum
const channel = window.Echo.join(`forum.week.{{ $week->id }}`)
    .here((users) => {
        updateOnlineUsers(users);
    })
    .joining((user) => {
        addOnlineUser(user);
    })
    .leaving((user) => {
        removeOnlineUser(user);
    })
    .listen('new-message', (data) => {
        addNewMessage(data);
    })
    .error((error) => {
        console.error('Erreur de connexion:', error);
    });

// Fonctions de gestion des utilisateurs en ligne
function updateOnlineUsers(users) {
    document.getElementById('online-count').textContent = users.length;
    const container = document.getElementById('online-users');
    container.innerHTML = users.map(user => `
        <div class="flex items-center">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
            <span class="text-gray-700">${user.name}</span>
        </div>
    `).join('');
}

function addOnlineUser(user) {
    const count = parseInt(document.getElementById('online-count').textContent);
    document.getElementById('online-count').textContent = count + 1;

    const container = document.getElementById('online-users');
    container.innerHTML += `
        <div class="flex items-center">
            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
            <span class="text-gray-700">${user.name}</span>
        </div>
    `;
}

function removeOnlineUser(user) {
    const count = parseInt(document.getElementById('online-count').textContent);
    if (count > 0) {
        document.getElementById('online-count').textContent = count - 1;
    }
}

// Fonction pour ajouter un nouveau message
function addNewMessage(data) {
    // Mettre à jour l'affichage
    console.log('Nouveau message:', data);

    // Si nous sommes dans le thread correspondant, ajouter le message
    if (currentThreadId === data.message.thread_id) {
        appendMessageToThread(data);
    }

    // Mettre à jour le compteur de réponses
    updateStats();
}

// Charger les threads
let currentPage = 1;
let currentThreadId = null;

function loadThreads(page = 1) {
    fetch(`/api/forum/week/{{ $week->id }}/threads?page=${page}`)
        .then(response => response.json())
        .then(data => {
            displayThreads(data.data);
            displayPagination(data);
        });
}

// Gestionnaire d'événements pour le modal
document.getElementById('new-thread-btn').addEventListener('click', () => {
    document.getElementById('new-thread-modal').classList.remove('hidden');
});

document.getElementById('cancel-thread').addEventListener('click', () => {
    document.getElementById('new-thread-modal').classList.add('hidden');
    document.getElementById('new-thread-form').reset();
});

// Soumission du formulaire de nouveau thread
document.getElementById('new-thread-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/api/forum/thread', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('new-thread-modal').classList.add('hidden');
            this.reset();
            loadThreads();
        }
    });
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    loadThreads();
});
</script>
@endpush
