<?php
header("Content-Type: application/json");

$gameId = $_GET["game"] ?? "";
$winner = $_GET["winner"] ?? "";

if ($gameId === "" || $winner === "") {
    echo json_encode(["status" => "error", "msg" => "missing_parameters"]);
    exit;
}

$ch = curl_init("http://localhost/sakk-js/backend/finish_game.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    "game" => $gameId,
    "winner" => $winner
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

echo json_encode([
    "status" => "ok",
    "finish_response" => json_decode($result, true)
]);
