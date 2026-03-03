<?php

declare(strict_types=1);

namespace App\Core;

class File
{
    private static array $config = [
        'upload_path' => __DIR__ . '/../../storage/uploads',
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'],
        'max_file_size' => 10485760, // 10MB
        'image_quality' => 85,
    ];

    public static function configure(array $config): void
    {
        self::$config = array_merge(self::$config, $config);
    }

    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function missing(string $path): bool
    {
        return !self::exists($path);
    }

    public static function get(string $path, bool $lock = false): string|false
    {
        if (self::missing($path)) {
            return false;
        }

        $contents = file_get_contents($path, $lock ? LOCK_EX : 0);
        
        if ($contents === false) {
            return false;
        }

        return $contents;
    }

    public static function put(string $path, string $contents, bool $lock = false): bool
    {
        $directory = dirname($path);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0) !== false;
    }

    public static function append(string $path, string $data): bool
    {
        if (self::missing($path)) {
            return self::put($path, $data);
        }

        return file_put_contents($path, $data, FILE_APPEND | LOCK_EX) !== false;
    }

    public static function prepend(string $path, string $data): bool
    {
        if (self::missing($path)) {
            return self::put($path, $data);
        }

        $contents = self::get($path);
        return self::put($path, $data . $contents);
    }

    public static function delete(string $path): bool
    {
        if (self::missing($path)) {
            return true;
        }

        return unlink($path);
    }

    public static function copy(string $from, string $to): bool
    {
        if (self::missing($from)) {
            return false;
        }

        $directory = dirname($to);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return copy($from, $to);
    }

    public static function move(string $from, string $to): bool
    {
        if (self::missing($from)) {
            return false;
        }

        $directory = dirname($to);
        
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return rename($from, $to);
    }

    public static function size(string $path): int
    {
        if (self::missing($path)) {
            return 0;
        }

        return filesize($path);
    }

    public static function sizeHuman(string $path): string
    {
        $bytes = self::size($path);
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public static function lastModified(string $path): int
    {
        if (self::missing($path)) {
            return 0;
        }

        return filemtime($path);
    }

    public static function extension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    public static function basename(string $path): string
    {
        return basename($path);
    }

    public static function dirname(string $path): string
    {
        return dirname($path);
    }

    public static function name(string $path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function mimeType(string $path): string|false
    {
        if (self::missing($path)) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mimeType;
    }

    public static function isImage(string $path): bool
    {
        $mimeType = self::mimeType($path);
        
        if (!$mimeType) {
            return false;
        }

        return str_starts_with($mimeType, 'image/');
    }

    public static function isFile(string $path): bool
    {
        return is_file($path);
    }

    public static function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    public static function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public static function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public static function makeDirectory(string $path, int $mode = 0755, bool $recursive = true): bool
    {
        if (self::isDirectory($path)) {
            return true;
        }

        return mkdir($path, $mode, $recursive);
    }

    public static function deleteDirectory(string $path, bool $preserve = false): bool
    {
        if (!self::isDirectory($path)) {
            return true;
        }

        $items = scandir($path);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            
            if (self::isDirectory($itemPath)) {
                self::deleteDirectory($itemPath);
            } else {
                self::delete($itemPath);
            }
        }

        if (!$preserve) {
            return rmdir($path);
        }

        return true;
    }

    public static function cleanDirectory(string $path): bool
    {
        return self::deleteDirectory($path, true);
    }

    public static function files(string $directory, bool $hidden = false): array
    {
        if (!self::isDirectory($directory)) {
            return [];
        }

        $files = [];
        
        $items = scandir($directory);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!$hidden && str_starts_with($item, '.')) {
                continue;
            }

            $itemPath = $directory . DIRECTORY_SEPARATOR . $item;
            
            if (self::isFile($itemPath)) {
                $files[] = $itemPath;
            }
        }

        return $files;
    }

    public static function allFiles(string $directory, bool $hidden = false): array
    {
        if (!self::isDirectory($directory)) {
            return [];
        }

        $files = [];
        
        $items = scandir($directory);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!$hidden && str_starts_with($item, '.')) {
                continue;
            }

            $itemPath = $directory . DIRECTORY_SEPARATOR . $item;
            
            if (self::isFile($itemPath)) {
                $files[] = $itemPath;
            } elseif (self::isDirectory($itemPath)) {
                $files = array_merge($files, self::allFiles($itemPath, $hidden));
            }
        }

        return $files;
    }

    public static function directories(string $directory): array
    {
        if (!self::isDirectory($directory)) {
            return [];
        }

        $directories = [];
        
        $items = scandir($directory);
        
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $directory . DIRECTORY_SEPARATOR . $item;
            
            if (self::isDirectory($itemPath)) {
                $directories[] = $itemPath;
            }
        }

        return $directories;
    }

    public static function upload(array $file, ?string $directory = null, ?string $name = null): array|false
    {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        $uploadPath = $directory ?? self::$config['upload_path'];
        $extension = self::extension($file['name']);
        
        if (!in_array($extension, self::$config['allowed_extensions'])) {
            return false;
        }

        if ($file['size'] > self::$config['max_file_size']) {
            return false;
        }

        if (!self::isDirectory($uploadPath)) {
            self::makeDirectory($uploadPath);
        }

        $filename = $name ?? uniqid() . '.' . $extension;
        $destination = $uploadPath . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'filename' => $filename,
                'path' => $destination,
                'size' => $file['size'],
                'mime_type' => $file['type'],
                'original_name' => $file['name']
            ];
        }

        return false;
    }

    public static function uploadImage(array $file, ?string $directory = null, ?int $maxWidth = null, ?int $maxHeight = null): array|false
    {
        $upload = self::upload($file, $directory);
        
        if (!$upload) {
            return false;
        }

        if (!self::isImage($upload['path'])) {
            self::delete($upload['path']);
            return false;
        }

        if ($maxWidth || $maxHeight) {
            self::resizeImage($upload['path'], $maxWidth, $maxHeight);
        }

        return $upload;
    }

    public static function resizeImage(string $path, ?int $maxWidth = null, ?int $maxHeight = null): bool
    {
        if (!self::isImage($path) || !extension_loaded('gd')) {
            return false;
        }

        $imageInfo = getimagesize($path);
        
        if (!$imageInfo) {
            return false;
        }

        [$width, $height] = $imageInfo;
        $mimeType = $imageInfo['mime'];

        $newWidth = $width;
        $newHeight = $height;

        if ($maxWidth && $width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($height * ($maxWidth / $width));
        }

        if ($maxHeight && $newHeight > $maxHeight) {
            $newHeight = $maxHeight;
            $newWidth = (int) ($newWidth * ($maxHeight / $newHeight));
        }

        if ($newWidth === $width && $newHeight === $height) {
            return true;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            default => false
        };

        if (!$image) {
            return false;
        }

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        $result = match ($mimeType) {
            'image/jpeg' => imagejpeg($newImage, $path, self::$config['image_quality']),
            'image/png' => imagepng($newImage, $path),
            'image/gif' => imagegif($newImage, $path),
            default => false
        };

        imagedestroy($image);
        imagedestroy($newImage);

        return $result;
    }

    public static function download(string $path, ?string $name = null): void
    {
        if (self::missing($path)) {
            header('HTTP/1.0 404 Not Found');
            exit;
        }

        $filename = $name ?? self::basename($path);
        $mimeType = self::mimeType($path) ?? 'application/octet-stream';
        $size = self::size($path);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $size);
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');

        readfile($path);
        exit;
    }

    public static function hash(string $path, string $algorithm = 'md5'): string|false
    {
        if (self::missing($path)) {
            return false;
        }

        return hash_file($algorithm, $path);
    }

    public static function chmod(string $path, int $mode): bool
    {
        return chmod($path, $mode);
    }

    public static function chown(string $path, string $user): bool
    {
        return chown($path, $user);
    }

    public static function chgrp(string $path, string $group): bool
    {
        return chgrp($path, $group);
    }
}
