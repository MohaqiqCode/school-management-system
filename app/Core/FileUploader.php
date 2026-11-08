<?php
namespace App\Core;

class FileUploader
{
    private $config;
    private $errors = [];
    
    public function __construct($config = [])
    {
        $this->config = array_merge([
            'max_file_size' => 5242880, // 5MB
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xlsx', 'xls'],
            'photo_max_size' => 2097152, // 2MB
            'document_max_size' => 10485760, // 10MB
            'upload_path' => 'uploads/'
        ], $config);
    }
    
    public function upload($file, $subfolder = '', $options = [])
    {
        $this->errors = [];
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'No file uploaded';
            return false;
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }
        
        // Validate file type
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = $options['allowed_types'] ?? $this->config['allowed_types'];
        
        if (!in_array($extension, $allowedTypes)) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $allowedTypes);
            return false;
        }
        
        // Validate file size
        $maxSize = $options['max_size'] ?? $this->config['max_file_size'];
        
        if ($file['size'] > $maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($maxSize);
            return false;
        }
        
        // Create upload path
        $uploadDir = $this->getUploadPath($subfolder);
        
        // Generate unique filename
        $filename = $this->generateFilename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'filename' => $filename,
                'original_name' => $file['name'],
                'path' => $filepath,
                'relative_path' => str_replace($this->config['upload_path'], '', $filepath),
                'size' => $file['size'],
                'type' => $file['type'],
                'extension' => $extension
            ];
        }
        
        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }
    
    public function uploadPhoto($file, $subfolder = 'photos/')
    {
        $options = [
            'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_size' => $this->config['photo_max_size']
        ];
        
        return $this->upload($file, $subfolder, $options);
    }
    
    public function uploadDocument($file, $subfolder = 'documents/')
    {
        $options = [
            'allowed_types' => ['pdf', 'doc', 'docx', 'xlsx', 'xls', 'txt', 'csv'],
            'max_size' => $this->config['document_max_size']
        ];
        
        return $this->upload($file, $subfolder, $options);
    }
    
    public function uploadMultiple($files, $subfolder = '')
    {
        $results = [];
        
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = $this->upload($file, $subfolder);
            
            if ($result) {
                $results[] = $result;
            }
        }
        
        return $results;
    }
    
    public function delete($filepath)
    {
        $fullPath = $this->config['upload_path'] . ltrim($filepath, '/');
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function hasErrors()
    {
        return !empty($this->errors);
    }
    
    private function getUploadPath($subfolder)
    {
        $path = rtrim($this->config['upload_path'], '/') . '/' . trim($subfolder, '/') . '/';
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        return $path;
    }
    
    private function generateFilename($originalName)
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }
    
    private function getUploadErrorMessage($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        return $errors[$code] ?? 'Unknown upload error';
    }
    
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}