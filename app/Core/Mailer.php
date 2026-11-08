<?php
namespace App\Core;

class Mailer
{
    private $config;
    private $fromAddress;
    private $fromName;
    
    public function __construct($config = [])
    {
        $this->config = array_merge([
            'from_address' => 'noreply@example.com',
            'from_name' => 'System',
            'mailer' => 'mail' // mail, smtp, sendmail
        ], $config);
        
        $this->fromAddress = $this->config['from_address'];
        $this->fromName = $this->config['from_name'];
    }
    
    public function send($to, $subject, $body, $attachments = [])
    {
        $headers = $this->buildHeaders();
        
        if (!empty($attachments)) {
            $body = $this->buildMultipartBody($body, $attachments);
            $headers .= $this->buildAttachmentHeaders($attachments);
        }
        
        return mail($to, $subject, $body, $headers);
    }
    
    public function sendHtml($to, $subject, $htmlBody, $attachments = [])
    {
        $headers = $this->buildHeaders(true);
        
        if (!empty($attachments)) {
            $boundary = md5(time());
            $headers .= "\r\nMIME-Version: 1.0";
            $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"{$boundary}\"";
            
            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
            $body .= $htmlBody . "\r\n";
            
            foreach ($attachments as $attachment) {
                $body .= "--{$boundary}\r\n";
                $body .= $this->buildAttachmentPart($attachment);
            }
            
            $body .= "--{$boundary}--";
        } else {
            $headers .= "\r\nMIME-Version: 1.0";
            $headers .= "\r\nContent-Type: text/html; charset=UTF-8";
            $body = $htmlBody;
        }
        
        return mail($to, $subject, $body, $headers);
    }
    
    public function sendTemplate($to, $subject, $template, $data = [])
    {
        $templatePath = Application::getInstance()->getBasePath() . '/app/Views/emails/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Email template '{$template}' not found");
        }
        
        extract($data);
        
        ob_start();
        require $templatePath;
        $htmlBody = ob_get_clean();
        
        return $this->sendHtml($to, $subject, $htmlBody);
    }
    
    private function buildHeaders($isHtml = false)
    {
        $headers = "From: {$this->fromName} <{$this->fromAddress}>\r\n";
        $headers .= "Reply-To: {$this->fromAddress}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        return $headers;
    }
    
    private function buildMultipartBody($body, $attachments)
    {
        $boundary = md5(time());
        
        $message = "--{$boundary}\r\n";
        $message .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $message .= $body . "\r\n";
        
        foreach ($attachments as $attachment) {
            $message .= "--{$boundary}\r\n";
            $message .= $this->buildAttachmentPart($attachment);
        }
        
        $message .= "--{$boundary}--";
        
        return $message;
    }
    
    private function buildAttachmentHeaders($attachments)
    {
        $headers = '';
        // Attachment headers are handled in buildMultipartBody
        return $headers;
    }
    
    private function buildAttachmentPart($attachment)
    {
        if (is_string($attachment)) {
            // File path
            $filename = basename($attachment);
            $content = file_get_contents($attachment);
            $type = mime_content_type($attachment);
        } else {
            // Array with file info
            $filename = $attachment['name'] ?? 'attachment';
            $content = $attachment['content'] ?? '';
            $type = $attachment['type'] ?? 'application/octet-stream';
        }
        
        $part = "Content-Type: {$type}; name=\"{$filename}\"\r\n";
        $part .= "Content-Disposition: attachment; filename=\"{$filename}\"\r\n";
        $part .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $part .= chunk_split(base64_encode($content)) . "\r\n";
        
        return $part;
    }
    
    public function setFrom($address, $name = null)
    {
        $this->fromAddress = $address;
        $this->fromName = $name;
        return $this;
    }
}