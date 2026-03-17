<?php
ini_set('session.cookie_path', '/');
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=utf-8");

$usersFile = __DIR__ . "/data/users.json";

$user = $_POST["user"] ?? "";
$badge = $_POST["badge"] ?? "";

if ($user === "" || $badge === "") {
    echo json_encode(["status" => "error", "message" => "missing_parameters"]);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);

$adminOnlyBadges = [
    "huh.gif",
    "iaua.gif"
];

$puzzleBadge = "secret_election_badge.gif";

$isAdmin = isset($_SESSION["user"]) && $_SESSION["user"] === "boronen";

if (in_array($badge, $adminOnlyBadges) && !$isAdmin) {
    echo json_encode(["status" => "error", "message" => "forbidden"]);
    exit;
}

if ($badge === $puzzleBadge) {
} else {
    if (!$isAdmin) {
        echo json_encode(["status" => "error", "message" => "forbidden"]);
        exit;
    }
}

if (!isset($users[$user]["badges"])) {
    $users[$user]["badges"] = [];
}

if (!in_array($badge, $users[$user]["badges"])) {
    $users[$user]["badges"][] = $badge;
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(["status" => "ok"]);
