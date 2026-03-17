<?php
ini_set('session.cookie_path', '/');
session_start();

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "boronen") {
    echo json_encode([
        "status" => "error",
        "message" => "forbidden"
    ]);
    exit;
}

if (!isset($_POST["name"]) || !isset($_FILES["file"])) {
    echo json_encode([
        "status" => "error",
        "message" => "missing_parameters"
    ]);
    exit;
}

$name = basename(trim($_POST["name"]));
$rarity = $_POST["rarity"] ?? "common";
$description = trim($_POST["description"] ?? "");
$file = $_FILES["file"];

if ($name === "") {
    echo json_encode([
        "status" => "error",
        "message" => "empty_name"
    ]);
    exit;
}

$allowed = ["png", "jpg", "jpeg", "gif"];
$ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    echo json_encode([
        "status" => "error",
        "message" => "invalid_file_type"
    ]);
    exit;
}

if ($file["error"] !== UPLOAD_ERR_OK) {
    echo json_encode([
        "status" => "error",
        "message" => "upload_error"
    ]);
    exit;
}

if ($file["size"] > 5 * 1024 * 1024) {
    echo json_encode([
        "status" => "error",
        "message" => "file_too_large"
    ]);
    exit;
}

$targetDir = __DIR__ . "/../public/assets/badges/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$targetFile = $targetDir . $name;

if (file_exists($targetFile)) {
    echo json_encode([
        "status" => "error",
        "message" => "file_already_exists"
    ]);
    exit;
}

if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
    echo json_encode([
        "status" => "error",
        "message" => "upload_failed"
    ]);
    exit;
}

$metaFile = __DIR__ . "/data/badges.json";
$meta = file_exists($metaFile)
    ? json_decode(file_get_contents($metaFile), true)
    : [];

$meta[$name] = [
    "name" => pathinfo($name, PATHINFO_FILENAME),
    "rarity" => $rarity,
    "description" => $description
];

file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    "status" => "ok",
    "message" => "badge_uploaded"
]);
