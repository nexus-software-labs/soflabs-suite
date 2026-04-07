<?php

namespace App\Mail;

use App\Models\Printing\PrintOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrintOrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PrintOrder $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pedido de Impresión Creado - '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.print-order-created',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
