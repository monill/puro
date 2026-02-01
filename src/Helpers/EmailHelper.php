<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Helper Class
 * Handles all email functionality using PHPMailer
 */
class EmailHelper
{
    private static $instance = null;
    private $mailer;
    private $config;

    private function __construct()
    {
        $this->config = $this->loadConfig();
        $this->setupMailer();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load email configuration
     */
    private function loadConfig()
    {
        return ConfigHelper::email() ?: [
            'driver' => 'smtp',
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from' => [
                'address' => 'noreply@uro.com',
                'name' => 'Puro'
            ],
            'reply_to' => [
                'address' => 'support@puro.com',
                'name' => 'Puro Support'
            ],
            'sendmail_path' => '/usr/sbin/sendmail -bs',
            'logging' => true,
            'debug' => false
        ];
    }

    /**
     * Setup PHPMailer instance
     */
    private function setupMailer()
    {
        $this->mailer = new PHPMailer(true);

        try {
            // Configure based on driver
            switch ($this->config['driver']) {
                case 'smtp':
                    $this->setupSMTP();
                    break;
                case 'mail':
                    $this->mailer->isMail();
                    break;
                case 'sendmail':
                    $this->mailer->isSendmail();
                    $this->mailer->Sendmail = $this->config['sendmail_path'] ?? '/usr/sbin/sendmail -bs';
                    break;
                case 'qmail':
                    $this->mailer->isQmail();
                    break;
            }

            // Set default from address
            $this->mailer->setFrom(
                $this->config['from']['address'],
                $this->config['from']['name']
            );

            // Set reply-to if configured
            if (!empty($this->config['reply_to']['address'])) {
                $this->mailer->addReplyTo(
                    $this->config['reply_to']['address'],
                    $this->config['reply_to']['name'] ?? ''
                );
            }

            // Set charset
            $this->mailer->CharSet = 'UTF-8';

            // Set debug mode if enabled
            if ($this->config['debug'] ?? false) {
                $this->mailer->SMTPDebug = SMTP::DEBUG_SERVER;
                $this->mailer->Debugoutput = function ($str, $level) {
                    LogHelper::debug("PHPMailer [{$level}]: {$str}");
                };
            }
        } catch (Exception $e) {
            LogHelper::error('Email setup failed: ' . $e->getMessage());
            throw new Exception('Email configuration error: ' . $e->getMessage());
        }
    }

    /**
     * Setup SMTP configuration
     */
    private function setupSMTP()
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->Port = $this->config['port'];
        $this->mailer->SMTPAuth = !empty($this->config['username']);

        if (!empty($this->config['username'])) {
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
        }

