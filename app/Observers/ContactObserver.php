<?php

namespace App\Observers;

use App\Models\Contact;
use Illuminate\Support\Facades\Log;

class ContactObserver
{
    /**
     * Handle the Contact "saving" event.
     */
    public function saving(Contact $contact): void
    {
        // Normalizar telefone (somente dígitos)
        $contact->phone = preg_replace('/[^0-9]/', '', $contact->phone);
    }

    /**
     * Handle the Contact "created" event.
     */
    public function created(Contact $contact): void
    {
        // Logar criação do contato
        Log::info('Contact created', [
            'id' => $contact->id,
            'name' => $contact->name,
            'email' => $contact->email,
            'phone' => $contact->phone
        ]);
    }

    /**
     * Handle the Contact "updated" event.
     */
    public function updated(Contact $contact): void
    {
        //
    }

    /**
     * Handle the Contact "deleted" event.
     */
    public function deleted(Contact $contact): void
    {
        //
    }

    /**
     * Handle the Contact "restored" event.
     */
    public function restored(Contact $contact): void
    {
        //
    }

    /**
     * Handle the Contact "force deleted" event.
     */
    public function forceDeleted(Contact $contact): void
    {
        //
    }
}
