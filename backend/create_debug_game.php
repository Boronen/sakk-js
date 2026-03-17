<?php
header("Content-Type: application/json");

$gamesFile = __DIR__ . "/data/games.json";

$games = json_decode(file_get_contents($gamesFile), true);

$gameId = substr(md5(time() . rand()), 0, 8);

$games[$gameId] = [
    "white" => "beta1",
    "black" => "pityu",
    "turn" => "white",
    "status" => "ongoing",
    "winner" => null,
    "board" => [
        ["r","n","b","q","k","b","n","r"],
        ["p","p","p","p","p","p","p","p"],
        ["","","","","","","",""],
        ["","","","","","","",""],
        ["","","","","","","",""],
        ["","","","","","","",""],
        ["P","P","P","P","P","P","P","P"],
        ["R","N","B","Q","K","B","N","R"]
    ],
    "moves" => []
];

file_put_contents($gamesFile, json_encode($games, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "ok",
    "game" => $gameId
]);
