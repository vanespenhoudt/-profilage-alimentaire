<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Questionnaire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class QuestionnaireCompletedClient extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Client        $client,
        public readonly Questionnaire $questionnaire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre questionnaire a bien été reçu — Profilage Alimentaire',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.client.questionnaire-completed',
        );
    }
}
