<?php
namespace MailService\Mailer;

class NullMailer implements MailerInterface
{
    public function send(string $to, string $subject, string $body): void
    {
        // No-op implementation for testing / offline mode
        // Optionally log to stdout for visibility
        echo "[NullMailer] Pretend sending to {$to} (subject: {$subject})\n";
    }
}
