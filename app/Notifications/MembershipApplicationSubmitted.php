<?php

namespace App\Notifications;

use App\Models\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class MembershipApplicationSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public MembershipApplication $application)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Membership application received')
            ->greeting('Hello '.$this->application->full_name.',')
            ->line('We received your membership application.')
            ->line('Reference number: '.$this->application->reference_no)
            ->line('Status: Pending review. Email confirmation does not approve or pay for a membership.')
            ->action('Confirm email address', URL::temporarySignedRoute('membership.verify', now()->addDays(7), ['application' => $this->application->id, 'hash' => sha1($this->application->email)]))
            ->line('This confirmation link expires in seven days. Keep your reference number for your records.')
            ->salutation('Daylist Membership');
    }
}
