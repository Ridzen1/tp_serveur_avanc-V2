<?php
namespace MailService\Mailer;

class MailerFactory
{
    /**
     * Create a MailerInterface implementation based on environment variables
     * - MAILER_DRIVER: 'symfony' (default), 'null'
     * - MAILER_DSN: explicit DSN for symfony (e.g. smtp://user:pass@host:port)
     * - MAILER_HOST / MAILER_PORT: fallback host/port
     * - MAILER_FROM: default from address
     */
    public static function create(): MailerInterface
    {
        $driver = getenv('MAILER_DRIVER') ?: 'symfony';
        $from = getenv('MAILER_FROM') ?: 'no-reply@toubilib.local';

        switch (strtolower($driver)) {
            case 'null':
            case 'noop':
                return new NullMailer();

            case 'symfony':
            default:
                // Allow an explicit DSN, otherwise build from host/port
                $dsn = getenv('MAILER_DSN');
                if (!$dsn) {
                    $host = getenv('MAILER_HOST') ?: 'mailcatcher';
                    $port = getenv('MAILER_PORT') ?: '1025';
                    $user = getenv('MAILER_USER');
                    $pass = getenv('MAILER_PASS');

                    if ($user && $pass) {
                        $dsn = sprintf('smtp://%s:%s@%s:%s', rawurlencode($user), rawurlencode($pass), $host, $port);
                    } else {
                        $dsn = sprintf('smtp://%s:%s', $host, $port);
                    }
                }

                return new SymfonyMailerAdapter($from, $dsn);
        }
    }
}
