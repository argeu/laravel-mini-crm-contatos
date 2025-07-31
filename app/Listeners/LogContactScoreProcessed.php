<?php

namespace App\Listeners;

use App\Events\ContactScoreProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogContactScoreProcessed
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContactScoreProcessed $event): void
    {
        // Gravar em storage/logs/contact.log
        Log::channel('contact')->info('Contact score processed', [
            'id' => $event->contact->id,
            'name' => $event->contact->name,
            'email' => $event->contact->email,
            'score' => $event->contact->score,
            'timestamp' => now()->toISOString()
        ]);
    }
}
