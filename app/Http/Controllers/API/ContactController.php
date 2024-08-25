<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ContactMail;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    //
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom_prenom' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        $contact = new Contact([
            'nom_prenom' => $request->input('nom_prenom'),
            'tel' => $request->input('tel'),
            'email' => $request->input('email'),
            'message' => $request->input('message'),
        ]);

        $contact->save();

        // Envoi de l'email
        Mail::to('sihiotsinfo@gmail.com')
            ->cc('info@sihiots.net')
            ->send(new ContactMail($contact));

        return response()->json(['message' => 'success'], 200);
    }
}
