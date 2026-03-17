<?php
header("Content-Type: application/json; charset=utf-8");

$usersFile = __DIR__ . "/data/users.json";

if (!file_exists($usersFile)) {
    echo json_encode([]);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true);

$eredmeny = [];

foreach ($users as $user => $stat) {
    $elo = $stat["elo"] ?? 1200;
    $win = $stat["win"] ?? 0;
    $lose = $stat["lose"] ?? 0;

    $ossz = $win + $lose;
    $winrate = ($ossz > 0) ? round(($win / $ossz) * 100, 2) : 0;

    $eredmeny[] = [
        "user" => $user,
        "elo" => $elo,
        "win" => $win,
        "lose" => $lose,
        "winrate" => $winrate
    ];
}

usort($eredmeny, function($a, $b) {
    return $b["elo"] - $a["elo"];
});

echo json_encode([
    "status" => "ok",
    "leaderboard" => $eredmeny
]);