        // Set encryption if specified
        if (!empty($this->config['encryption'])) {
            switch (strtolower($this->config['encryption'])) {
                case 'tls':
                    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    break;
                case 'ssl':
                    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                default:
                    $this->mailer->SMTPSecure = '';
            }
        }
    }

    /**
     * Send email
     */
    public function send($to, $subject, $body, $options = [])
    {
        try {
            // Reset recipients
            $this->mailer->clearAddresses();
            $this->mailer->clearCCs();
            $this->mailer->clearBCCs();
            $this->mailer->clearReplyTos();
            $this->mailer->clearAttachments();
            $this->mailer->clearCustomHeaders();

            // Set subject
            $this->mailer->Subject = $subject;

            // Set body
            if ($options['html'] ?? true) {
                $this->mailer->isHTML(true);
                $this->mailer->Body = $body;
                $this->mailer->AltBody = $options['text'] ?? strip_tags($body);
            } else {
                $this->mailer->isHTML(false);
                $this->mailer->Body = $body;
            }

            // Add recipients
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addAddress($name);
                    } else {
                        $this->mailer->addAddress($email, $name);
                    }
                }
            } else {
                $this->mailer->addAddress($to);
            }

            // Add CC if specified
            if (!empty($options['cc'])) {
                foreach ($options['cc'] as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addCC($name);
                    } else {
                        $this->mailer->addCC($email, $name);
                    }
                }
            }

            // Add BCC if specified
            if (!empty($options['bcc'])) {
                foreach ($options['bcc'] as $email => $name) {
                    if (is_numeric($email)) {
                        $this->mailer->addBCC($name);
                    } else {
                        $this->mailer->addBCC($email, $name);
                    }
                }
            }

            // Add attachments if specified
            if (!empty($options['attachments'])) {
                foreach ($options['attachments'] as $attachment) {
                    if (is_array($attachment)) {
                        $this->mailer->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? basename($attachment['path'])
                        );
                    } else {
                        $this->mailer->addAttachment($attachment);
                    }
                }
            }

            // Add custom headers if specified
            if (!empty($options['headers'])) {
                foreach ($options['headers'] as $name => $value) {
                    $this->mailer->addCustomHeader($name, $value);
                }
            }

            // Send email
            $result = $this->mailer->send();

            // Log successful send
            if ($this->config['logging'] ?? true) {
                LogHelper::info('Email sent successfully', [
                    'to' => is_array($to) ? array_keys($to) : [$to],
                    'subject' => $subject,
                    'driver' => $this->config['driver']
                ]);
            }

            return true;
        } catch (Exception $e) {
            // Log error
            if ($this->config['logging'] ?? true) {
                LogHelper::error('Email send failed: ' . $e->getMessage(), [
                    'to' => is_array($to) ? array_keys($to) : [$to],
                    'subject' => $subject,
                    'driver' => $this->config['driver'],
                    'error' => $e->getMessage()
                ]);
            }

            throw new Exception('Email send failed: ' . $e->getMessage());
        }
    }

    /**
     * Send welcome email
     */
    public function sendWelcome($user, $password = null)
    {
        $template = $this->renderEmailTemplate('welcome', [
            'user' => $user,
            'password' => $password,
            'login_url' => app_url('/login'),
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, 'Bem-vindo ao ' . config('app.name'), $template);
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($user, $token)
    {
        $template = $this->renderEmailTemplate('password_reset', [
            'user' => $user,
            'token' => $token,
            'reset_url' => app_url('/reset-password/' . $token),
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, 'Redefinir Senha - ' . config('app.name'), $template);
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification($user, $token)
    {
        $template = $this->renderEmailTemplate('email_verification', [
            'user' => $user,
            'token' => $token,
            'verification_url' => app_url('/verify-email/' . $token),
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, 'Verificar Email - ' . config('app.name'), $template);
    }

    /**
     * Send notification email
     */
    public function sendNotification($user, $title, $message, $data = [])
    {
        $template = $this->renderEmailTemplate('notification', [
            'user' => $user,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, $title . ' - ' . config('app.name'), $template);
    }

    /**
     * Send battle report
     */
    public function sendBattleReport($user, $battle)
    {
        $template = $this->renderEmailTemplate('battle_report', [
            'user' => $user,
            'battle' => $battle,
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, 'Relatório de Batalha - ' . config('app.name'), $template);
    }

    /**
     * Send alliance invitation
     */
    public function sendAllianceInvitation($user, $alliance, $inviter)
    {
        $template = $this->renderEmailTemplate('alliance_invitation', [
            'user' => $user,
            'alliance' => $alliance,
            'inviter' => $inviter,
            'server_name' => config('app.name'),
            'support_email' => $this->config['reply_to']['address'] ?? 'support@puro.com'
        ]);

        return $this->send($user->email, 'Convite para Aliança - ' . config('app.name'), $template);
    }

    /**
     * Render email template
     */
    private function renderEmailTemplate($template, $data = [])
    {
        $templateFile = FileHelper::path("templates/emails/{$template}.php");

        if (!FileHelper::exists($templateFile)) {
            throw new Exception("Email template not found: {$template}");
        }

        // Extract data to make variables available in template
        extract($data);

        // Start output buffering
        ob_start();

        // Include template
        include $templateFile;

        // Get template content
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Test email configuration
     */
    public function test($to = null)
    {
        try {
            $testTo = $to ?? $this->config['from']['address'];

            $template = $this->renderEmailTemplate('test', [
                'server_name' => config('app.name'),
                'test_time' => date('Y-m-d H:i:s'),
                'config' => $this->config
            ]);

            return $this->send($testTo, 'Teste de Email - ' . config('app.name'), $template);
        } catch (Exception $e) {
            throw new Exception('Email test failed: ' . $e->getMessage());
        }
    }

    /**
     * Get email configuration
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Check if email is configured
     */
    public function isConfigured()
    {
        return !empty($this->config['username']) && !empty($this->config['password']);
    }

    /**
     * Get PHPMailer instance for advanced usage
     */
    public function getMailer()
    {
        return $this->mailer;
    }
}
