<?php
header('Content-Type: application/json; charset=utf-8');

$user = $_POST['user'] ?? null;
$style = $_POST['style'] ?? null;

$allowed = ["default", "neon.gif", "fire.gif", "placeholder.gif"];

if (!$user || !in_array($style, $allowed)) {
    echo json_encode(["status" => "error", "message" => "Invalid data"]);
    exit;
}

$jsonPath = __DIR__ . "/data/users.json";
$data = json_decode(file_get_contents($jsonPath), true);

$data[$user]["background_style"] = $style;

file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT));

echo json_encode(["status" => "ok", "background_style" => $style]);
