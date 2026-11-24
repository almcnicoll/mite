<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PickReminder extends Mailable
{
    public function __construct(public User $user, public int $count)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: self::subjectLine($this->count, Str::plural('pick', $this->count)));
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pick-reminder',
            with: self::viewData(
                $this->user->name,
                $this->count,
                Str::plural('pick', $this->count),
                // Signed, so no login is needed (there isn't one) and the link can't be guessed.
                URL::signedRoute('reminders.unsubscribe', ['user' => $this->user->id]),
            ),
        );
    }

    public static function subjectLine(int|string $count, string $pickWord): string
    {
        return "You have {$count} outstanding {$pickWord} to choose";
    }

    /** The single place the template's variables are assembled, for real sends and the preview alike. */
    private static function viewData(string $name, int|string $count, string $pickWord, string $unsubscribeUrl): array
    {
        return [
            'name'           => $name,
            'count'          => $count,
            'pickWord'       => $pickWord,
            'siteUrl'        => route('picks.today'),
            'unsubscribeUrl' => $unsubscribeUrl,
        ];
    }

    /**
     * Plain-text rendering of the real email with {placeholders} for the per-person fields, for
     * showing on the Setup page. Built from the same template and subject as the real email, so it can't drift.
     */
    public static function preview(): string
    {
        $html = View::make('emails.pick-reminder', self::viewData('{name}', '{number}', 'pick(s)', '{your personal opt-out link}'))
            ->render();

        // Source line breaks aren't meaningful in HTML; then keep link targets visible and flatten to text.
        $html = preg_replace('/\s+/', ' ', $html);
        $html = preg_replace('#<a\s[^>]*href="([^"]*)"[^>]*>(.*?)</a>#s', '$2 ($1)', $html);
        $html = preg_replace('#<hr[^>]*>#i', "\n---\n", $html);
        $html = preg_replace('#</p>#i', "\n\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace(['/[ \t]+/', '/ *\n */', '/\n{3,}/'], [' ', "\n", "\n\n"], $text));

        return 'Subject: ' . self::subjectLine('{number}', 'pick(s)') . "\n\n" . $text;
    }
}
