<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceReminder extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice, public int $number) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $i = $this->invoice;
        return (new MailMessage)
            ->subject(($this->number > 1 ? 'Tweede herinnering' : 'Herinnering') . ': factuur ' . $i->number)
            ->line('Factuur ' . $i->number . ' van ' . $i->bestrijder->name . ' (' . Money::format($i->total_cents) . ') had betaald moeten zijn op ' . $i->due_on->format('d-m-Y') . '.')
            ->line('Wilt u de betaling controleren? Is de factuur al voldaan, dan kunt u dit bericht negeren.');
    }
}
