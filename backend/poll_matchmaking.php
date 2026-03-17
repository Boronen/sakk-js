<?php
header("Content-Type: application/json; charset=utf-8");

$queueFile = __DIR__ . "/data/queue.json";
$gamesFile = __DIR__ . "/data/games.json";

$user = trim(strtolower($_POST["user"] ?? ""));

if ($user === "") {
    echo json_encode(["status" => "error", "message" => "missing_user"]);
    exit;
}

if (file_exists($gamesFile)) {
    $games = json_decode(file_get_contents($gamesFile), true);

    foreach ($games as $gid => $g) {
        if (
            ($g["white"] === $user || $g["black"] === $user)
            && $g["status"] === "ongoing"
        ) {
            echo json_encode([
                "status" => "match_found",
                "game" => $gid
            ]);
            exit;
        }
    }
}


if (!file_exists($queueFile)) {
    echo json_encode(["status" => "waiting"]);
    exit;
}

$queue = json_decode(file_get_contents($queueFile), true);

if (!isset($queue[$user])) {
    echo json_encode(["status" => "waiting"]);
    exit;
}

$myElo = intval($queue[$user]["elo"]);
$myJoined = intval($queue[$user]["joined_at"]);
$now = time();
$elapsedSec = max(0, $now - $myJoined);

$baseRange = 100;
$growPerSec = 50 / 60;
$maxRange = 500;

$currentRange = min($maxRange, intval($baseRange + $elapsedSec * $growPerSec));

$bestOpponent = null;
$bestDiff = null;

foreach ($queue as $otherUser => $adat) {
    if ($otherUser === $user) continue;

    $elo = intval($adat["elo"]);
    $diff = abs($elo - $myElo);

    if ($diff <= $currentRange) {
        if ($bestOpponent === null || $diff < $bestDiff) {
            $bestOpponent = $otherUser;
            $bestDiff = $diff;
        }
    }
}

if ($bestOpponent === null) {
    echo json_encode(["status" => "waiting"]);
    exit;
}

$gameId = substr(md5(uniqid()), 0, 8);

$games = file_exists($gamesFile)
    ? json_decode(file_get_contents($gamesFile), true)
    : [];

$alapTabla = [
    ["r", "n", "b", "q", "k", "b", "n", "r"],
    ["p", "p", "p", "p", "p", "p", "p", "p"],
    ["", "", "", "", "", "", "", ""],
    ["", "", "", "", "", "", "", ""],
    ["", "", "", "", "", "", "", ""],
    ["", "", "", "", "", "", "", ""],
    ["P", "P", "P", "P", "P", "P", "P", "P"],
    ["R", "N", "B", "Q", "K", "B", "N", "R"]
];

$oppJoined = intval($queue[$bestOpponent]["joined_at"]);
if ($myJoined <= $oppJoined) {
    $white = $user;
    $black = $bestOpponent;
} else {
    $white = $bestOpponent;
    $black = $user;
}

$games[$gameId] = [
    "white" => $white,
    "black" => $black,
    "turn" => "white",
    "status" => "ongoing",
    "board" => $alapTabla,
    "moves" => []
];

unset($queue[$user]);
unset($queue[$bestOpponent]);

file_put_contents($queueFile, json_encode($queue, JSON_PRETTY_PRINT));
file_put_contents($gamesFile, json_encode($games, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "match_found",
    "game" => $gameId
]);
exit;
