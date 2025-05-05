<?php

$targetDir = __DIR__ . '/uploads/';

$tempPath = $_FILES['file']['tmp_name'];
$fileName = basename($_FILES['file']['name']);
$destination = $targetDir . $fileName;

if (move_uploaded_file($tempPath, $destination)) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Upload failed.']);
}