<?php
/**
 * Minimal SMTP mailer with PHP mail() fallback.
 * Uses SMTP_HOST / SMTP_PORT / SMTP_SECURE / SMTP_USER / SMTP_PASS from config.php.
 */

/**
 * Send an email. Returns true on success.
 */
function send_mail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    if ($textBody === '') {
        $textBody = trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody)));
    }

    if (SMTP_HOST !== '' && SMTP_USER !== '' && SMTP_PASS !== '') {
        try {
            if (smtp_send($to, $subject, $htmlBody, $textBody)) {
                return true;
            }
        } catch (Throwable $e) {
            error_log('SMTP send failed: ' . $e->getMessage());
        }
    }

    // Fallback: PHP mail()
    $boundary = 'b' . bin2hex(random_bytes(12));
    $headers  = "From: " . SITE_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . MAIL_FROM . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
    $body  = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$textBody\r\n";
    $body .= "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$htmlBody\r\n";
    $body .= "--$boundary--";
    return @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}

/** Raw SMTP conversation (STARTTLS or SMTPS). */
function smtp_send(string $to, string $subject, string $htmlBody, string $textBody): bool
{
    $timeout = 15;
    $host    = (SMTP_SECURE === 'ssl') ? 'ssl://' . SMTP_HOST : SMTP_HOST;
    $fp      = @stream_socket_client("$host:" . SMTP_PORT, $errno, $errstr, $timeout);
    if (!$fp) {
        throw new RuntimeException("connect: $errstr ($errno)");
    }
    stream_set_timeout($fp, $timeout);

    $read = function () use ($fp): string {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function (string $c, array $expect) use ($fp, $read): string {
        fwrite($fp, $c . "\r\n");
        $r = $read();
        if (!in_array((int)substr($r, 0, 3), $expect, true)) {
            throw new RuntimeException("SMTP '$c' -> $r");
        }
        return $r;
    };

    $r = $read();                                   // banner
    if ((int)substr($r, 0, 3) !== 220) throw new RuntimeException("banner: $r");

    $hostname = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $cmd("EHLO $hostname", [250]);

    if (SMTP_SECURE === 'tls') {
        $cmd('STARTTLS', [220]);
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new RuntimeException('STARTTLS negotiation failed');
        }
        $cmd("EHLO $hostname", [250]);
    }

    $cmd('AUTH LOGIN', [334]);
    $cmd(base64_encode(SMTP_USER), [334]);
    $cmd(base64_encode(SMTP_PASS), [235]);

    $cmd('MAIL FROM:<' . MAIL_FROM . '>', [250]);
    $cmd('RCPT TO:<' . $to . '>', [250, 251]);
    $cmd('DATA', [354]);

    $boundary = 'b' . bin2hex(random_bytes(12));
    $headers = [
        'Date: ' . date('r'),
        'From: ' . SITE_NAME . ' <' . MAIL_FROM . '>',
        'Reply-To: ' . MAIL_FROM,
        'To: <' . $to . '>',
        'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
        'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . parse_url(SITE_URL, PHP_URL_HOST) . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    $data  = implode("\r\n", $headers) . "\r\n\r\n";
    $data .= "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n$textBody\r\n";
    $data .= "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\n\r\n$htmlBody\r\n";
    $data .= "--$boundary--";
    // dot-stuffing
    $data = preg_replace('/^\./m', '..', $data);

    fwrite($fp, $data . "\r\n.\r\n");
    $r = $read();
    if ((int)substr($r, 0, 3) !== 250) throw new RuntimeException("DATA: $r");

    fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return true;
}

/** Small branded HTML wrapper for notification emails. */
function mail_template(string $title, string $bodyHtml, string $buttonText = '', string $buttonUrl = ''): string
{
    $button = '';
    if ($buttonText !== '' && $buttonUrl !== '') {
        $button = '<p style="margin:28px 0"><a href="' . htmlspecialchars($buttonUrl) . '" '
            . 'style="background:#2e9fff;color:#fff;text-decoration:none;padding:12px 26px;'
            . 'border-radius:5px;display:inline-block;font-weight:bold">'
            . htmlspecialchars($buttonText) . '</a></p>';
    }
    return '<div style="font-family:Arial,Helvetica,sans-serif;max-width:560px;margin:0 auto;color:#333">'
        . '<h2 style="color:#2e9fff">' . htmlspecialchars(SITE_NAME) . '</h2>'
        . '<h3>' . htmlspecialchars($title) . '</h3>'
        . $bodyHtml . $button
        . '<hr style="border:none;border-top:1px solid #eee;margin:28px 0">'
        . '<p style="font-size:12px;color:#999">© 2021 - ' . date('Y') . ' ' . htmlspecialchars(SITE_NAME)
        . ' — <a href="' . SITE_URL . '">' . SITE_URL . '</a></p></div>';
}
