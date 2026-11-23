<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadNoBestrijder extends Notification
{
    public function __construct(public Lead $lead, public bool $internal = false) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        if ($this->internal) {
            return (new MailMessage)
                ->subject('Geen bestrijder voor lead #' . $this->lead->id)
                ->line('Voor nest #' . $this->lead->nest_id . ' (' . ($this->lead->nest->address ?? 'onbekend adres') . ') is geen bestrijder gevonden of heeft niemand geaccepteerd.')
                ->line('Inwoner: ' . $this->lead->name . ', ' . $this->lead->phone)
                ->action('Naar beheer', route('beheer.index'));
        }
        return (new MailMessage)
            ->subject('We hebben nog geen bestrijder voor je gevonden')
            ->line('Op dit moment is er bij jou in de buurt geen bestrijder beschikbaar via MeldHoornaar. We zoeken verder en nemen contact met je op.')
            ->line('Blijf uit de buurt van het nest.');
    }
}
