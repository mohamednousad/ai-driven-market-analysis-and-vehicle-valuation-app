<?php
final class FileUploader
{
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const DOC_TYPES = ['application/pdf' => 'pdf'];

    public static function saveImage(array $file, string $relativeDir, string $baseName): array
    {
        return self::save($file, $relativeDir, $baseName, self::IMAGE_TYPES, (int)Config::get('MAX_IMAGE_BYTES'));
    }

    public static function saveDocument(array $file, string $relativeDir, string $baseName): array
    {
        return self::save($file, $relativeDir, $baseName, self::DOC_TYPES, (int)Config::get('MAX_DOC_BYTES'));
    }

    private static function save(array $file, string $relativeDir, string $baseName, array $allowed, int $maxBytes): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'File upload failed. Please try again.'];
        }
        if ($file['size'] > $maxBytes) {
            return ['ok' => false, 'error' => 'File is too large. Maximum allowed is ' . round($maxBytes / 1048576) . 'MB.'];
        }
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            return ['ok' => false, 'error' => 'File type not allowed.'];
        }
        $ext = $allowed[$mime];
        $dir = Config::rootPath($relativeDir);
        if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
            return ['ok' => false, 'error' => 'Could not create the upload folder.'];
        }
        $relativePath = rtrim($relativeDir, '/') . '/' . $baseName . '.' . $ext;
        if (!move_uploaded_file($file['tmp_name'], Config::rootPath($relativePath))) {
            return ['ok' => false, 'error' => 'Could not save the uploaded file.'];
        }
        return ['ok' => true, 'path' => $relativePath, 'size' => (int)$file['size'], 'format' => $ext];
    }
}
