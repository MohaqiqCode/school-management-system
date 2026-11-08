<?php
namespace App\Controllers\Api;

use App\Core\Controller;

class FileController extends Controller
{
    private $allowedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $allowedDocTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
    private $maxImageSize = 5 * 1024 * 1024; // 5MB
    private $maxDocSize = 10 * 1024 * 1024; // 10MB

    /**
     * Upload single file
     * POST /api/v1/upload
     */
    public function upload()
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'فایلی آپلود نشده است'], 422);
            return;
        }

        $file = $_FILES['file'];
        $type = $_POST['type'] ?? 'document';
        $subfolder = $_POST['subfolder'] ?? 'general';

        $result = $this->processUpload($file, $type, $subfolder);

        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => 'فایل با موفقیت آپلود شد',
                'data' => $result['data']
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']], 422);
        }
    }

    /**
     * Upload multiple files
     * POST /api/v1/upload/multiple
     */
    public function uploadMultiple()
    {
        if (!isset($_FILES['files'])) {
            $this->json(['success' => false, 'message' => 'فایلی آپلود نشده است'], 422);
            return;
        }

        $files = $_FILES['files'];
        $type = $_POST['type'] ?? 'document';
        $subfolder = $_POST['subfolder'] ?? 'general';
        $results = [];

        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $singleFile = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                $results[] = $this->processUpload($singleFile, $type, $subfolder);
            }
        }

        $successful = array_filter($results, fn($r) => $r['success']);

        $this->json([
            'success' => true,
            'message' => count($successful) . ' فایل با موفقیت آپلود شد',
            'data' => ['uploaded' => array_column($successful, 'data'), 'total' => count($results)]
        ]);
    }

    /**
     * Delete uploaded file
     * DELETE /api/v1/upload/{id}
     */
    public function delete($id)
    {
        $filePath = $_POST['file_path'] ?? '';

        if (empty($filePath)) {
            $this->json(['success' => false, 'message' => 'مسیر فایل الزامی است'], 422);
            return;
        }

        $fullPath = __DIR__ . '/../../../public' . $filePath;

        if (file_exists($fullPath)) {
            unlink($fullPath);
            $this->json(['success' => true, 'message' => 'فایل با موفقیت حذف شد']);
        } else {
            $this->json(['success' => false, 'message' => 'فایل یافت نشد'], 404);
        }
    }

    /**
     * Process file upload
     */
    private function processUpload($file, $type, $subfolder)
    {
        // Validate file
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedTypes = $type === 'image' ? $this->allowedImageTypes : array_merge($this->allowedImageTypes, $this->allowedDocTypes);
        $maxSize = $type === 'image' ? $this->maxImageSize : $this->maxDocSize;

        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => 'فرمت فایل مجاز نیست. فرمت‌های مجاز: ' . implode(', ', $allowedTypes)];
        }

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'حجم فایل بیشتر از حد مجاز (' . ($maxSize / 1024 / 1024) . 'MB) است'];
        }

        // Sanitize filename
        $filename = $this->sanitizeFilename($file['name']);
        $uniqueName = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        // Create upload directory
        $uploadDir = __DIR__ . '/../../../public/assets/uploads/' . $subfolder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filepath = $uploadDir . $uniqueName;
        $relativePath = '/assets/uploads/' . $subfolder . '/' . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'data' => [
                    'filename' => $uniqueName,
                    'original_name' => $filename,
                    'path' => $relativePath,
                    'url' => $this->config['app_url'] . $relativePath,
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'extension' => $extension
                ]
            ];
        }

        return ['success' => false, 'message' => 'خطا در ذخیره فایل'];
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename($filename)
    {
        // Remove path info
        $filename = basename($filename);
        // Remove special characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        // Remove multiple dots
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        return $filename;
    }
}