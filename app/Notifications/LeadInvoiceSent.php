<?php

namespace App\Notifications;

use App\Models\LeadInvoice;
use App\Support\Money;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class LeadInvoiceSent extends Notification
{
    public function __construct(public LeadInvoice $invoice) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $i = $this->invoice;
        $mail = (new MailMessage)
            ->subject('Factuur ' . $i->number . ' – MeldHoornaar.nl')
            ->line('Bijgaand de factuur voor ' . $i->lead_count . ' geaccepteerde aanvraag/aanvragen in ' . $i->period . '.')
            ->line('Totaal: ' . Money::format($i->total_cents) . ', te betalen vóór ' . $i->due_on->format('d-m-Y') . '.');
        if ($i->pdf_path && Storage::disk('local')->exists($i->pdf_path)) {
            $mail->attachData(Storage::disk('local')->get($i->pdf_path), 'factuur-' . $i->number . '.pdf', ['mime' => 'application/pdf']);
        }
        return $mail;
    }
}
