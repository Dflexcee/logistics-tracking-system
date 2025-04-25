<?php
require_once 'db.php';

class NotificationHelper {
    private $conn;
    private $email_settings;
    private $sms_settings;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->loadSettings();
    }

    private function loadSettings() {
        // Load email settings
        $sql = "SELECT * FROM email_settings WHERE is_active = 1 LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $this->email_settings = $result->fetch_assoc();
        }

        // Load SMS settings
        $sql = "SELECT * FROM sms_settings WHERE is_active = 1 LIMIT 1";
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $this->sms_settings = $result->fetch_assoc();
        }
    }

    public function sendEmail($to, $subject, $message, $is_html = true) {
        if (!$this->email_settings) {
            error_log("Email settings not configured");
            return false;
        }

        try {
            // Try using PHPMailer if available
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendEmailWithPHPMailer($to, $subject, $message, $is_html);
            } else {
                // Fallback to native PHP mail() function
                return $this->sendEmailWithNativeMail($to, $subject, $message, $is_html);
            }
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendEmailWithPHPMailer($to, $subject, $message, $is_html) {
        require_once 'PHPMailer/PHPMailer.php';
        require_once 'PHPMailer/SMTP.php';
        require_once 'PHPMailer/Exception.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $this->email_settings['smtp_host'];
        $mail->Port = $this->email_settings['smtp_port'];
        $mail->SMTPAuth = true;
        $mail->Username = $this->email_settings['smtp_username'];
        $mail->Password = $this->email_settings['smtp_password'];
        
        if ($this->email_settings['smtp_encryption'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($this->email_settings['smtp_encryption'] === 'tls') {
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        // Recipients
        $mail->setFrom($this->email_settings['from_email'], $this->email_settings['from_name']);
        $mail->addAddress($to);

        // Content
        $mail->isHTML($is_html);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();
    }

    private function sendEmailWithNativeMail($to, $subject, $message, $is_html) {
        // Set headers
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: ' . ($is_html ? 'text/html' : 'text/plain') . '; charset=UTF-8';
        $headers[] = 'From: ' . $this->email_settings['from_name'] . ' <' . $this->email_settings['from_email'] . '>';
        $headers[] = 'Reply-To: ' . $this->email_settings['from_email'];
        $headers[] = 'X-Mailer: PHP/' . phpversion();

        // Combine headers into a string
        $headers_string = implode("\r\n", $headers);

        // Send email using PHP's native mail() function
        return mail($to, $subject, $message, $headers_string);
    }

    public function sendSMS($to, $message) {
        if (!$this->sms_settings) {
            error_log("SMS settings not configured");
            return false;
        }

        try {
            switch ($this->sms_settings['provider']) {
                case 'twilio':
                    return $this->sendViaTwilio($to, $message);
                case 'nexmo':
                    return $this->sendViaNexmo($to, $message);
                case 'plivo':
                    return $this->sendViaPlivo($to, $message);
                case 'custom':
                    return $this->sendViaCustomAPI($to, $message);
                default:
                    error_log("Unsupported SMS provider: " . $this->sms_settings['provider']);
                    return false;
            }
        } catch (Exception $e) {
            error_log("SMS sending failed: " . $e->getMessage());
            return false;
        }
    }

    private function sendViaTwilio($to, $message) {
        require_once 'Twilio/autoload.php';
        
        $sid = $this->sms_settings['api_key'];
        $token = $this->sms_settings['api_secret'];
        $from = $this->sms_settings['sender_id'];

        $client = new Twilio\Rest\Client($sid, $token);
        
        try {
            $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message
                ]
            );
            return true;
        } catch (Exception $e) {
            error_log("Twilio error: " . $e->getMessage());
            return false;
        }
    }

    private function sendViaNexmo($to, $message) {
        require_once 'Nexmo/autoload.php';
        
        $basic = new \Nexmo\Client\Credentials\Basic($this->sms_settings['api_key'], $this->sms_settings['api_secret']);
        $client = new \Nexmo\Client($basic);
        
        try {
            $client->message()->send([
                'to' => $to,
                'from' => $this->sms_settings['sender_id'],
                'text' => $message
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Nexmo error: " . $e->getMessage());
            return false;
        }
    }

    private function sendViaPlivo($to, $message) {
        require_once 'Plivo/autoload.php';
        
        $auth_id = $this->sms_settings['api_key'];
        $auth_token = $this->sms_settings['api_secret'];
        $client = new \Plivo\RestClient($auth_id, $auth_token);
        
        try {
            $client->messages->create(
                $this->sms_settings['sender_id'],
                [$to],
                $message
            );
            return true;
        } catch (Exception $e) {
            error_log("Plivo error: " . $e->getMessage());
            return false;
        }
    }

    private function sendViaCustomAPI($to, $message) {
        if (empty($this->sms_settings['api_url'])) {
            error_log("Custom API URL not configured");
            return false;
        }

        $data = [
            'to' => $to,
            'message' => $message,
            'sender_id' => $this->sms_settings['sender_id'],
            'api_key' => $this->sms_settings['api_key']
        ];

        if (!empty($this->sms_settings['api_secret'])) {
            $data['api_secret'] = $this->sms_settings['api_secret'];
        }

        $ch = curl_init($this->sms_settings['api_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $http_code >= 200 && $http_code < 300;
    }
} 