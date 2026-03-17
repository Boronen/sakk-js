<?php
header("Content-Type: application/json; charset=utf-8");

$queueFile = __DIR__ . "/data/queue.json";
$gamesFile = __DIR__ . "/data/games.json";
$usersFile = __DIR__ . "/data/users.json";

$user = trim(strtolower($_POST["user"] ?? ""));

if ($user === "") {
    echo json_encode(["status" => "error", "message" => "missing_user"]);
    exit;
}

if (!file_exists($usersFile)) {
    echo json_encode(["status" => "error", "message" => "no_users_file"]);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);
if (!isset($users[$user])) {
    echo json_encode(["status" => "error", "message" => "user_not_found"]);
    exit;
}

$elo = intval($users[$user]["elo"] ?? 1200);

if (file_exists($gamesFile)) {
    $games = json_decode(file_get_contents($gamesFile), true);

    foreach ($games as $gid => $g) {
        if (
            ($g["white"] === $user || $g["black"] === $user)
            && $g["status"] === "ongoing"
        ) {
            echo json_encode([
                "status" => "already_in_game",
                "game" => $gid
            ]);
            exit;
        }
    }
}


if (!file_exists($queueFile)) {
    file_put_contents($queueFile, json_encode([], JSON_PRETTY_PRINT));
}

$queue = json_decode(file_get_contents($queueFile), true);

if (!isset($queue[$user])) {
    $queue[$user] = [
        "elo" => $elo,
        "joined_at" => time()
    ];
    file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
}

echo json_encode(["status" => "queued"]);
