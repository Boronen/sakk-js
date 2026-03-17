<?php
header("Content-Type: application/json; charset=utf-8");

$gamesFile = __DIR__ . "/data/games.json";

$gameId  = $_POST["game"] ?? "";
$fromRow = intval($_POST["fromRow"] ?? -1);
$fromCol = intval($_POST["fromCol"] ?? -1);
$toRow   = intval($_POST["toRow"] ?? -1);
$toCol   = intval($_POST["toCol"] ?? -1);
$jatekos = $_POST["user"] ?? "";

if ($gameId === "" || $jatekos === "") {
    echo json_encode(["status" => "error", "msg" => "missing_parameters"]);
    exit;
}

$games = json_decode(file_get_contents($gamesFile), true);

if (!isset($games[$gameId])) {
    echo json_encode(["status" => "error", "msg" => "game_not_found"]);
    exit;
}

$game = $games[$gameId];

if ($game["status"] !== "ongoing") {
    echo json_encode(["status" => "error", "msg" => "game_finished"]);
    exit;
}

$szin = ($game["white"] === $jatekos) ? "white" : (($game["black"] === $jatekos) ? "black" : null);

if ($szin === null) {
    echo json_encode(["status" => "error", "msg" => "not_in_game"]);
    exit;
}

if ($game["turn"] !== $szin) {
    echo json_encode(["status" => "error", "msg" => "not_your_turn"]);
    exit;
}

$board = $game["board"];
$figura = $board[$fromRow][$fromCol] ?? "";
$celfigura = $board[$toRow][$toCol] ?? "";

if ($figura === "") {
    echo json_encode(["status" => "error", "msg" => "empty_square"]);
    exit;
}

if ($szin === "white" && !php_isWhitePiece($figura)) {
    echo json_encode(["status" => "error", "msg" => "not_your_piece"]);
    exit;
}
if ($szin === "black" && !php_isBlackPiece($figura)) {
    echo json_encode(["status" => "error", "msg" => "not_your_piece"]);
    exit;
}

if ($celfigura !== "" && php_sameColor($figura, $celfigura)) {
    echo json_encode(["status" => "error", "msg" => "own_piece"]);
    exit;
}

$rawMoves = php_szamolNyersLepesek($board, $fromRow, $fromCol, $figura);

$ervenyes = false;
foreach ($rawMoves as $m) {
    if ($m["sor"] === $toRow && $m["oszlop"] === $toCol) {
        $ervenyes = true;
        break;
    }
}

if (!$ervenyes) {
    echo json_encode(["status" => "error", "msg" => "illegal_move"]);
    exit;
}

$board[$toRow][$toCol] = $figura;
$board[$fromRow][$fromCol] = "";

$game["moves"][] = [
    "user" => $jatekos,
    "from" => [$fromRow, $fromCol],
    "to"   => [$toRow, $toCol],
    "piece" => $figura,
    "captured" => $celfigura
];

$vege = false;
$winnerName = null;

if ($celfigura === "K" || $celfigura === "k") {
    $vege = true;
    $winnerName = $jatekos;
    $game["status"] = "ended";
    $game["winner"] = $winnerName;
}

$game["board"] = $board;

if ($vege) {
    $games[$gameId] = $game;
    file_put_contents($gamesFile, json_encode($games, JSON_PRETTY_PRINT));

    $ch = curl_init("http://localhost/sakk-js/backend/finish_game.php");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        "game" => $gameId,
        "winner" => $winnerName
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $finishResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    file_put_contents(__DIR__ . "/curl_debug.txt",
    "finish_response_raw: " . $finishResponse . "\n" .
    "curl_error: " . $curlError . "\n\n",
    FILE_APPEND
);
    echo json_encode([
        "status" => "ok",
        "board" => $board,
        "turn" => $game["turn"],
        "moves" => $game["moves"],
        "status_game" => "ended",
        "winner" => $winnerName,
        "finish_response" => json_decode($finishResponse, true),
        "finish_response_raw" => $finishResponse,
"curl_error" => $curlError

    ]);
    exit;
}

$kovetkezoSzin = ($szin === "white") ? "black" : "white";
$game["turn"] = $kovetkezoSzin;

$games[$gameId] = $game;
file_put_contents($gamesFile, json_encode($games, JSON_PRETTY_PRINT));

echo json_encode([
    "status" => "ok",
    "board" => $board,
    "turn" => $game["turn"],
    "moves" => $game["moves"],
    "status_game" => $game["status"],
    "winner" => $game["winner"] ?? null
]);

/* --- SEGÉDFÜGGVÉNYEK --- */

function php_isWhitePiece($f)
{
    return $f !== "" && strtoupper($f) === $f;
}

function php_isBlackPiece($f)
{
    return $f !== "" && strtolower($f) === $f;
}

function php_sameColor($a, $b)
{
    if ($a === "" || $b === "") return false;
    return (php_isWhitePiece($a) && php_isWhitePiece($b)) ||
        (php_isBlackPiece($a) && php_isBlackPiece($b));
}

/*  
 *  FIGURÁK MOZGÁSA — EGYSZERŰ SAKKLOGIKA
 */
