<?php
namespace App\Helpers;

/**
 * File Helper Class
 * توابع کمکی برای مدیریت فایل‌ها
 */
class FileHelper
{
    /**
     * Read file contents
     */
    public static function read($path)
    {
        if (!file_exists($path)) {
            throw new \Exception("File not found: {$path}");
        }
        
        return file_get_contents($path);
    }
    
    /**
     * Write contents to file
     */
    public static function write($path, $contents, $lock = true)
    {
        $directory = dirname($path);
        
        if (!is_dir($directory)) {
            self::makeDirectory($directory, 0755, true);
        }
        
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }
    
    /**
     * Append contents to file
     */
    public static function append($path, $contents)
    {
        $directory = dirname($path);
        
        if (!is_dir($directory)) {
            self::makeDirectory($directory, 0755, true);
        }
        
        return file_put_contents($path, $contents, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Delete file
     */
    public static function delete($path)
    {
        if (file_exists($path)) {
            return unlink($path);
        }
        
        return true;
    }
    
    /**
     * Delete multiple files
     */
    public static function deleteMultiple($paths)
    {
        foreach ($paths as $path) {
            self::delete($path);
        }
    }
    
    /**
     * Copy file
     */
    public static function copy($source, $destination)
    {
        $directory = dirname($destination);
        
        if (!is_dir($directory)) {
            self::makeDirectory($directory, 0755, true);
        }
        
        return copy($source, $destination);
    }
    
    /**
     * Move file
     */
    public static function move($source, $destination)
    {
        $directory = dirname($destination);
        
        if (!is_dir($directory)) {
            self::makeDirectory($directory, 0755, true);
        }
        
        return rename($source, $destination);
    }
    
    /**
     * Get file extension
     */
    public static function extension($path)
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }
    
    /**
     * Get file name without extension
     */
    public static function name($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }
    
    /**
     * Get file basename
     */
    public static function basename($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }
    
    /**
     * Get file size in bytes
     */
    public static function size($path)
    {
        return file_exists($path) ? filesize($path) : 0;
    }
    
    /**
     * Get file size formatted
     */
    public static function sizeFormatted($path)
    {
        return Helper::formatBytes(self::size($path));
    }
    
    /**
     * Get file MIME type
     */
    public static function mimeType($path)
    {
        if (!file_exists($path)) {
            return 'application/octet-stream';
        }
        
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mimeType;
        }
        
        return mime_content_type($path);
    }
    
    /**
     * Get last modified time
     */
    public static function lastModified($path)
    {
        return file_exists($path) ? filemtime($path) : 0;
    }
    
    /**
     * Check if file exists
     */
    public static function exists($path)
    {
        return file_exists($path);
    }
    
    /**
     * Check if path is a file
     */
    public static function isFile($path)
    {
        return is_file($path);
    }
    
    /**
     * Check if path is a directory
     */
    public static function isDirectory($path)
    {
        return is_dir($path);
    }
    
    /**
     * Check if file is readable
     */
    public static function isReadable($path)
    {
        return is_readable($path);
    }
    
    /**
     * Check if file is writable
     */
    public static function isWritable($path)
    {
        return is_writable($path);
    }
    
    /**
     * Check if file is executable
     */
    public static function isExecutable($path)
    {
        return is_executable($path);
    }
    
    /**
     * Check if file is an image
     */
    public static function isImage($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        
        $mimeType = self::mimeType($path);
        return strpos($mimeType, 'image/') === 0;
    }
    
    /**
     * Get image dimensions
     */
    public static function imageDimensions($path)
    {
        if (!self::isImage($path)) {
            return ['width' => 0, 'height' => 0];
        }
        
        $info = getimagesize($path);
        
        return [
            'width' => $info[0] ?? 0,
            'height' => $info[1] ?? 0,
            'type' => $info[2] ?? 0,
            'mime' => $info['mime'] ?? '',
        ];
    }
    
    /**
     * Create directory
     */
    public static function makeDirectory($path, $mode = 0755, $recursive = false)
    {
        if (is_dir($path)) {
            return true;
        }
        
        return mkdir($path, $mode, $recursive);
    }
    
    /**
     * Delete directory
     */
    public static function deleteDirectory($path, $preserve = false)
    {
        if (!is_dir($path)) {
            return true;
        }
        
        $items = new \FilesystemIterator($path);
        
        foreach ($items as $item) {
            if ($item->isDir() && !$item->isLink()) {
                self::deleteDirectory($item->getPathname());
            } else {
                self::delete($item->getPathname());
            }
        }
        
        if (!$preserve) {
            @rmdir($path);
        }
        
        return true;
    }
    
    /**
     * Clean directory (delete all contents)
     */
    public static function cleanDirectory($path)
    {
        return self::deleteDirectory($path, true);
    }
    
