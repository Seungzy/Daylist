<?php

namespace App\Listeners;

use App\Notifications\MembershipApplicationSubmitted;
use Illuminate\Notifications\Events\NotificationSent;

class RecordApplicationEmailSent
{
    public function handle(NotificationSent $event): void
    {
        if ($event->channel === 'mail' && $event->notification instanceof MembershipApplicationSubmitted) {
            $event->notification->application->update(['verification_sent_at' => now()]);
        }
    }
}
