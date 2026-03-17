<?php

$dir = __DIR__ . "/../public/assets/header_backgrounds/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

header('Content-Type: application/json; charset=utf-8');

$user = $_POST['user'] ?? null;
$file = $_FILES['file'] ?? null;

if (!$user || !$file) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ["png", "jpg", "jpeg", "gif"];

if (!in_array($ext, $allowed)) {
    echo json_encode(["status" => "error", "message" => "Invalid file type"]);
    exit;
}

$filename = $user . "_header.png";
$target = __DIR__ . "/../public/assets/header_backgrounds/" . $filename;

move_uploaded_file($file["tmp_name"], $target);

$jsonPath = __DIR__ . "/data/users.json";
$data = json_decode(file_get_contents($jsonPath), true);

$data[$user]["header_bg"] = $filename;

file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT));

echo json_encode(["status" => "ok", "header_bg" => $filename]);
