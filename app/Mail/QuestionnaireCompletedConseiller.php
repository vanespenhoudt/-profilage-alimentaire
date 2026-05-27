<?php

namespace App\Mail;

use App\Models\Client;
use App\Models\Questionnaire;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class QuestionnaireCompletedConseiller extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User          $conseiller,
        public readonly Client        $client,
        public readonly Questionnaire $questionnaire,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 ' . $this->client->nom_complet . ' a complété son questionnaire',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.conseiller.questionnaire-completed',
        );
    }
}
