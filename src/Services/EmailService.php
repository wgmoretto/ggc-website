<?php
declare(strict_types=1);

namespace WebEngine\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $body, bool $isHtml = true): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = $this->config['encryption'];
            $mail->Port = $this->config['port'];

            // Recipients
            $mail->setFrom($this->config['from_address'], $this->config['from_name']);
            $mail->addAddress($to);

            // Content
            $mail->isHTML($isHtml);
            $mail->Subject = $subject;
            $mail->Body = $body;

            if ($isHtml) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function sendWelcomeEmail(string $to, string $username): bool
    {
        $subject = "Welcome to " . $_ENV['SERVER_NAME'];
        $body = "
            <h2>Welcome to {$_ENV['SERVER_NAME']}!</h2>
            <p>Hello {$username},</p>
            <p>Your account has been successfully created.</p>
            <p>You can now login and start playing!</p>
            <p>Server: {$_ENV['SERVER_NAME']}</p>
            <p>Thank you for joining us!</p>
        ";

        return $this->send($to, $subject, $body);
    }

    public function sendPasswordResetEmail(string $to, string $username, string $token): bool
    {
        $resetUrl = $_ENV['APP_URL'] . "/resetpassword?token={$token}";

        $subject = "Password Reset Request";
        $body = "
            <h2>Password Reset</h2>
            <p>Hello {$username},</p>
            <p>You have requested to reset your password.</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='{$resetUrl}'>{$resetUrl}</a></p>
            <p>This link will expire in 24 hours.</p>
            <p>If you did not request this, please ignore this email.</p>
        ";

        return $this->send($to, $subject, $body);
    }

    public function sendVerificationEmail(string $to, string $username, string $token): bool
    {
        $verifyUrl = $_ENV['APP_URL'] . "/verifyemail/{$token}";

        $subject = "Email Verification";
        $body = "
            <h2>Email Verification</h2>
            <p>Hello {$username},</p>
            <p>Please verify your email address by clicking the link below:</p>
            <p><a href='{$verifyUrl}'>{$verifyUrl}</a></p>
            <p>Thank you!</p>
        ";

        return $this->send($to, $subject, $body);
    }

    public function sendContactFormEmail(string $name, string $email, string $message): bool
    {
        $subject = "Contact Form Submission from {$name}";
        $body = "
            <h2>Contact Form</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Message:</strong></p>
            <p>{$message}</p>
        ";

        $adminEmail = $_ENV['MAIL_FROM_ADDRESS'];
        return $this->send($adminEmail, $subject, $body);
    }
}