function php_szamolNyersLepesek($tabla, $sor, $oszlop, $figura)
{
    $lepesek = [];
    $f = strtolower($figura);

    switch ($f) {
        case "p":
            return php_pawnMoves($tabla, $sor, $oszlop, $figura);
        case "r":
            return php_rookMoves($tabla, $sor, $oszlop, $figura);
        case "n":
            return php_knightMoves($tabla, $sor, $oszlop, $figura);
        case "b":
            return php_bishopMoves($tabla, $sor, $oszlop, $figura);
        case "q":
            return php_queenMoves($tabla, $sor, $oszlop, $figura);
        case "k":
            return php_kingMoves($tabla, $sor, $oszlop, $figura);
    }

    return [];
}

/* --- PARASZT --- */
function php_pawnMoves($t, $r, $c, $f)
{
    $moves = [];
    $white = php_isWhitePiece($f);
    $dir = $white ? -1 : 1;

    // 1 lépés előre
    if (isset($t[$r + $dir][$c]) && $t[$r + $dir][$c] === "") {
        $moves[] = ["sor" => $r + $dir, "oszlop" => $c];

        // 2 lépés előre (kezdő sor)
        $start = $white ? 6 : 1;
        if ($r === $start && $t[$r + 2 * $dir][$c] === "") {
            $moves[] = ["sor" => $r + 2 * $dir, "oszlop" => $c];
        }
    }

    // ütés balra
    if (
        isset($t[$r + $dir][$c - 1]) && $t[$r + $dir][$c - 1] !== "" &&
        !php_sameColor($f, $t[$r + $dir][$c - 1])
    ) {
        $moves[] = ["sor" => $r + $dir, "oszlop" => $c - 1];
    }

    // ütés jobbra
    if (
        isset($t[$r + $dir][$c + 1]) && $t[$r + $dir][$c + 1] !== "" &&
        !php_sameColor($f, $t[$r + $dir][$c + 1])
    ) {
        $moves[] = ["sor" => $r + $dir, "oszlop" => $c + 1];
    }

    return $moves;
}

/* --- HUSZÁR --- */
function php_knightMoves($t, $r, $c, $f)
{
    $moves = [];
    $list = [
        [$r - 2, $c - 1],
        [$r - 2, $c + 1],
        [$r + 2, $c - 1],
        [$r + 2, $c + 1],
        [$r - 1, $c - 2],
        [$r - 1, $c + 2],
        [$r + 1, $c - 2],
        [$r + 1, $c + 2]
    ];

    foreach ($list as [$rr, $cc]) {
        if (!isset($t[$rr][$cc])) continue;
        if ($t[$rr][$cc] === "" || !php_sameColor($f, $t[$rr][$cc]) || strtolower($t[$rr][$cc]) === "k") {
            $moves[] = ["sor" => $rr, "oszlop" => $cc];
        }
    }

    return $moves;
}

/* --- BÁSTYA --- */
function php_rookMoves($t, $r, $c, $f)
{
    return array_merge(
        php_line($t, $r, $c, $f, 1, 0),
        php_line($t, $r, $c, $f, -1, 0),
        php_line($t, $r, $c, $f, 0, 1),
        php_line($t, $r, $c, $f, 0, -1)
    );
}

/* --- FUTÓ --- */
function php_bishopMoves($t, $r, $c, $f)
{
    return array_merge(
        php_line($t, $r, $c, $f, 1, 1),
        php_line($t, $r, $c, $f, 1, -1),
        php_line($t, $r, $c, $f, -1, 1),
        php_line($t, $r, $c, $f, -1, -1)
    );
}

/* --- VEZÉR --- */
function php_queenMoves($t, $r, $c, $f)
{
    return array_merge(
        php_rookMoves($t, $r, $c, $f),
        php_bishopMoves($t, $r, $c, $f)
    );
}

/* --- KIRÁLY --- */
function php_kingMoves($t, $r, $c, $f)
{
    $moves = [];
    $list = [
        [$r - 1, $c],
        [$r + 1, $c],
        [$r, $c - 1],
        [$r, $c + 1],
        [$r - 1, $c - 1],
        [$r - 1, $c + 1],
        [$r + 1, $c - 1],
        [$r + 1, $c + 1]
    ];

    foreach ($list as [$rr, $cc]) {
        if (!isset($t[$rr][$cc])) continue;
        if ($t[$rr][$cc] === "" || !php_sameColor($f, $t[$rr][$cc]) || strtolower($t[$rr][$cc]) === "k") {
            $moves[] = ["sor" => $rr, "oszlop" => $cc];
        }
    }

    return $moves;
}

/* --- VONALON MOZGÓ FIGURÁK (bástya, futó, vezér) --- */
function php_line($t, $r, $c, $f, $dr, $dc)
{
    $moves = [];
    $rr = $r + $dr;
    $cc = $c + $dc;

    while (isset($t[$rr][$cc])) {
        $cel = $t[$rr][$cc];

        if ($cel === "" || !php_sameColor($f, $cel) || strtolower($cel) === "k") {
            $moves[] = ["sor" => $rr, "oszlop" => $cc];
        }

        if ($cel !== "") break;

        $rr += $dr;
        $cc += $dc;
    }

    return $moves;
}
