<?php
header("Content-Type: application/json; charset=utf-8");

$usersFile = __DIR__ . "/data/users.json";

$user = strtolower(trim($_POST["user"]));
$pass = $_POST["pass"] ?? "";

if ($user === "" || $pass === "") {
    echo json_encode(["status" => "error", "message" => "missing"]);
    exit;
}

if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([], JSON_PRETTY_PRINT));
}

$users = json_decode(file_get_contents($usersFile), true);

if (isset($users[$user])) {
    echo json_encode(["status" => "error", "message" => "exists"]);
    exit;
}

$hash = password_hash($pass, PASSWORD_DEFAULT);

function giveBadge(&$userData, $badge) {
    if (!isset($userData["badges"])) {
        $userData["badges"] = [];
    }
    if (!in_array($badge, $userData["badges"])) {
        $userData["badges"][] = $badge;
    }
}

$users[$user] = [
    "password" => $hash,
    "elo" => 1200,
    "win" => 0,
    "lose" => 0,
    "pfp" => "default_pfp.png",
    "header_bg" => null,
    "background_style" => "default",
    "badges" => [],
    "matches" => [],
    "registered_at" => date("Y-m-d")
];

if (count($users) <= 100) {
    giveBadge($users[$user], "top100.png");
}

if (strtotime($users[$user]["registered_at"]) < strtotime("2026-04-01")) {
    giveBadge($users[$user], "beta_user.png");
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));

echo json_encode(["status" => "ok"]);
