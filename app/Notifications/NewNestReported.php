<?php

namespace App\Notifications;

use App\Models\Nest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewNestReported extends Notification
{
    use Queueable;

    public function __construct(public Nest $nest) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        return (new MailMessage)
            ->subject('Nieuwe melding #' . $this->nest->id . ($this->nest->address ? ' – ' . $this->nest->address : ''))
            ->line('Er is een nieuw mogelijk hoornaarnest gemeld in uw gemeente.')
            ->action('Bekijk en beoordeel', route('gemeente.nesten.show', $this->nest));
    }
}
