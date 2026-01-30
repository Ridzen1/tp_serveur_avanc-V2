<?php
namespace MailService\Mailer;

interface MailerInterface
{
    /**
     * Send a plain text email
     * @param string $to
     * @param string $subject
     * @param string $body
     */
    public function send(string $to, string $subject, string $body): void;
}
