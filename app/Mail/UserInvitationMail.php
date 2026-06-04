<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class UserInvitationMail extends Mailable
{
    use SerializesModels;

    public string $acceptUrl;

    public function __construct(public User $invitee)
    {
        $expiresAt = now()->addHours((int) config('auth.invitation_expiry_hours', 72));

        $this->acceptUrl = URL::temporarySignedRoute(
            'invitation.accept',
            $expiresAt,
            ['token' => $invitee->invitation_token],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung zu Hey, Alter! Essen',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.user-invitation',
            with: [
                'invitee'   => $this->invitee,
                'acceptUrl' => $this->acceptUrl,
                'expiresIn' => (int) config('auth.invitation_expiry_hours', 72),
            ],
        );
    }
}
