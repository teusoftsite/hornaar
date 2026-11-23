<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadOffered extends Notification
{
    public function __construct(public Lead $lead) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $nest = $this->lead->nest;
        return (new MailMessage)
            ->subject('Nieuwe aanvraag: hoornaarnest bij u in de buurt')
            ->line('Een inwoner zoekt een bestrijder voor een nest van de Aziatische hoornaar bij u in de buurt.')
            ->line('Accepteert u binnen ' . config('meldhoornaar.lead_response_hours') . ' uur, dan krijgt u de contactgegevens. Accepteren kost € ' . number_format(config('meldhoornaar.lead_fee_cents') / 100, 2, ',', '.') . ' excl. btw; weigeren is gratis.')
            ->action('Bekijken en accepteren', route('bestrijder.leads'));
    }
}
