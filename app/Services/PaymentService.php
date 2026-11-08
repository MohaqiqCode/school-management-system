<?php
namespace App\Services;

use App\Core\Application;

/**
 * Payment Service
 * سرویس درگاه پرداخت
 */
class PaymentService
{
    private $config;
    private $gateway;
    private $db;
    private $logger;

    /**
     * Available payment gateways
     */
    private $gateways = [
        'zarinpal' => [
            'request_url' => 'https://api.zarinpal.com/pg/v4/payment/request.json',
            'verify_url' => 'https://api.zarinpal.com/pg/v4/payment/verify.json',
            'sandbox_request_url' => 'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
            'sandbox_verify_url' => 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
            'start_pay_url' => 'https://www.zarinpal.com/pg/StartPay/{authority}',
            'sandbox_start_pay_url' => 'https://sandbox.zarinpal.com/pg/StartPay/{authority}',
        ],
        'zibal' => [
            'request_url' => 'https://gateway.zibal.ir/v1/request',
            'verify_url' => 'https://gateway.zibal.ir/v1/verify',
            'start_pay_url' => 'https://gateway.zibal.ir/start/{trackId}',
        ],
        'nextpay' => [
            'request_url' => 'https://nextpay.org/nx/gateway/token',
            'verify_url' => 'https://nextpay.org/nx/gateway/verify',
            'start_pay_url' => 'https://nextpay.org/nx/gateway/payment/{trans_id}',
        ],
    ];

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/services.php';
        $paymentConfig = $this->config['payment'] ?? [];
        
