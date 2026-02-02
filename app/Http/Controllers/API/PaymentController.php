<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'cours_id' => 'required|exists:courses,id',
            'methode_paiement' => 'required|string',
        ]);

        $user = $request->user();
        $course = Course::findOrFail($request->cours_id);

        // Vérifier si déjà inscrit
        $existing = Enrollment::where('user_id', $user->id)
            ->where('cours_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Vous êtes déjà inscrit à ce cours'
            ], 400);
        }

        // Créer le paiement
        $payment = Payment::create([
            'user_id' => $user->id,
            'cours_id' => $course->id,
            'montant' => $course->current_price,
            'devise' => 'XOF',
            'methode_paiement' => $request->methode_paiement,
            'statut' => 'pending',
            'reference' => 'PAY-' . Str::upper(Str::random(10)),
            'transaction_id' => Str::uuid(),
        ]);

        // Ici, intégrer avec le gateway de paiement (FedaPay, CinetPay, etc.)
        // Pour l'exemple, on simule une URL de paiement
        $paymentUrl = url('/payment/process/' . $payment->id);

        return response()->json([
            'message' => 'Paiement initié',
            'payment' => $payment,
            'payment_url' => $paymentUrl,
        ], 201);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
        ]);

        $payment = Payment::where('reference', $request->reference)->firstOrFail();

        // Ici, vérifier avec le gateway de paiement
        // Pour l'exemple, on simule une vérification
        
        if ($payment->statut === 'completed') {
            return response()->json([
                'message' => 'Paiement déjà vérifié',
                'payment' => $payment
            ]);
        }

        // Mettre à jour le statut
        $payment->update([
            'statut' => 'completed',
            'date_paiement' => now(),
        ]);

        // Créer l'inscription
        Enrollment::create([
            'user_id' => $payment->user_id,
            'cours_id' => $payment->cours_id,
            'status' => 'actif',
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Paiement vérifié avec succès',
            'payment' => $payment
        ]);
    }

    public function paymentHistory(Request $request)
    {
        $user = $request->user();

        $payments = Payment::where('user_id', $user->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = Payment::with(['user', 'course'])->findOrFail($id);

        // Vérifier l'autorisation
        if ($payment->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        return response()->json($payment);
    }

    public function refund(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->statut !== 'completed') {
            return response()->json([
                'message' => 'Seuls les paiements complétés peuvent être remboursés'
            ], 400);
        }

        // Ici, intégrer la logique de remboursement avec le gateway

        $payment->update([
            'statut' => 'refunded',
        ]);

        // Désactiver l'inscription
        Enrollment::where('user_id', $payment->user_id)
            ->where('cours_id', $payment->cours_id)
            ->update(['status' => 'abandonne']);

        return response()->json([
            'message' => 'Remboursement effectué avec succès',
            'payment' => $payment
        ]);
    }
}