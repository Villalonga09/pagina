<?php
class Mailer {
  public static function send($to, $subject, $html, $attachments = []) {
    // Simple mail() fallback. For SMTP, integrate PHPMailer if available in vendor/.
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $from = $_ENV['SMTP_FROM'] ?? 'no-reply@localhost';
    $fromName = $_ENV['SMTP_FROM_NAME'] ?? 'Rifas';
    $headers .= "From: {$fromName} <{$from}>\r\n";
    // Attachments are ignored in mail() fallback; advise to use SMTP vendor if needed.
    return mail($to, '=?UTF-8?B?'.base64_encode($subject).'?=', $html, $headers);
  }
}