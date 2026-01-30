<?php
namespace MailService\Mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mime\Email;

class SymfonyMailerAdapter implements MailerInterface
{
    private SymfonyMailer $mailer;
    private string $from;

    /**
     * @param string $from default from address
     * @param string|null $dsn explicit DSN (e.g. smtp://user:pass@host:port). If null, will use MAILER_HOST/MAILER_PORT.
     */
    public function __construct(string $from = 'no-reply@toubilib.local', ?string $dsn = null)
    {
        // Allow explicit DSN or build from env
        if (!$dsn) {
            $envDsn = getenv('MAILER_DSN');
            if ($envDsn) {
                $dsn = $envDsn;
            } else {
                $host = getenv('MAILER_HOST') ?: 'mailcatcher';
                $port = getenv('MAILER_PORT') ?: '1025';
                $dsn = sprintf('smtp://%s:%s', $host, $port);
            }
        }

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
