<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        abort_if(Auth::user() == null, 403);

        $attrs = $request->validate([
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required",
            "phone" => "required",
        ]);

        $contact = $request->user()->contacts()->create($attrs);
        return new ContactResource($contact);
    }

    public function show(Contact $contact)
    {
        if (auth()->user()->id != $contact->user_id) abort(404, "Records not found");
        return new ContactResource($contact);
    }

    public function update(Request $request, Contact $contact)
    {
        if ($contact->id !== auth()->user()->id) abort(403, "unathorized");

        $attrs = $request->validate([
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required",
            "phone" => "required",
        ]);

        $contact->update($attrs);

        return new ContactResource($contact);
    }
}
