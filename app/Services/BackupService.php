<?php
namespace App\Services;

use App\Core\Application;

/**
 * Backup Service
 * سرویس پشتیبان‌گیری از سیستم
 */
class BackupService
{
    private $db;
    private $config;
    private $backupPath;
    private $logger;

    public function __construct()
    {
        $this->db = Application::getInstance()->getDB();
        $this->config = Application::getInstance()->getConfig();
        $this->backupPath = __DIR__ . '/../../storage/backups/';
        $this->logger = Application::getInstance()->getLogger();

        // Ensure backup directory exists
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Create backup
     * 
     * @param string $type database, files, full
     * @param bool $compress Compress backup
     * @param bool $download Download after creation
     * @return array
     */
    public function create($type = 'database', $compress = true, $download = false)
    {
        $timestamp = date('Y-m-d_H-i-s');
        $backupId = 'backup_' . $timestamp;
        $backupDir = $this->backupPath . $backupId . '/';
        $files = [];

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        try {
            // Backup database
            if ($type === 'database' || $type === 'full') {
                $dbFile = $this->backupDatabase($backupDir, $timestamp);
                $files[] = $dbFile;
            }

            // Backup files
            if ($type === 'files' || $type === 'full') {
                $filesDir = $this->backupFiles($backupDir, $timestamp);
                $files[] = $filesDir;
            }

            // Create metadata
            $metadata = [
                'id' => $backupId,
                'type' => $type,
                'timestamp' => date('Y-m-d H:i:s'),
                'files' => $files,
                'database' => $this->config['connections']['mysql']['database'] ?? '',
                'app_version' => $this->config['app_version'] ?? '1.0.0',
            ];
            file_put_contents($backupDir . 'metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            // Compress
            $backupFile = null;
            if ($compress) {
                $backupFile = $this->compressBackup($backupDir, $backupId);
                // Remove uncompressed directory
                $this->removeDirectory($backupDir);
            }

            // Save record to database
            $this->saveBackupRecord($backupId, $type, $backupFile ?? $backupDir);

            $this->logger->info("Backup created: {$backupId}", ['type' => $type]);

            $result = [
                'success' => true,
                'message' => 'پشتیبان با موفقیت ایجاد شد',
                'data' => [
                    'id' => $backupId,
                    'type' => $type,
                    'file' => $backupFile ? basename($backupFile) : basename($backupDir),
                    'size' => $backupFile ? $this->formatBytes(filesize($backupFile)) : $this->getDirectorySize($backupDir, true),
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            ];

            // Download if requested
            if ($download && $backupFile) {
                $this->downloadBackup($backupFile);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Backup failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطا در ایجاد پشتیبان: ' . $e->getMessage()];
        }
    }

    /**
     * Restore backup
     * 
     * @param string $backupId Backup ID
     * @return array
     */
    public function restore($backupId)
    {
        // Find backup file
        $backupFile = $this->findBackupFile($backupId);
        if (!$backupFile) {
            return ['success' => false, 'message' => 'فایل پشتیبان یافت نشد'];
        }

        try {
            // Create temp directory for extraction
            $tempDir = $this->backupPath . 'temp_restore/';
            if (is_dir($tempDir)) $this->removeDirectory($tempDir);
            mkdir($tempDir, 0755, true);

            // Extract if compressed
            if (pathinfo($backupFile, PATHINFO_EXTENSION) === 'zip') {
                $this->extractZip($backupFile, $tempDir);
            } else {
                // Copy directory
                $this->copyDirectory($backupFile, $tempDir);
            }

            // Read metadata
            $metadata = json_decode(file_get_contents($tempDir . 'metadata.json'), true);

            // Restore database
            $dbFile = $tempDir . 'database_' . ($metadata['timestamp'] ?? '') . '.sql';
            if (file_exists($dbFile)) {
                $this->restoreDatabase($dbFile);
            }

            // Restore files
            $filesDir = $tempDir . 'files_' . ($metadata['timestamp'] ?? '') . '/';
            if (is_dir($filesDir)) {
                $this->restoreFiles($filesDir);
            }

            // Clean up
            $this->removeDirectory($tempDir);

            $this->logger->info("Backup restored: {$backupId}");

            return ['success' => true, 'message' => 'پشتیبان با موفقیت بازیابی شد'];

        } catch (\Exception $e) {
            $this->logger->error('Restore failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'خطا در بازیابی پشتیبان: ' . $e->getMessage()];
        }
    }

    /**
     * Delete backup
     * 
     * @param string $backupId Backup ID
     * @return array
     */
    public function delete($backupId)
    {
        $backupFile = $this->findBackupFile($backupId);
        if (!$backupFile) {
            return ['success' => false, 'message' => 'فایل پشتیبان یافت نشد'];
        }

        try {
            if (is_dir($backupFile)) {
                $this->removeDirectory($backupFile);
            } else {
                unlink($backupFile);
            }

            // Remove database record
            $this->db->delete('backups', 'backup_id = ?', [$backupId]);

            $this->logger->info("Backup deleted: {$backupId}");

            return ['success' => true, 'message' => 'پشتیبان با موفقیت حذف شد'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'خطا در حذف پشتیبان: ' . $e->getMessage()];
        }
    }

    /**
     * Get list of backups
     * 
     * @return array
     */
    public function getList()
    {
        $backups = [];

        // Get from database
        $records = $this->db->select("SELECT * FROM backups ORDER BY created_at DESC");

        // Scan backup directory
        $files = glob($this->backupPath . 'backup_*');
        if ($files) {
            rsort($files);
        }

        foreach ($files as $file) {
            $basename = basename($file);
            $backupId = pathinfo($basename, PATHINFO_FILENAME);
            if (pathinfo($basename, PATHINFO_EXTENSION) === 'zip') {
                $backupId = pathinfo($basename, PATHINFO_FILENAME);
            }

            $dbRecord = array_filter($records, fn($r) => $r['backup_id'] === $backupId);
            $dbRecord = reset($dbRecord);

            $backups[] = [
                'id' => $backupId,
                'filename' => $basename,
                'size' => is_file($file) ? filesize($file) : $this->getDirectorySize($file),
                'type' => $dbRecord['type'] ?? 'unknown',
                'created_at' => $dbRecord['created_at'] ?? date('Y-m-d H:i:s', filemtime($file)),
            ];
        }

        return $backups;
    }

    /**
     * Get backup file path for download
     */
    public function getFilePath($backupId)
    {
        return $this->findBackupFile($backupId);
    }

    /**
     * Get backup file for download
     */
    public function getFileForDownload($backupId)
    {
        $filePath = $this->findBackupFile($backupId);
        if (!$filePath) return null;

        // If directory, compress first
        if (is_dir($filePath)) {
            $zipFile = $this->compressBackup($filePath, $backupId);
            return $zipFile;
        }

        return $filePath;
    }

    /**
     * Download backup file
     */
    public function downloadBackup($filePath)
    {
        if (!file_exists($filePath)) return;

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($filePath);
        exit;
    }

    /**
     * Clean old backups
     */
    public function cleanOld($keepLast = 7)
    {
        $backups = $this->getList();
        if (count($backups) <= $keepLast) return;

        $toDelete = array_slice($backups, $keepLast);
        foreach ($toDelete as $backup) {
            $this->delete($backup['id']);
        }

        return ['success' => true, 'message' => count($toDelete) . ' پشتیبان قدیمی حذف شد'];
    }

    // ============================================
    // Private Methods
    // ============================================

    /**
     * Backup database to SQL file
     */
    private function backupDatabase($backupDir, $timestamp)
    {
        $dbConfig = $this->config['connections']['mysql'];
        $filename = 'database_' . $timestamp . '.sql';
        $filepath = $backupDir . $filename;

        // Get all tables
        $tables = $this->db->select("SHOW TABLES");
        $output = '';

        $output .= "-- School Management System - Database Backup\n";
        $output .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Database: {$dbConfig['database']}\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
        $output .= "SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n";
        $output .= "SET NAMES utf8mb4;\n\n";

        foreach ($tables as $table) {
            $tableName = reset($table);
            
            // Get create table syntax
            $createTable = $this->db->selectOne("SHOW CREATE TABLE `{$tableName}`");
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output .= $createTable['Create Table'] . ";\n\n";

            // Get table data
            $rows = $this->db->select("SELECT * FROM `{$tableName}`");
            if (!empty($rows)) {
                $output .= "INSERT INTO `{$tableName}` VALUES\n";
                $valueStrings = [];
                foreach ($rows as $row) {
                    $values = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . str_replace("'", "\\'", $val) . "'";
                    }, $row);
                    $valueStrings[] = '(' . implode(', ', $values) . ')';
                }
                $output .= implode(",\n", $valueStrings) . ";\n\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($filepath, $output);

        return $filename;
    }

    /**
     * Restore database from SQL file
     */
    private function restoreDatabase($sqlFile)
    {
        $sql = file_get_contents($sqlFile);
        
        // Split by statements
        $statements = explode(";\n", $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $this->db->query($statement);
                } catch (\Exception $e) {
                    $this->logger->warning('SQL restore error', ['error' => $e->getMessage(), 'statement' => substr($statement, 0, 100)]);
                }
            }
        }
    }

    /**
     * Backup files
     */
    private function backupFiles($backupDir, $timestamp)
    {
        $dirname = 'files_' . $timestamp;
        $destDir = $backupDir . $dirname . '/';
        mkdir($destDir, 0755, true);

        // Backup uploads directory
        $uploadDir = __DIR__ . '/../../public/assets/uploads/';
        if (is_dir($uploadDir)) {
            $this->copyDirectory($uploadDir, $destDir . 'uploads/');
        }

        return $dirname;
    }

    /**
     * Restore files
     */
    private function restoreFiles($filesDir)
    {
        // Restore uploads
        $uploadSource = $filesDir . 'uploads/';
        $uploadDest = __DIR__ . '/../../public/assets/uploads/';
        if (is_dir($uploadSource)) {
            $this->copyDirectory($uploadSource, $uploadDest);
        }
    }

    /**
     * Compress backup directory to ZIP
     */
    private function compressBackup($backupDir, $backupId)
    {
        $zipFile = $this->backupPath . $backupId . '.zip';

        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($backupDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($backupDir));
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            return $zipFile;
        }

        return null;
    }

    /**
     * Extract ZIP file
     */
    private function extractZip($zipFile, $destDir)
    {
        $zip = new \ZipArchive();
        if ($zip->open($zipFile) === true) {
            $zip->extractTo($destDir);
            $zip->close();
        }
    }

    /**
     * Find backup file by ID
     */
    private function findBackupFile($backupId)
    {
        // Check for ZIP file
        $zipFile = $this->backupPath . $backupId . '.zip';
        if (file_exists($zipFile)) {
            return $zipFile;
        }

        // Check for directory
        $dir = $this->backupPath . $backupId . '/';
        if (is_dir($dir)) {
            return $dir;
        }

        return null;
    }

    /**
     * Save backup record to database
     */
    private function saveBackupRecord($backupId, $type, $filePath)
    {
        $size = is_file($filePath) ? filesize($filePath) : $this->getDirectorySize($filePath);

        $this->db->insert('backups', [
            'backup_id' => $backupId,
            'type' => $type,
            'file_path' => $filePath,
            'file_size' => $size,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory($src, $dst)
    {
        if (!is_dir($src)) return;

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file == '.' || $file == '..') continue;
            
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            
            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
        closedir($dir);
    }

    /**
     * Remove directory recursively
     */
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Get directory size
     */
    private function getDirectorySize($path, $formatted = false)
    {
        $size = 0;
        if (!is_dir($path)) return $formatted ? '0 B' : 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }

        return $formatted ? $this->formatBytes($size) : $size;
    }

    /**
     * Format bytes to human readable
     */
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