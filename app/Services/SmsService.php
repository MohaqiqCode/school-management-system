<?php
namespace App\Services;

use App\Core\Application;

/**
 * SMS Service
 * سرویس ارسال پیامک
 */
class SmsService
{
    private $config;
    private $provider;
    private $apiKey;
    private $sender;
    private $baseUrl;
    private $logger;

    /**
     * Available SMS providers
     */
    private $providers = [
        'kavenegar' => [
            'base_url' => 'https://api.kavenegar.com/v1/',
            'send_endpoint' => '{api_key}/sms/send.json',
            'status_endpoint' => '{api_key}/sms/status.json',
            'credit_endpoint' => '{api_key}/account/info.json',
        ],
        'ghasedak' => [
            'base_url' => 'https://api.ghasedak.me/v2/',
            'send_endpoint' => 'sms/send/simple',
            'status_endpoint' => 'sms/status',
        ],
        'melipayamak' => [
            'base_url' => 'https://rest.payamak-panel.com/api/SendSMS/',
            'send_endpoint' => 'SendSMS',
            'status_endpoint' => 'GetDeliveries2',
        ],
    ];

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/services.php';
        $smsConfig = $this->config['sms'] ?? [];
        
        $this->provider = $smsConfig['default'] ?? 'kavenegar';
        $providerConfig = $smsConfig['providers'][$this->provider] ?? [];
        
        $this->apiKey = $providerConfig['api_key'] ?? '';
        $this->sender = $providerConfig['sender'] ?? '';
        $this->baseUrl = $providerConfig['url'] ?? '';
        
