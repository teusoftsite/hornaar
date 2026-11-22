<?php

namespace App\Notifications;

use App\Models\Nest;
use App\Models\Report;
use App\Support\NestStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(public Nest $nest, public Report $report, public string $status) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $text = match ($this->status) {
            NestStatus::GOEDGEKEURD => 'De gemeente heeft je melding beoordeeld: het gaat om een hoornaarnest. Er wordt een bestrijder ingeschakeld.',
            NestStatus::AFGEWEZEN => 'De gemeente heeft je melding beoordeeld. Het gaat waarschijnlijk niet om een Aziatische hoornaar. Bedankt voor het melden!',
            NestStatus::INGEPLAND => 'De bestrijding van het nest is ingepland.',
            NestStatus::BESTREDEN => 'Het nest is bestreden. Bedankt voor je hulp!',
            default => 'De status van je melding is gewijzigd: ' . NestStatus::label($this->status) . '.',
        };

        return (new MailMessage)
            ->subject('Update melding #' . $this->nest->id . ': ' . NestStatus::label($this->status))
            ->line($text)
            ->action('Bekijk je melding', route('melding.status', $this->report->token));
    }
}
