<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Events\ContactScoreProcessed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessContactScore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Contact $contact
    ) {
        $this->onQueue('contacts');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Simular processamento pesado
        sleep(2);
        
        // Gerar score aleatório entre 0 e 100
        $score = rand(0, 100);
        
        // Atualizar contato
        $this->contact->update([
            'score' => $score,
            'processed_at' => now()
        ]);
        
        // Disparar evento apenas se não estiver em teste e se broadcasting estiver configurado
        if (!app()->environment('testing') && config('broadcasting.default') !== 'null') {
            try {
                event(new ContactScoreProcessed($this->contact));
            } catch (\Exception $e) {
                // Log error but don't fail the job
                Log::error('Failed to broadcast ContactScoreProcessed event: ' . $e->getMessage());
            }
        }
    }
}
