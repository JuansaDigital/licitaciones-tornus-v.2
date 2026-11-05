<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NuevasLicitacionesMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $licitaciones;
    public string $market;
    public int $totalCount;
    public string $recipientName;

    /**
     * @param array $licitaciones Lista de licitaciones detectadas
     * @param string $market Código de mercado ('CO', 'PE' o 'TODOS')
     * @param int $totalCount Conteo total
     * @param string $recipientName Nombre del destinatario
     */
    public function __construct(array $licitaciones, string $market = 'CO', int $totalCount = 0, string $recipientName = 'Miembro del Equipo')
    {
        $this->licitaciones = $licitaciones;
        $this->market = strtoupper($market);
        $this->totalCount = $totalCount ?: count($licitaciones);
        $this->recipientName = $recipientName;
    }

    public function envelope(): Envelope
    {
        $marketLabel = $this->market === 'PE' ? 'Perú (OECE)' : ($this->market === 'CO' ? 'Colombia (SECOP II)' : 'Colombia y Perú');
        $subject = "Alerta Radar Tornus: {$this->totalCount} nuevas propuestas detectadas · {$marketLabel}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.nuevas-licitaciones',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}

