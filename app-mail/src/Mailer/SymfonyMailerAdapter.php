<?php
namespace MailService\Mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

class SymfonyMailerAdapter implements MailerInterface
{
    private SymfonyMailer $mailer;
    private string $from;

    public function __construct(string $from = 'no-reply@toubilib.local')
    {
        $host = getenv('MAILER_HOST') ?: 'mailcatcher';
        $port = getenv('MAILER_PORT') ?: '1025';
        $dsn = sprintf('smtp://%s:%s', $host, $port);

        $transport = Transport::fromDsn($dsn);
        $this->mailer = new SymfonyMailer($transport);
        $this->from = $from;
    }

    public function send(string $to, string $subject, string $body): void
    {
        $email = (new Email())
            ->from($this->from)
            ->to($to)
            ->subject($subject)
            ->text($body);

        $this->mailer->send($email);
    }
}
