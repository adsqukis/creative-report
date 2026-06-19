<?php
class FileController {
    public function upload(string $id): void {
        Auth::require();

        $db      = Database::getInstance();
        $rid     = (int)$id;
        $uid     = (int)Auth::id();
        $fileType = in_array($_POST['file_type'] ?? '', ['draft','revision','final'], true) ? $_POST['file_type'] : 'draft';
        $revNum  = (int)($_POST['revision_number'] ?? 0);
        $gdriveUrl = trim($_POST['gdrive_url'] ?? '');

        // Google Drive URL path (no file needed)
        if ($gdriveUrl !== '') {
            $sql = "INSERT INTO co_request_files (request_id, revision_number, file_type, gdrive_url, uploaded_by) VALUES (?, ?, ?, ?, ?)";
            $db->execute($sql, [$rid, $revNum, $fileType, $gdriveUrl, $uid]);
            header('Location: ' . APP_URL . '/requests/' . $rid . '#files');
            exit;
        }

        // Local file upload
        if (!empty($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $saved = Upload::save($_FILES['upload_file'], $rid);
                // 'filetype' (mime) kolom tidak ada di schema co_request_files — hapus dari INSERT
                $sql2  = "INSERT INTO co_request_files (request_id, revision_number, file_type, filename, filepath, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)";
                $db->execute($sql2, [
                    $rid, $revNum, $fileType,
                    $saved['filename'],
                    $saved['filepath'],
                    $uid,
                ]);
                $_SESSION['upload_success'] = 'File berhasil diupload.';
            } catch(Exception $e) {
                $_SESSION['upload_error'] = $e->getMessage();
            }
        } elseif (!empty($_FILES['upload_file']) && $_FILES['upload_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Ada file dipilih tapi gagal upload — tangkap error code PHP
            $phpErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File melebihi batas upload_max_filesize di php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'File melebihi batas MAX_FILE_SIZE di form.',
                UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian. Coba lagi.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan di server.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk. Periksa permission folder storage/.',
                UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
            ];
            $code = $_FILES['upload_file']['error'];
            $_SESSION['upload_error'] = $phpErrors[$code] ?? 'Upload gagal dengan kode error: ' . $code;
        }

        header('Location: ' . APP_URL . '/requests/' . $rid . '#files');
        exit;
    }

    public function download(string $fileId): void {
        Auth::require();
        $db   = Database::getInstance();
        $fid  = (int)$fileId;
        $sql  = "SELECT * FROM co_request_files WHERE id = ? LIMIT 1";
        $file = $db->row($sql, [$fid]);

        if (!$file) {
            http_response_code(404);
            echo 'File tidak ditemukan.';
            exit;
        }

        // Jika ini Google Drive link, redirect ke GDrive
        if (!empty($file['gdrive_url'])) {
            header('Location: ' . $file['gdrive_url']);
            exit;
        }

        if (empty($file['filepath'])) {
            http_response_code(404);
            echo 'File tidak memiliki path.';
            exit;
        }

        $path = APP_ROOT . '/' . ltrim($file['filepath'], '/');
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'File tidak ada di server. Mungkin sudah dihapus manual.';
            exit;
        }

        // Gunakan mime_content_type dari file fisik — kolom 'filetype' tidak ada di schema
        $mime = mime_content_type($path) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . basename($file['filename']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }

    public function delete(string $fileId): void {
        Auth::requireRole(['super_admin', 'creative_manager', 'designer', 'video_editor']);
        $db   = Database::getInstance();
        $fid  = (int)$fileId;
        $sql  = "SELECT request_id, filepath FROM co_request_files WHERE id = ? LIMIT 1";
        $file = $db->row($sql, [$fid]);

        if ($file) {
            $rid   = (int)$file['request_id'];
            $sql2  = "DELETE FROM co_request_files WHERE id = ?";
            $db->execute($sql2, [$fid]);
            if (!empty($file['filepath'])) {
                Upload::delete($file['filepath']);
            }
            header('Location: ' . APP_URL . '/requests/' . $rid . '#files');
            exit;
        }

        header('Location: ' . APP_URL . '/requests');
        exit;
    }
}