        $this->logger = Application::getInstance()->getLogger();
    }

    /**
     * Send single SMS
     * 
     * @param string $receptor Mobile number
     * @param string $message Message text
     * @return array Response
     */
    public function send($receptor, $message)
    {
        return $this->sendBulk([$receptor], $message);
    }

    /**
     * Send bulk SMS
     * 
     * @param array $receptors Array of mobile numbers
     * @param string $message Message text
     * @return array Response
     */
    public function sendBulk(array $receptors, $message)
    {
        // Clean receptors
        $receptors = array_map(function($r) {
            return $this->normalizeMobile($r);
        }, $receptors);
        
        // Filter valid mobiles
        $receptors = array_filter($receptors, function($r) {
            return $this->isValidMobile($r);
        });

        if (empty($receptors)) {
            return ['success' => false, 'message' => 'شماره موبایل معتبری یافت نشد'];
        }

        switch ($this->provider) {
            case 'kavenegar':
                return $this->sendViaKavenegar($receptors, $message);
            case 'ghasedak':
                return $this->sendViaGhasedak($receptors, $message);
            case 'melipayamak':
                return $this->sendViaMelipayamak($receptors, $message);
            default:
                return ['success' => false, 'message' => 'سرویس پیامک پیکربندی نشده است'];
        }
    }

    /**
     * Send SMS using template
     * 
     * @param string $receptor Mobile number
     * @param string $templateName Template name from config
     * @param array $params Template parameters
     * @return array
     */
    public function sendTemplate($receptor, $templateName, array $params = [])
    {
        $templates = $this->config['sms']['templates'] ?? [];
        $template = $templates[$templateName] ?? null;

        if (!$template) {
            return ['success' => false, 'message' => 'قالب پیامک یافت نشد'];
        }

        $message = $template['text'];
        foreach ($params as $key => $value) {
            $message = str_replace('{$' . $key . '}', $value, $message);
        }

        return $this->send($receptor, $message);
    }

    /**
     * Send welcome SMS
     */
    public function sendWelcome($receptor, $name)
    {
        return $this->sendTemplate($receptor, 'welcome', ['name' => $name]);
    }

    /**
     * Send fee reminder SMS
     */
    public function sendFeeReminder($receptor, $name, $amount, $date)
    {
        return $this->sendTemplate($receptor, 'fee_reminder', [
            'name' => $name,
            'amount' => number_format($amount),
            'date' => $date
        ]);
    }

    /**
     * Send payment receipt SMS
     */
    public function sendPaymentReceipt($receptor, $name, $amount, $trackingCode)
    {
        return $this->sendTemplate($receptor, 'payment_receipt', [
            'name' => $name,
            'amount' => number_format($amount),
            'tracking_code' => $trackingCode
        ]);
    }

    /**
     * Send attendance alert SMS
     */
    public function sendAttendanceAlert($receptor, $name, $studentName)
    {
        return $this->sendTemplate($receptor, 'attendance_alert', [
            'name' => $name,
            'student_name' => $studentName
        ]);
    }

    /**
     * Send result published SMS
     */
    public function sendResultPublished($receptor, $examName)
    {
        return $this->sendTemplate($receptor, 'result_published', [
            'exam_name' => $examName
        ]);
    }

    /**
     * Check SMS credit
     * 
     * @return array
     */
    public function getCredit()
    {
        switch ($this->provider) {
            case 'kavenegar':
                return $this->getKavenegarCredit();
            default:
                return ['success' => false, 'message' => 'امکان بررسی اعتبار برای این سرویس وجود ندارد'];
        }
    }

    /**
     * Get SMS delivery status
     * 
     * @param string $messageId
     * @return array
     */
    public function getStatus($messageId)
    {
        switch ($this->provider) {
            case 'kavenegar':
                return $this->getKavenegarStatus($messageId);
            default:
                return ['success' => false, 'message' => 'امکان بررسی وضعیت برای این سرویس وجود ندارد'];
        }
    }

    // ============================================
    // Provider Implementations
    // ============================================

    /**
     * Send via Kavenegar
     */
    private function sendViaKavenegar($receptors, $message)
    {
        $url = $this->baseUrl . str_replace('{api_key}', $this->apiKey, $this->providers['kavenegar']['send_endpoint']);
        
        $data = [
            'receptor' => implode(',', $receptors),
            'sender' => $this->sender,
            'message' => $message,
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['return']['status']) && $response['return']['status'] == 200) {
            $this->logger->info('SMS sent via Kavenegar', [
                'receptors' => count($receptors),
                'message_id' => $response['entries'][0]['messageid'] ?? null
            ]);

            return [
                'success' => true,
                'message' => 'پیامک با موفقیت ارسال شد',
                'data' => [
                    'message_id' => $response['entries'][0]['messageid'] ?? null,
                    'cost' => $response['entries'][0]['cost'] ?? 0,
                ]
            ];
        }

        $this->logger->error('Kavenegar SMS failed', ['response' => $response]);
        return [
            'success' => false,
            'message' => $response['return']['message'] ?? 'خطا در ارسال پیامک'
        ];
    }

    /**
     * Send via Ghasedak
     */
    private function sendViaGhasedak($receptors, $message)
    {
        $url = $this->baseUrl . $this->providers['ghasedak']['send_endpoint'];
        
        $data = [
            'message' => $message,
            'receptor' => implode(',', $receptors),
            'linenumber' => $this->sender,
        ];

        $headers = [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/x-www-form-urlencoded',
        ];

        $response = $this->makeRequest($url, http_build_query($data), $headers);

        if ($response && isset($response['result']['code']) && $response['result']['code'] == 200) {
            return [
                'success' => true,
                'message' => 'پیامک با موفقیت ارسال شد',
                'data' => ['message_id' => $response['items'][0] ?? null]
            ];
        }

        return [
            'success' => false,
            'message' => $response['result']['message'] ?? 'خطا در ارسال پیامک'
        ];
    }

    /**
     * Send via Melipayamak
     */
    private function sendViaMelipayamak($receptors, $message)
    {
        $url = $this->baseUrl . $this->providers['melipayamak']['send_endpoint'];
        
        $data = [
            'username' => $this->config['sms']['providers']['melipayamak']['username'] ?? '',
            'password' => $this->config['sms']['providers']['melipayamak']['password'] ?? '',
            'from' => $this->sender,
            'to' => implode(',', $receptors),
            'text' => $message,
        ];

        $response = $this->makeRequest($url, json_encode($data), ['Content-Type: application/json']);

        if ($response && isset($response['RetStatus']) && $response['RetStatus'] == 1) {
            return [
                'success' => true,
                'message' => 'پیامک با موفقیت ارسال شد',
                'data' => ['message_id' => $response['Value'] ?? null]
            ];
        }

        return [
            'success' => false,
            'message' => $response['StrRetStatus'] ?? 'خطا در ارسال پیامک'
        ];
    }

    /**
     * Get Kavenegar credit
     */
    private function getKavenegarCredit()
    {
        $url = $this->baseUrl . str_replace('{api_key}', $this->apiKey, $this->providers['kavenegar']['credit_endpoint']);
        $response = $this->makeRequest($url);

        if ($response && isset($response['entries']['remaincredit'])) {
            return [
                'success' => true,
                'data' => ['credit' => $response['entries']['remaincredit']]
            ];
        }

        return ['success' => false, 'message' => 'خطا در دریافت اعتبار'];
    }

    /**
     * Get Kavenegar delivery status
     */
    private function getKavenegarStatus($messageId)
    {
        $url = $this->baseUrl . str_replace('{api_key}', $this->apiKey, $this->providers['kavenegar']['status_endpoint']);
        $response = $this->makeRequest($url, ['messageid' => $messageId]);

        if ($response && isset($response['entries'][0])) {
            return [
                'success' => true,
                'data' => $response['entries'][0]
            ];
        }

        return ['success' => false, 'message' => 'خطا در دریافت وضعیت'];
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($url, $data = [], $headers = [])
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
            }

            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $this->logger->error('SMS API request failed', ['error' => $error]);
                return null;
            }

            return json_decode($result, true);

        } catch (\Exception $e) {
            $this->logger->error('SMS API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Normalize mobile number
     */
    private function normalizeMobile($mobile)
    {
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        if (strlen($mobile) === 10 && substr($mobile, 0, 1) === '9') {
            return '0' . $mobile;
        }
        
        if (strlen($mobile) === 11 && substr($mobile, 0, 2) === '09') {
            return $mobile;
        }
        
        if (strlen($mobile) === 12 && substr($mobile, 0, 3) === '980') {
            return '0' . substr($mobile, 3);
        }
        
        if (strlen($mobile) === 13 && substr($mobile, 0, 4) === '0098') {
            return '0' . substr($mobile, 4);
        }
        
        return $mobile;
    }

    /**
     * Validate Iranian mobile number
     */
    private function isValidMobile($mobile)
    {
        return preg_match('/^09\d{9}$/', $mobile) === 1;
    }
}