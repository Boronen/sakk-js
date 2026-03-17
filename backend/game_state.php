<?php
error_reporting(0);
ini_set("display_errors", 0);

header("Content-Type: application/json; charset=utf-8");

$gamesFile = __DIR__ . "/data/games.json";

if (!isset($_GET["game"])) {
    echo json_encode(["status" => "error", "msg" => "no_game_id"]);
    exit;
}

$gameId = $_GET["game"];

if (!file_exists($gamesFile)) {
    echo json_encode(["status" => "error", "msg" => "no_games_file"]);
    exit;
}

$games = json_decode(file_get_contents($gamesFile), true);

if (!isset($games[$gameId])) {
    echo json_encode(["status" => "error", "msg" => "game_not_found"]);
    exit;
}

echo json_encode([
    "status" => "ok",
    "game" => $gameId,
    "white" => $games[$gameId]["white"],
    "black" => $games[$gameId]["black"],
    "turn" => $games[$gameId]["turn"],
    "board" => $games[$gameId]["board"],
    "moves" => $games[$gameId]["moves"],
    "status_game" => $games[$gameId]["status"]
]);
