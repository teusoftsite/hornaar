<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReceived extends Notification
{
    use Queueable;

    public function __construct(public Report $report) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        return (new MailMessage)
            ->subject('Bedankt voor je melding #' . $this->report->nest_id)
            ->greeting('Bedankt!')
            ->line('We hebben je melding van een mogelijk hoornaarnest ontvangen en doorgestuurd naar de gemeente.')
            ->action('Volg je melding', route('melding.status', $this->report->token))
            ->line('Benader het nest niet zelf. Je e-mailadres wordt 90 dagen na afhandeling verwijderd.');
    }
}
