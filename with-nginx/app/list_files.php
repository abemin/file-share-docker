<?php
session_start();
if (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /login');
    exit;
}

$dir = '/mnt/share';
$files = scandir($dir);
$file_data = [];

foreach ($files as $file) {
    if ($file !== '.' && $file !== '..' && $file !== '.htaccess' && strpos($file, '.') !== 0) {
        $path = $dir . '/' . $file;
        $size = round(filesize($path) / 1024 / 1024, 2) . ' MB';
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' :
                ($ext === 'pdf' ? 'pdf' :
                (in_array($ext, ['mp4', 'avi', 'mov']) ? 'video' : 'file'));
        $file_data[] = [
            'name' => $file,
            'type' => $type,
            'size' => $size,
            'path' => '/share/' . rawurlencode($file)
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($file_data);
?>
