<?php
declare(strict_types=1);

namespace Mizam\Traits;

use Exception;
use InvalidArgumentException;
use Mizam\Log;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

trait EmailSendTrait
{
    /**
     * @param string $subject
     * @param string $body
     * @param array $from ['john@doe.com' => 'John Doe']
     * @param array $to ['john@doe.com' => 'John Doe']
     * @param array $attacheFilePathList ['/path/to/file']
     * @param array $cc ['john@doe.com' => 'John Doe']
     * @param array $bcc ['john@doe.com' => 'John Doe']
     * @return int int The number of successful recipients. Can be 0 which indicates failure
     * @throws Exception
     */
    static public function sendEmail(
        string $subject,
        string $body,
        array $from,
        array $to,
        array $attacheFilePathList = [],
        array $cc = [],
        array $bcc = []
    ): int
    {
        $mail_method = getenv("MAIL_METHOD");

        if ($mail_method === 'smtp') {
            Log::debug("use smtp");
            $transport = new Swift_SmtpTransport(
                getenv("SMTP_HOST"),
                getenv("SMTP_PORT")
            );
            $transport->setUsername(getenv("SMTP_USER_NAME"));
            $transport->setPassword(getenv("SMTP_USER_PASS"));

        } elseif ($mail_method === 'sendmail') {
            Log::debug("use sendmail");
            $sendmail_cli = getenv("SENDMAIL_CLI");
            $transport = new Swift_SendmailTransport($sendmail_cli);

        } else {
            throw new InvalidArgumentException("invalid MAIL_METHOD");
        }

        $mailer = new Swift_Mailer($transport);

        $message = new Swift_Message();
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setFrom($from);
        $message->setTo($to);

        if (count($cc) > 0) {
            $message->setCc($cc);
        }

        if (count($bcc) > 0) {
            $message->setBcc($bcc);
        }

        if (count($attacheFilePathList) > 0) {
            foreach ($attacheFilePathList as $attacheFilePath) {
                $message->attach(Swift_Attachment::fromPath($attacheFilePath));
            }
        }

        $result = $mailer->send($message);

        return $result;
    }
}
