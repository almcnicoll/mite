<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;

class PickReminder extends Mailable
{
    public function __construct(public User $user, public int $count)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You have {$this->count} outstanding " . str('pick')->plural($this->count) . ' to choose',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pick-reminder',
            with: [
                'siteUrl'        => route('picks.today'),
                // Signed, so no login is needed (there isn't one) and the link can't be guessed.
                'unsubscribeUrl' => URL::signedRoute('reminders.unsubscribe', ['user' => $this->user->id]),
            ],
        );
    }
}