    /**
     * Copy directory recursively
     */
    public static function copyDirectory($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!is_dir($destination)) {
            self::makeDirectory($destination, 0755, true);
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $target = $destination . '/' . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                self::makeDirectory($target);
            } else {
                self::copy($item->getPathname(), $target);
            }
        }
        
        return true;
    }
    
    /**
     * Get all files in directory
     */
    public static function files($directory, $pattern = '*')
    {
        $files = [];
        
        foreach (glob($directory . '/' . $pattern) as $file) {
            if (is_file($file)) {
                $files[] = $file;
            }
        }
        
        return $files;
    }
    
    /**
     * Get all directories in directory
     */
    public static function directories($directory)
    {
        $directories = [];
        
        foreach (glob($directory . '/*', GLOB_ONLYDIR) as $dir) {
            $directories[] = $dir;
        }
        
        return $directories;
    }
    
    /**
     * Get all files recursively
     */
    public static function allFiles($directory, $hidden = false)
    {
        $files = [];
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($hidden || $file->getFilename()[0] !== '.') {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
    
    /**
     * Generate unique filename
     */
    public static function uniqueName($directory, $extension = '')
    {
        do {
            $name = Helper::generateRandomString(16);
            if ($extension) {
                $name .= '.' . ltrim($extension, '.');
            }
        } while (file_exists($directory . '/' . $name));
        
        return $name;
    }
    
    /**
     * Sanitize filename
     */
    public static function sanitizeName($filename)
    {
        // Remove anything which isn't a word, number, space, dash, or dot
        $filename = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $filename);
        // Remove any runs of periods
        $filename = mb_ereg_replace("([\.]{2,})", '', $filename);
        
        return $filename;
    }
    
    /**
     * Get file extension from MIME type
     */
    public static function extensionFromMime($mimeType)
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/zip' => 'zip',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
        ];
        
        return $map[$mimeType] ?? 'bin';
    }
    
    /**
     * Get directory size
     */
    public static function directorySize($path)
    {
        $size = 0;
        
        foreach (self::allFiles($path) as $file) {
            $size += filesize($file);
        }
        
        return $size;
    }
    
    /**
     * Get free disk space
     */
    public static function freeSpace($directory)
    {
        return disk_free_space($directory);
    }
    
    /**
     * Get total disk space
     */
    public static function totalSpace($directory)
    {
        return disk_total_space($directory);
    }
    
    /**
     * Create ZIP archive
     */
    public static function createZip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not loaded');
        }
        
        $zip = new \ZipArchive();
        
        if ($zip->open($destination, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        
        if (is_file($source)) {
            $zip->addFile($source, basename($source));
        } elseif (is_dir($source)) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($source) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }
        
        return $zip->close();
    }
    
    /**
     * Extract ZIP archive
     */
    public static function extractZip($source, $destination)
    {
        if (!extension_loaded('zip')) {
            throw new \Exception('ZIP extension is not loaded');
        }
        
        $zip = new \ZipArchive();
        
        if ($zip->open($source) !== true) {
            return false;
        }
        
        if (!is_dir($destination)) {
            self::makeDirectory($destination, 0755, true);
        }
        
        $zip->extractTo($destination);
        $zip->close();
        
        return true;
    }
    
    /**
     * Get CSV data as array
     */
    public static function csvToArray($path, $delimiter = ',', $hasHeader = true)
    {
        if (!file_exists($path)) {
            return [];
        }
        
        $data = [];
        $file = fopen($path, 'r');
        $headers = [];
        
        if ($hasHeader) {
            $headers = fgetcsv($file, 0, $delimiter);
        }
        
        while (($row = fgetcsv($file, 0, $delimiter)) !== false) {
            if ($hasHeader && !empty($headers)) {
                $data[] = array_combine($headers, $row);
            } else {
                $data[] = $row;
            }
        }
        
        fclose($file);
        
        return $data;
    }
    
    /**
     * Save array as CSV file
     */
    public static function arrayToCsv($path, $data, $delimiter = ',')
    {
        $file = fopen($path, 'w');
        
        // Add BOM for UTF-8
        fputs($file, "\xEF\xBB\xBF");
        
        if (!empty($data)) {
            // Headers
            fputcsv($file, array_keys(reset($data)), $delimiter);
            
            // Data
            foreach ($data as $row) {
                fputcsv($file, $row, $delimiter);
            }
        }
        
        fclose($file);
        
        return true;
    }
    
    /**
     * Get file permissions as string
     */
    public static function permissions($path)
    {
        if (!file_exists($path)) {
            return '';
        }
        
        $perms = fileperms($path);
        
        $info = '';
        
        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));
        
        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));
        
        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));
        
        return $info;
    }
}