        $this->gateway = $paymentConfig['default'] ?? 'zarinpal';
        $this->db = Application::getInstance()->getDB();
        $this->logger = Application::getInstance()->getLogger();
    }

    /**
     * Create payment request
     * 
     * @param float $amount Payment amount in Toman
     * @param string $description Payment description
     * @param array $metadata Additional data (user_id, fee_id, etc.)
     * @return array Payment URL and authority
     */
    public function requestPayment($amount, $description = '', $metadata = [])
    {
        $amount = intval($amount);
        
        if ($amount < 1000) {
            return ['success' => false, 'message' => 'حداقل مبلغ پرداخت ۱۰۰۰ تومان است'];
        }

        // Save payment record
        $paymentId = $this->createPaymentRecord($amount, $description, $metadata);

        switch ($this->gateway) {
            case 'zarinpal':
                return $this->requestZarinpal($amount, $description, $paymentId, $metadata);
            case 'zibal':
                return $this->requestZibal($amount, $description, $paymentId, $metadata);
            case 'nextpay':
                return $this->requestNextpay($amount, $description, $paymentId, $metadata);
            default:
                return ['success' => false, 'message' => 'درگاه پرداخت پیکربندی نشده است'];
        }
    }

    /**
     * Verify payment
     * 
     * @param array $params Payment callback parameters
     * @return array Verification result
     */
    public function verifyPayment($params)
    {
        switch ($this->gateway) {
            case 'zarinpal':
                return $this->verifyZarinpal($params);
            case 'zibal':
                return $this->verifyZibal($params);
            case 'nextpay':
                return $this->verifyNextpay($params);
            default:
                return ['success' => false, 'message' => 'درگاه پرداخت پیکربندی نشده است'];
        }
    }

    // ============================================
    // Zarinpal Implementation
    // ============================================

    private function requestZarinpal($amount, $description, $paymentId, $metadata)
    {
        $gatewayConfig = $this->config['payment']['gateways']['zarinpal'] ?? [];
        $isSandbox = $gatewayConfig['sandbox'] ?? false;
        $merchantId = $gatewayConfig['merchant_id'] ?? '';
        
        $url = $isSandbox 
            ? $this->gateways['zarinpal']['sandbox_request_url'] 
            : $this->gateways['zarinpal']['request_url'];

        $callbackUrl = ($gatewayConfig['callback_url'] ?? $this->getAppUrl()) . '?payment_id=' . $paymentId;

        $data = [
            'merchant_id' => $merchantId,
            'amount' => $amount,
            'description' => $description,
            'callback_url' => $callbackUrl,
            'metadata' => array_merge($metadata, ['payment_id' => $paymentId]),
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['data']['code']) && $response['data']['code'] == 100) {
            $authority = $response['data']['authority'];
            $startPayUrl = $isSandbox 
                ? $this->gateways['zarinpal']['sandbox_start_pay_url'] 
                : $this->gateways['zarinpal']['start_pay_url'];

            // Update payment record with authority
            $this->updatePaymentRecord($paymentId, [
                'authority' => $authority,
                'status' => 'pending'
            ]);

            return [
                'success' => true,
                'data' => [
                    'payment_id' => $paymentId,
                    'authority' => $authority,
                    'payment_url' => str_replace('{authority}', $authority, $startPayUrl),
                ]
            ];
        }

        $this->logger->error('Zarinpal request failed', ['response' => $response]);
        return [
            'success' => false,
            'message' => $response['errors']['message'] ?? 'خطا در اتصال به درگاه پرداخت'
        ];
    }

    private function verifyZarinpal($params)
    {
        $authority = $params['Authority'] ?? '';
        $status = $params['Status'] ?? '';
        $paymentId = $params['payment_id'] ?? '';

        if ($status !== 'OK') {
            $this->updatePaymentRecord($paymentId, ['status' => 'failed']);
            return ['success' => false, 'message' => 'پرداخت توسط کاربر لغو شد'];
        }

        $gatewayConfig = $this->config['payment']['gateways']['zarinpal'] ?? [];
        $isSandbox = $gatewayConfig['sandbox'] ?? false;
        $merchantId = $gatewayConfig['merchant_id'] ?? '';
        
        $url = $isSandbox 
            ? $this->gateways['zarinpal']['sandbox_verify_url'] 
            : $this->gateways['zarinpal']['verify_url'];

        // Get payment record for amount
        $payment = $this->getPaymentRecord($paymentId);

        $data = [
            'merchant_id' => $merchantId,
            'amount' => $payment['amount'] ?? 0,
            'authority' => $authority,
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['data']['code']) && $response['data']['code'] == 100) {
            $refId = $response['data']['ref_id'];
            
            $this->updatePaymentRecord($paymentId, [
                'ref_id' => $refId,
                'status' => 'verified',
                'verified_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info('Payment verified', [
                'payment_id' => $paymentId,
                'ref_id' => $refId,
                'amount' => $payment['amount'] ?? 0
            ]);

            return [
                'success' => true,
                'message' => 'پرداخت با موفقیت تأیید شد',
                'data' => [
                    'ref_id' => $refId,
                    'payment_id' => $paymentId,
                    'amount' => $payment['amount'] ?? 0,
                ]
            ];
        }

        $this->updatePaymentRecord($paymentId, ['status' => 'verification_failed']);
        return [
            'success' => false,
            'message' => $response['errors']['message'] ?? 'خطا در تأیید پرداخت'
        ];
    }

    // ============================================
    // Zibal Implementation
    // ============================================

    private function requestZibal($amount, $description, $paymentId, $metadata)
    {
        $gatewayConfig = $this->config['payment']['gateways']['zibal'] ?? [];
        $isSandbox = $gatewayConfig['sandbox'] ?? false;
        $merchant = $gatewayConfig['merchant'] ?? '';
        
        $url = $this->gateways['zibal']['request_url'];
        $callbackUrl = ($gatewayConfig['callback_url'] ?? $this->getAppUrl()) . '?payment_id=' . $paymentId;

        $data = [
            'merchant' => $isSandbox ? 'zibal' : $merchant,
            'amount' => $amount,
            'description' => $description,
            'callbackUrl' => $callbackUrl,
            'orderId' => (string)$paymentId,
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['result']) && $response['result'] == 100) {
            $trackId = $response['trackId'];
            
            $this->updatePaymentRecord($paymentId, [
                'authority' => $trackId,
                'status' => 'pending'
            ]);

            return [
                'success' => true,
                'data' => [
                    'payment_id' => $paymentId,
                    'track_id' => $trackId,
                    'payment_url' => str_replace('{trackId}', $trackId, $this->gateways['zibal']['start_pay_url']),
                ]
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'خطا در اتصال به درگاه پرداخت'
        ];
    }

    private function verifyZibal($params)
    {
        $trackId = $params['trackId'] ?? '';
        $success = $params['success'] ?? '';
        $paymentId = $params['payment_id'] ?? '';

        if ($success != 1) {
            $this->updatePaymentRecord($paymentId, ['status' => 'failed']);
            return ['success' => false, 'message' => 'پرداخت توسط کاربر لغو شد'];
        }

        $gatewayConfig = $this->config['payment']['gateways']['zibal'] ?? [];
        $merchant = $gatewayConfig['merchant'] ?? '';
        
        $url = $this->gateways['zibal']['verify_url'];
        $data = ['merchant' => $merchant, 'trackId' => $trackId];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['result']) && $response['result'] == 100) {
            $this->updatePaymentRecord($paymentId, [
                'ref_id' => $response['refNumber'] ?? $trackId,
                'status' => 'verified',
                'verified_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'پرداخت با موفقیت تأیید شد',
                'data' => [
                    'ref_id' => $response['refNumber'] ?? $trackId,
                    'payment_id' => $paymentId,
                ]
            ];
        }

        $this->updatePaymentRecord($paymentId, ['status' => 'verification_failed']);
        return ['success' => false, 'message' => $response['message'] ?? 'خطا در تأیید پرداخت'];
    }

    // ============================================
    // Nextpay Implementation
    // ============================================

    private function requestNextpay($amount, $description, $paymentId, $metadata)
    {
        $gatewayConfig = $this->config['payment']['gateways']['nextpay'] ?? [];
        $apiKey = $gatewayConfig['api_key'] ?? '';
        
        $url = $this->gateways['nextpay']['request_url'];
        $callbackUrl = ($gatewayConfig['callback_url'] ?? $this->getAppUrl()) . '?payment_id=' . $paymentId;

        $data = [
            'api_key' => $apiKey,
            'amount' => $amount,
            'order_id' => (string)$paymentId,
            'callback_uri' => $callbackUrl,
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['code']) && $response['code'] == -1) {
            $transId = $response['trans_id'];
            
            $this->updatePaymentRecord($paymentId, [
                'authority' => $transId,
                'status' => 'pending'
            ]);

            return [
                'success' => true,
                'data' => [
                    'payment_id' => $paymentId,
                    'trans_id' => $transId,
                    'payment_url' => str_replace('{trans_id}', $transId, $this->gateways['nextpay']['start_pay_url']),
                ]
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'خطا در اتصال به درگاه پرداخت'
        ];
    }

    private function verifyNextpay($params)
    {
        $transId = $params['trans_id'] ?? '';
        $paymentId = $params['payment_id'] ?? '';

        $gatewayConfig = $this->config['payment']['gateways']['nextpay'] ?? [];
        $apiKey = $gatewayConfig['api_key'] ?? '';
        
        $url = $this->gateways['nextpay']['verify_url'];
        $payment = $this->getPaymentRecord($paymentId);

        $data = [
            'api_key' => $apiKey,
            'trans_id' => $transId,
            'amount' => $payment['amount'] ?? 0,
        ];

        $response = $this->makeRequest($url, $data);

        if ($response && isset($response['code']) && $response['code'] == 0) {
            $this->updatePaymentRecord($paymentId, [
                'ref_id' => $response['Shaparak_Ref_Id'] ?? $transId,
                'status' => 'verified',
                'verified_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'پرداخت با موفقیت تأیید شد',
                'data' => [
                    'ref_id' => $response['Shaparak_Ref_Id'] ?? $transId,
                    'payment_id' => $paymentId,
                ]
            ];
        }

        $this->updatePaymentRecord($paymentId, ['status' => 'verification_failed']);
        return ['success' => false, 'message' => $response['message'] ?? 'خطا در تأیید پرداخت'];
    }

    // ============================================
    // Database Operations
    // ============================================

    /**
     * Create payment record in database
     */
    private function createPaymentRecord($amount, $description, $metadata)
    {
        return $this->db->insert('payment_transactions', [
            'amount' => $amount,
            'description' => $description,
            'gateway' => $this->gateway,
            'user_id' => $metadata['user_id'] ?? null,
            'fee_id' => $metadata['fee_id'] ?? null,
            'metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE),
            'status' => 'initiated',
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Update payment record
     */
    private function updatePaymentRecord($paymentId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('payment_transactions', $data, 'id = ?', [$paymentId]);
    }

    /**
     * Get payment record
     */
    private function getPaymentRecord($paymentId)
    {
        return $this->db->selectOne("SELECT * FROM payment_transactions WHERE id = ?", [$paymentId]);
    }

    /**
     * Make HTTP request
     */
    private function makeRequest($url, $data = [])
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                $this->logger->error('Payment API request failed', ['error' => $error]);
                return null;
            }

            return json_decode($result, true);

        } catch (\Exception $e) {
            $this->logger->error('Payment API exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get application URL
     */
    private function getAppUrl()
    {
        return Application::getInstance()->getConfig('app_url', 'http://localhost');
    }
}