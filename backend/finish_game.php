<?php
header("Content-Type: application/json; charset=utf-8");

$usersFile = __DIR__ . "/data/users.json";
$gamesFile = __DIR__ . "/data/games.json";
file_put_contents(__DIR__ . "/finish_log.txt",
    date("Y-m-d H:i:s") . " POST: " . json_encode($_POST) . PHP_EOL,
    FILE_APPEND
);

$gameId = $_POST["game"] ?? "";
$winner = $_POST["winner"] ?? "";

if ($gameId === "" || $winner === "") {
    echo json_encode(["status" => "error", "message" => "missing_parameters"]);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);
$games = json_decode(file_get_contents($gamesFile), true);

if (!isset($games[$gameId])) {
    echo json_encode(["status" => "error", "message" => "game_not_found"]);
    exit;
}

$game = $games[$gameId];
$white = $game["white"];
$black = $game["black"];

$loser = ($winner === $white) ? $black : $white;

foreach ([$winner, $loser] as $user) {
    if (!isset($users[$user]["win"]))  $users[$user]["win"] = 0;
    if (!isset($users[$user]["lose"])) $users[$user]["lose"] = 0;
    if (!isset($users[$user]["elo"]))  $users[$user]["elo"] = 1200;
    if (!isset($users[$user]["matches"])) $users[$user]["matches"] = [];
    if (!isset($users[$user]["badges"])) $users[$user]["badges"] = [];
}

function giveBadge(&$userData, $badge) {
    if (!in_array($badge, $userData["badges"])) {
        $userData["badges"][] = $badge;
    }
}

$users[$winner]["win"] += 1;
$users[$loser]["lose"] += 1;

$users[$winner]["elo"] += 10;
$users[$loser]["elo"] -= 10;

if ($users[$winner]["win"] == 1) giveBadge($users[$winner], "first_win.gif");
if ($users[$winner]["win"] == 10) giveBadge($users[$winner], "win_10.png");
if ($users[$loser]["lose"] == 10) giveBadge($users[$loser], "lose_10.png");
if ($users[$winner]["elo"] >= 1300) giveBadge($users[$winner], "elo_100.png");

$users[$winner]["matches"][] = $gameId;
$users[$loser]["matches"][] = $gameId;

$games[$gameId]["status"] = "ended";
$games[$gameId]["winner"] = $winner;

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
file_put_contents($gamesFile, json_encode($games, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "ok",
    "winner" => $winner,
    "loser" => $loser,
    "game" => $gameId
]);
