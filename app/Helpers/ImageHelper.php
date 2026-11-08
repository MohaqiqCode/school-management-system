<?php
namespace App\Helpers;

/**
 * Image Helper Class
 * توابع کمکی برای پردازش تصویر
 */
class ImageHelper
{
    /**
     * Supported image types
     */
    private static $supportedTypes = [
        IMAGETYPE_JPEG => 'jpeg',
        IMAGETYPE_PNG => 'png',
        IMAGETYPE_GIF => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];

    /**
     * Create image resource from file
     * 
     * @param string $filePath
     * @return resource|false
     */
    public static function createFromFile($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                return imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($filePath);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                return $image;
            case IMAGETYPE_GIF:
                return imagecreatefromgif($filePath);
            case IMAGETYPE_WEBP:
                return imagecreatefromwebp($filePath);
            default:
                return false;
        }
    }

    /**
     * Save image resource to file
     * 
     * @param resource $image
     * @param string $filePath
     * @param int $quality 0-100
     * @return bool
     */
    public static function saveToFile($image, $filePath, $quality = 85)
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $filePath, $quality);
            case 'png':
                $quality = (int)round(9 - ($quality / 100 * 9)); // Convert to PNG compression
                return imagepng($image, $filePath, $quality);
            case 'gif':
                return imagegif($image, $filePath);
            case 'webp':
                return imagewebp($image, $filePath, $quality);
            default:
                return imagejpeg($image, $filePath, $quality);
        }
    }

    /**
     * Resize image
     * 
     * @param string $sourcePath Source image path
     * @param string $destPath Destination image path
     * @param int $width New width
     * @param int $height New height (0 for auto)
     * @param bool $crop Crop image to fit exact dimensions
     * @param int $quality Image quality (0-100)
     * @return bool
     */
    public static function resize($sourcePath, $destPath, $width, $height = 0, $crop = false, $quality = 85)
    {
        $sourceImage = self::createFromFile($sourcePath);
        if (!$sourceImage) return false;

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // Auto calculate height
        if ($height == 0) {
            $height = (int)round(($width / $sourceWidth) * $sourceHeight);
        }

        if ($crop) {
            // Crop and resize
            $ratio = max($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int)round($sourceWidth * $ratio);
            $newHeight = (int)round($sourceHeight * $ratio);

            $tempImage = imagecreatetruecolor($newWidth, $newHeight);
            self::preserveTransparency($tempImage, $sourceImage);
            imagecopyresampled($tempImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            $destImage = imagecreatetruecolor($width, $height);
            self::preserveTransparency($destImage, $sourceImage);

            $x = (int)round(($newWidth - $width) / 2);
            $y = (int)round(($newHeight - $height) / 2);

            imagecopy($destImage, $tempImage, 0, 0, $x, $y, $width, $height);
            imagedestroy($tempImage);
        } else {
            // Simple resize (maintain aspect ratio)
            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = (int)round($sourceWidth * $ratio);
            $newHeight = (int)round($sourceHeight * $ratio);

            $destImage = imagecreatetruecolor($width, $height);
            self::preserveTransparency($destImage, $sourceImage);

            // Fill with transparent/white background
            $bgColor = imagecolorallocate($destImage, 255, 255, 255);
            imagefill($destImage, 0, 0, $bgColor);

            // Center the image
            $x = (int)round(($width - $newWidth) / 2);
            $y = (int)round(($height - $newHeight) / 2);

            imagecopyresampled($destImage, $sourceImage, $x, $y, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        }

        $result = self::saveToFile($destImage, $destPath, $quality);

        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $result;
    }

    /**
     * Create thumbnail
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param int $size Thumbnail size (square)
     * @param int $quality
     * @return bool
     */
    public static function thumbnail($sourcePath, $destPath, $size = 150, $quality = 80)
    {
        return self::resize($sourcePath, $destPath, $size, $size, true, $quality);
    }

    /**
     * Create multiple thumbnails at once
     * 
     * @param string $sourcePath
     * @param string $destDir Destination directory
     * @param array $sizes Array of sizes ['small' => 150, 'medium' => 400, 'large' => 800]
     * @return array Created file paths
     */
    public static function createThumbnails($sourcePath, $destDir, $sizes = [])
    {
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $created = [];

        foreach ($sizes as $prefix => $size) {
            $destPath = $destDir . '/' . $filename . '_' . $prefix . '.' . $extension;
            if (self::thumbnail($sourcePath, $destPath, $size)) {
                $created[$prefix] = $destPath;
            }
        }

        return $created;
    }

    /**
     * Add watermark to image
     * 
     * @param string $sourcePath Source image
     * @param string $watermarkPath Watermark image (PNG with transparency recommended)
     * @param string $destPath Destination (null to overwrite source)
     * @param string $position top-left, top-right, bottom-left, bottom-right, center
     * @param int $opacity 0-100
     * @return bool
     */
    public static function watermark($sourcePath, $watermarkPath, $destPath = null, $position = 'bottom-right', $opacity = 50)
    {
        $destPath = $destPath ?? $sourcePath;

        $sourceImage = self::createFromFile($sourcePath);
        $watermarkImage = self::createFromFile($watermarkPath);

        if (!$sourceImage || !$watermarkImage) return false;

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $watermarkWidth = imagesx($watermarkImage);
        $watermarkHeight = imagesy($watermarkImage);

        // Calculate position
        $padding = 20;
        switch ($position) {
            case 'top-left':
                $x = $padding;
                $y = $padding;
                break;
            case 'top-right':
                $x = $sourceWidth - $watermarkWidth - $padding;
                $y = $padding;
                break;
            case 'bottom-left':
                $x = $padding;
                $y = $sourceHeight - $watermarkHeight - $padding;
                break;
            case 'center':
                $x = (int)round(($sourceWidth - $watermarkWidth) / 2);
                $y = (int)round(($sourceHeight - $watermarkHeight) / 2);
                break;
            case 'bottom-right':
            default:
                $x = $sourceWidth - $watermarkWidth - $padding;
                $y = $sourceHeight - $watermarkHeight - $padding;
                break;
        }

        // Apply watermark with opacity
        imagecopymerge($sourceImage, $watermarkImage, $x, $y, 0, 0, $watermarkWidth, $watermarkHeight, $opacity);

        $result = self::saveToFile($sourceImage, $destPath);

        imagedestroy($sourceImage);
        imagedestroy($watermarkImage);

        return $result;
    }

    /**
     * Convert image format
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param string $format jpg, png, gif, webp
     * @param int $quality
     * @return bool
     */
    public static function convert($sourcePath, $destPath, $format = 'jpg', $quality = 85)
    {
        $image = self::createFromFile($sourcePath);
        if (!$image) return false;

        // Change extension
        $destPath = pathinfo($destPath, PATHINFO_DIRNAME) . '/' . pathinfo($destPath, PATHINFO_FILENAME) . '.' . $format;

        $result = self::saveToFile($image, $destPath, $quality);
        imagedestroy($image);

        return $result;
    }

    /**
     * Rotate image
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param float $angle Rotation angle
     * @param int $bgColor Background color (hex without #)
     * @return bool
     */
    public static function rotate($sourcePath, $destPath = null, $angle = 90, $bgColor = 'FFFFFF')
    {
        $destPath = $destPath ?? $sourcePath;

        $image = self::createFromFile($sourcePath);
        if (!$image) return false;

        // Parse background color
        $r = hexdec(substr($bgColor, 0, 2));
        $g = hexdec(substr($bgColor, 2, 2));
        $b = hexdec(substr($bgColor, 4, 2));
        $bg = imagecolorallocate($image, $r, $g, $b);

        $rotated = imagerotate($image, -$angle, $bg); // Negative for clockwise

        $result = self::saveToFile($rotated, $destPath);

        imagedestroy($image);
        imagedestroy($rotated);

        return $result;
    }

    /**
     * Flip image
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param string $mode horizontal, vertical, both
     * @return bool
     */
    public static function flip($sourcePath, $destPath = null, $mode = 'horizontal')
    {
        $destPath = $destPath ?? $sourcePath;

        $image = self::createFromFile($sourcePath);
        if (!$image) return false;

        $width = imagesx($image);
        $height = imagesy($image);

        $flipped = imagecreatetruecolor($width, $height);
        self::preserveTransparency($flipped, $image);

        switch ($mode) {
            case 'vertical':
                imagecopyresampled($flipped, $image, 0, 0, 0, $height - 1, $width, $height, $width, -$height);
                break;
            case 'both':
                imagecopyresampled($flipped, $image, 0, 0, $width - 1, $height - 1, $width, $height, -$width, -$height);
                break;
            case 'horizontal':
            default:
                imagecopyresampled($flipped, $image, 0, 0, $width - 1, 0, $width, $height, -$width, $height);
                break;
        }

        $result = self::saveToFile($flipped, $destPath);

        imagedestroy($image);
        imagedestroy($flipped);

        return $result;
    }

    /**
     * Compress image
     * 
     * @param string $sourcePath
     * @param string $destPath
     * @param int $quality 0-100
     * @return bool
     */
    public static function compress($sourcePath, $destPath = null, $quality = 60)
    {
        $destPath = $destPath ?? $sourcePath;
        $image = self::createFromFile($sourcePath);
        if (!$image) return false;

        $result = self::saveToFile($image, $destPath, $quality);
        imagedestroy($image);

        return $result;
    }

    /**
     * Get image info
     * 
     * @param string $filePath
     * @return array|null
     */
    public static function getInfo($filePath)
    {
        if (!file_exists($filePath)) return null;

        $imageInfo = getimagesize($filePath);
        if (!$imageInfo) return null;

        $fileSize = filesize($filePath);

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
            'type' => self::$supportedTypes[$imageInfo[2]] ?? 'unknown',
            'mime' => $imageInfo['mime'] ?? '',
            'size' => $fileSize,
            'size_formatted' => Helper::formatBytes($fileSize),
            'ratio' => $imageInfo[1] > 0 ? round($imageInfo[0] / $imageInfo[1], 2) : 0,
            'bits' => $imageInfo['bits'] ?? 8,
            'channels' => $imageInfo['channels'] ?? 3,
        ];
    }

    /**
     * Check if file is a valid image
     * 
     * @param string $filePath
     * @return bool
     */
    public static function isValidImage($filePath)
    {
        if (!file_exists($filePath)) return false;

        $imageInfo = @getimagesize($filePath);
        return $imageInfo !== false && isset(self::$supportedTypes[$imageInfo[2]]);
    }

    /**
     * Get dominant color of image
     * 
     * @param string $filePath
     * @return string Hex color
     */
    public static function getDominantColor($filePath)
    {
        $image = self::createFromFile($filePath);
        if (!$image) return '#000000';

        // Resize to 1x1 to get average color
        $resized = imagecreatetruecolor(1, 1);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));

        $rgb = imagecolorat($resized, 0, 0);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        imagedestroy($image);
        imagedestroy($resized);

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Generate base64 data URI
     * 
     * @param string $filePath
     * @return string|null
     */
    public static function toBase64($filePath)
    {
        if (!file_exists($filePath)) return null;

        $mimeType = mime_content_type($filePath);
        $base64 = base64_encode(file_get_contents($filePath));

        return "data:{$mimeType};base64,{$base64}";
    }

    /**
     * Create a placeholder image
     * 
     * @param int $width
     * @param int $height
     * @param string $text
     * @param string $bgColor Hex background color
     * @param string $textColor Hex text color
     * @return resource
     */
    public static function createPlaceholder($width, $height, $text = '', $bgColor = 'CCCCCC', $textColor = '666666')
    {
        $image = imagecreatetruecolor($width, $height);

        // Parse colors
        $bgR = hexdec(substr($bgColor, 0, 2));
        $bgG = hexdec(substr($bgColor, 2, 2));
        $bgB = hexdec(substr($bgColor, 4, 2));
        $bg = imagecolorallocate($image, $bgR, $bgG, $bgB);

        $textR = hexdec(substr($textColor, 0, 2));
        $textG = hexdec(substr($textColor, 2, 2));
        $textB = hexdec(substr($textColor, 4, 2));
        $textColorAlloc = imagecolorallocate($image, $textR, $textG, $textB);

        // Fill background
        imagefill($image, 0, 0, $bg);

        // Add text
        if ($text) {
            $fontSize = min($width, $height) / 10;
            $x = (int)round(($width - strlen($text) * $fontSize * 0.6) / 2);
            $y = (int)round(($height - $fontSize) / 2);
            imagestring($image, 5, max(0, $x), max(0, $y), $text, $textColorAlloc);
        }

        return $image;
    }

    /**
     * Preserve transparency when creating new image
     * 
     * @param resource $newImage
     * @param resource $sourceImage
     */
    private static function preserveTransparency($newImage, $sourceImage)
    {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);

        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefill($newImage, 0, 0, $transparent);
    }
}