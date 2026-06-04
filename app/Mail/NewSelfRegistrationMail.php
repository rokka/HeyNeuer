<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewSelfRegistrationMail extends Mailable
{
    use SerializesModels;

    public function __construct(public User $newUser) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Neue Selbst-Registrierung: ' . $this->newUser->email,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-self-registration',
            with: [
                'newUser'    => $this->newUser,
                'usersIndex' => url(route('users.index', absolute: false)),
            ],
        );
    }
}
