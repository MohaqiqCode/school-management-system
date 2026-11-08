<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class WebhookController extends Controller
{
    /**
     * Payment gateway callback
     * POST /webhooks/payment/callback/{gateway}
     */
    public function paymentCallback($gateway)
    {
        // Log incoming webhook
        $this->logWebhook('payment', $gateway, $_POST);

        switch ($gateway) {
            case 'zarinpal':
                return $this->handleZarinpalCallback();
            case 'zibal':
                return $this->handleZibalCallback();
            case 'nextpay':
                return $this->handleNextpayCallback();
            default:
                $this->json(['success' => false, 'message' => 'Invalid gateway'], 400);
        }
    }

    private function handleZarinpalCallback()
    {
        $authority = $_GET['Authority'] ?? $_POST['Authority'] ?? '';
        $status = $_GET['Status'] ?? $_POST['Status'] ?? '';
        $refId = $_GET['RefID'] ?? $_POST['RefID'] ?? '';

        if ($status === 'OK' && $refId) {
            // Payment successful
            // Verify payment with Zarinpal API
            $this->completePayment($authority, $refId, 'zarinpal');
            $this->redirect($this->config['app_url'] . '/payment/success?ref_id=' . $refId);
        } else {
            $this->redirect($this->config['app_url'] . '/payment/failed');
        }
    }

    private function handleZibalCallback()
    {
        $trackId = $_GET['trackId'] ?? $_POST['trackId'] ?? '';
        $success = $_GET['success'] ?? $_POST['success'] ?? '';

        if ($success == 1 && $trackId) {
            $this->completePayment($trackId, $trackId, 'zibal');
            $this->redirect($this->config['app_url'] . '/payment/success?track_id=' . $trackId);
        } else {
            $this->redirect($this->config['app_url'] . '/payment/failed');
        }
    }

    private function handleNextpayCallback()
    {
        $transId = $_GET['trans_id'] ?? $_POST['trans_id'] ?? '';
        $amount = $_GET['amount'] ?? $_POST['amount'] ?? '';

        if ($transId) {
            $this->completePayment($transId, $transId, 'nextpay');
            $this->redirect($this->config['app_url'] . '/payment/success?trans_id=' . $transId);
        } else {
            $this->redirect($this->config['app_url'] . '/payment/failed');
        }
    }

    private function completePayment($transactionId, $referenceId, $gateway)
    {
        // Find pending payment by transaction ID
        // Update payment status
        // This implementation depends on your payment tracking system
        $this->logActivity('payment', 'webhook', "Payment completed: {$gateway} - {$referenceId}");
    }

    /**
     * SMS delivery callback
     * POST /webhooks/sms/callback/{provider}
     */
    public function smsCallback($provider)
    {
        $data = $_POST;
        $this->logWebhook('sms', $provider, $data);

        switch ($provider) {
            case 'kavenegar':
                $messageId = $data['messageid'] ?? '';
                $status = $data['status'] ?? '';
                $statusText = $data['statustext'] ?? '';
                // Update SMS status in database
                break;
            case 'ghasedak':
                $messageId = $data['message_id'] ?? '';
                $status = $data['status'] ?? '';
                break;
        }

        $this->json(['success' => true, 'message' => 'OK']);
    }

    /**
     * Email tracking (open/pixel)
     * GET /webhooks/email/track/{id}
     */
    public function emailTrack($id)
    {
        // Log email open
        $this->db->query(
            "UPDATE notification_logs SET opened_at = NOW(), open_count = open_count + 1 WHERE id = ? AND type = 'email'",
            [$id]
        );

        // Return a 1x1 transparent pixel
        header('Content-Type: image/gif');
        echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        exit;
    }

    /**
     * Log webhook request
     */
    private function logWebhook($type, $provider, $data)
    {
        try {
            $this->db->insert('system_logs', [
                'level' => 'info',
                'module' => 'webhook',
                'message' => "Webhook received: {$type}/{$provider}",
                'context' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }

    private function logActivity($module, $action, $description)
    {
        try {
            $this->db->insert('audit_logs', [
                'user_id' => $this->session->get('user_id'),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $this->request->getIpAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {}
    }
}