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
use Illuminate\Support\Facades\DB;

class ProcessContactScore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $contactId
    ) {
        $this->onQueue('contacts');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting job processing', ['contact_id' => $this->contactId]);
            
            // Buscar o contato pelo ID
            $contact = Contact::find($this->contactId);
            
            if (!$contact) {
                Log::error('Contact not found for processing', ['contact_id' => $this->contactId]);
                return;
            }
            
            Log::info('Contact found', ['contact_id' => $this->contactId, 'name' => $contact->name]);
            
            // Simular processamento pesado
            sleep(2);
            
            // Gerar score aleatório entre 0 e 100
            $score = rand(0, 100);
            
            Log::info('Generated score', ['contact_id' => $this->contactId, 'score' => $score]);
            
            // Atualizar contato
            $updated = $contact->update([
                'score' => $score,
                'processed_at' => now()
            ]);
            
            Log::info('Contact updated', ['contact_id' => $this->contactId, 'updated' => $updated]);
            
            // Disparar evento apenas se não estiver em teste e se broadcasting estiver configurado
            if (!app()->environment('testing') && config('broadcasting.default') !== 'null') {
                try {
                    event(new ContactScoreProcessed($contact));
                    Log::info('Event broadcasted', ['contact_id' => $this->contactId]);
                } catch (\Exception $e) {
                    // Log error but don't fail the job
                    Log::error('Failed to broadcast ContactScoreProcessed event: ' . $e->getMessage());
                }
            }
            
            Log::info('Job completed successfully', ['contact_id' => $this->contactId]);
            
        } catch (\Exception $e) {
            Log::error('Job failed', [
                'contact_id' => $this->contactId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
