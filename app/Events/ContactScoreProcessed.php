<?php

namespace App\Events;

use App\Models\Contact;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactScoreProcessed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Contact $contact
    ) {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('contacts.' . $this->contact->id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'contact' => [
                'id' => $this->contact->id,
                'name' => $this->contact->name,
                'email' => $this->contact->email,
                'phone' => $this->contact->phone,
                'score' => $this->contact->score,
                'processed_at' => $this->contact->processed_at,
                'updated_at' => $this->contact->updated_at
            ]
        ];
    }
}
