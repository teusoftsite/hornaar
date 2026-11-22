<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class InvoiceSent extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice) {}

    public function via($n): array { return ['mail']; }

    public function toMail($n): MailMessage
    {
        $i = $this->invoice;
        $mail = (new MailMessage)
            ->subject(($i->credits_invoice_id ? 'Creditfactuur ' : 'Factuur ') . $i->number . ' – ' . $i->bestrijder->name)
            ->line('Bijgaand ' . ($i->credits_invoice_id ? 'de creditfactuur' : 'de factuur') . ' voor de bestrijding van hoornaarnesten, periode ' . $i->period . '.')
            ->line('Totaal: ' . Money::format($i->total_cents) . ($i->due_on ? ', te betalen vóór ' . $i->due_on->format('d-m-Y') : '') . '.');
        if ($i->bestrijder->invoice_email) {
            $mail->replyTo($i->bestrijder->invoice_email);
        }
        if ($i->pdf_path && Storage::disk('local')->exists($i->pdf_path)) {
            $mail->attachData(Storage::disk('local')->get($i->pdf_path), 'factuur-' . $i->number . '.pdf', ['mime' => 'application/pdf']);
        }
        return $mail;
    }
}
