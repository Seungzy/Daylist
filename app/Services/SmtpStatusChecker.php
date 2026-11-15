<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Throwable;

class SmtpStatusChecker
{
    public function check(): array
    {
        $mailer = config('mail.default');
        if (config("mail.mailers.$mailer.transport") !== 'smtp') {
            return ['status' => 'not_applicable', 'message' => 'The selected mailer does not use SMTP. No connection was attempted.'];
        }

        $transport = null;
        try {
            $transport = Mail::mailer($mailer)->getSymfonyTransport();
            if (! $transport instanceof SmtpTransport) {
                return ['status' => 'not_applicable', 'message' => 'The selected transport cannot be checked as a direct SMTP connection.'];
            }
            $transport->getStream()->setTimeout(8);
            $transport->start();

            return ['status' => 'working', 'message' => 'SMTP connection and configured authentication succeeded. No email was sent.'];
        } catch (Throwable $exception) {
            // Transport exceptions can contain usernames, passwords, and server replies.
            // Return a fixed description rather than displaying the exception text.
            $message = strtolower($exception->getMessage());
            $description = match (true) {
                str_contains($message, 'authenticate'), str_contains($message, '535'), str_contains($message, 'credentials') => 'SMTP authentication failed. Check the account and App Password.',
                str_contains($message, 'certificate'), str_contains($message, 'crypto') => 'The secure SMTP connection failed. Check the server certificate and encryption settings.',
                default => 'SMTP could not connect. Check the host, port, network connection, and provider availability.',
            };

            return ['status' => 'failed', 'message' => $description];
        } finally {
            if ($transport instanceof SmtpTransport) {
                try {
                    $transport->stop();
                } catch (Throwable) { /* Preserve the original check result. */
                }
            }
        }
    }
}
