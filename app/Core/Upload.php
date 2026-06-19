<?php
class Upload {
    private static array $allowedMimes = [
        'image/jpeg'       => 'jpg',
        'image/png'        => 'png',
        'image/gif'        => 'gif',
        'image/webp'       => 'webp',
        'video/mp4'        => 'mp4',
        'video/quicktime'  => 'mov',
        'application/pdf'  => 'pdf',
        'application/zip'  => 'zip',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'   => 'docx',
    ];

    private static int $maxSizeMb = 50;

    public static function save(array $file, int $requestId): array {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload error code: ' . $file['error']);
        }

        $maxBytes = self::$maxSizeMb * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            throw new RuntimeException('File terlalu besar. Maksimal ' . self::$maxSizeMb . 'MB.');
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!isset(self::$allowedMimes[$mime])) {
            throw new RuntimeException('Tipe file tidak diizinkan: ' . $mime);
        }

        $dir = APP_ROOT . '/storage/uploads/' . $requestId . '/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext      = self::$allowedMimes[$mime];
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = date('Ymd_His') . '_' . $safeName . '.' . $ext;
        $dest     = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Gagal menyimpan file.');
        }

        return [
            'filename'  => $filename,
            'filepath'  => 'storage/uploads/' . $requestId . '/' . $filename,
            'filetype'  => $mime,
            'filesize'  => $file['size'],
        ];
    }

    public static function delete(string $filepath): void {
        $full = APP_ROOT . '/' . ltrim($filepath, '/');
        if (file_exists($full) && is_file($full)) {
            unlink($full);
        }
    }

    public static function getMimeLabel(string $mime): string {
        $labels = [
            'image/jpeg'      => 'JPG',
            'image/png'       => 'PNG',
            'image/webp'      => 'WebP',
            'video/mp4'       => 'MP4',
            'video/quicktime' => 'MOV',
            'application/pdf' => 'PDF',
            'application/zip' => 'ZIP',
        ];
        return $labels[$mime] ?? strtoupper(pathinfo($mime, PATHINFO_EXTENSION));
    }
}
