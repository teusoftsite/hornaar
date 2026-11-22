<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInvite extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        return (new MailMessage)
            ->subject('Uw account op MeldHoornaar.nl')
            ->line('Er is een account voor u aangemaakt bij ' . ($n->organisation?->name ?? 'MeldHoornaar.nl') . '.')
            ->action('Kies uw wachtwoord', url(route('password.reset', ['token' => $this->token, 'email' => $n->email], false)))
            ->line('Deze link is 60 minuten geldig. Daarna kunt u via "Wachtwoord vergeten" een nieuwe aanvragen.');
    }
}
