<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    //
    public function getSubscribers()
    {
        // Ajoutez une authentification ou une vérification d'API key ici

        $subscribers = NewsletterSubscriber::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($subscribers);
    }


    public function subscribe(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email',
        ]);

        $subscriber = new NewsletterSubscriber([
            'email' => $request->input('email'),

        ]);

        $subscriber->save();

        return response()->json(['message' => 'Abonnement réussi'], 201);
    }


    public function unsubscribe(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:newsletter_subscribers,email',
        ]);

        $subscriber = NewsletterSubscriber::where('email', $validatedData['email'])->first();
        $subscriber->is_active = false;
        $subscriber->save();

        return response()->json(['message' => 'Désabonnement réussi']);
    }
}
