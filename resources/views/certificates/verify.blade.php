{{-- resources/views/certificates/verify.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- En-tête -->
            <div class="bg-bcc-primary p-6">
                <h1 class="text-2xl font-bold text-white">Vérification de certificat</h1>
                <p class="text-blue-100">Vérifiez l'authenticité d'un certificat BCC-Center</p>
            </div>

            <!-- Résultat -->
            <div class="p-8">
                @if($result['valid'])
                <div class="mb-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-center text-gray-800 mb-2">
                        Certificat Valide ✓
                    </h2>
                    <p class="text-center text-gray-600">
                        Ce certificat a été émis par BCC-Center et est authentique.
                    </p>
                </div>

                <!-- Détails du certificat -->
                <div class="border rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Détails du certificat</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Numéro de certificat</label>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ $result['certificate']->certificate_number }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Participant</label>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ $result['certificate']->user->name }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Date d'émission</label>
                                <p class="mt-1 text-lg text-gray-800">
                                    {{ $result['certificate']->issue_date->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Formation</label>
                                <p class="mt-1 text-lg font-semibold text-gray-800">
                                    {{ $result['certificate']->formation->title }}
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Durée</label>
                                <p class="mt-1 text-lg text-gray-800">
                                    {{ $result['certificate']->formation->duration }} heures
                                </p>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-500">Date d'expiration</label>
                                <p class="mt-1 text-lg text-gray-800">
                                    @if($result['certificate']->expiry_date)
                                        {{ $result['certificate']->expiry_date->format('d/m/Y') }}
                                    @else
                                        Illimitée
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-center space-x-4">
                    @if(auth()->check() && auth()->id() === $result['certificate']->user_id)
                    <a href="{{ route('certificate.download', $result['certificate']->certificate_number) }}"
                       class="px-6 py-3 bg-bcc-primary text-white rounded-lg hover:bg-blue-800 font-semibold">
                        Télécharger le certificat
                    </a>
                    @endif

                    <button onclick="window.print()"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-semibold">
                        Imprimer cette page
                    </button>
                </div>

                @else
                <!-- Certificat invalide -->
                <div class="mb-8">
                    <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-4">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-center text-gray-800 mb-2">
                        Certificat Invalide
                    </h2>
                    <p class="text-center text-gray-600 mb-6">
                        {{ $result['message'] }}
                    </p>

                    <div class="text-center">
                        <a href="{{ route('home') }}"
                           class="px-6 py-3 bg-bcc-primary text-white rounded-lg hover:bg-blue-800 font-semibold">
                            Retour à l'accueil
                        </a>
                    </div>
                </div>
                @endif

                <!-- QR Code pour vérification mobile -->
                @if(isset($result['qr_code']))
                <div class="mt-8 pt-8 border-t text-center">
                    <p class="text-sm text-gray-500 mb-4">
                        Scannez ce code QR pour vérifier ce certificat
                    </p>
                    <img src="{{ $result['qr_code'] }}"
                         alt="QR Code de vérification"
                         class="w-32 h-32 mx-auto">
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
