<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$targetDir = "uploads/";
if (!file_exists($targetDir)) {
    mkdir($targetDir);
}


$uploaded = [];
$errors = [];

foreach ($_FILES['file']['name'] as $index => $name) {
    $tempPath = $_FILES['file']['tmp_name'][$index];
    $fileName = basename($name);
    $destination = $targetDir . $fileName;

    if (move_uploaded_file($tempPath, $destination)) {
        $uploaded[] = $fileName;
    } else {
        $errors[] = $fileName;
    }
}

if (empty($errors)) {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'files' => $uploaded]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Some files failed', 'failed_files' => $errors]);
}