<?php

namespace App\Notifications;

use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadAccepted extends Notification
{
    public function __construct(public Lead $lead) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $b = $this->lead->bestrijder;
        $mail = (new MailMessage)
            ->subject('Een bestrijder komt je nest weghalen')
            ->line($b->name . ' heeft je aanvraag aangenomen en neemt contact met je op om een afspraak te maken.');
        if ($b->phone) {
            $mail->line('Telefoon: ' . $b->phone);
        }
        return $mail->line('De bestrijder spreekt de prijs met je af en rekent met je af. MeldHoornaar zelf is gratis.')
            ->action('Je melding bekijken', route('melding.status', $this->lead->report->token));
    }
}
