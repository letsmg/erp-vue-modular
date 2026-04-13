<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecuperarSenhaMail extends Mailable
{
    use Queueable, SerializesModels;

    // Construtor opcional para passar a URL
    public function __construct(public string $url) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperação de Senha - Erp Vue Modular',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.recuperar-senha',
            with: [
                'url' => $this->url, 
            ],
        );
    }

    // try {
    //     Mail::to($request->email)->send(new RecuperarSenhaMail());
    //     return back()->with('success', 'Enviado!');
    // } catch (\Exception $e) {
    //     // Isso vai travar a tela e mostrar o erro real do Resend/PHP
    //     dd($e->getMessage()); 
    // }
}