<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AssignmentCreated extends Notification
{
    use Queueable;

    public function __construct(public Assignment $assignment) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $nest = $this->assignment->nest;
        return (new MailMessage)
            ->subject('Nieuwe opdracht: nest #' . $nest->id . ($nest->address ? ' – ' . $nest->address : ''))
            ->line('Gemeente ' . $this->assignment->contract->gemeente->name . ' heeft u een opdracht gegeven.')
            ->action('Bekijk opdracht', route('bestrijder.opdrachten'));
    }
}
