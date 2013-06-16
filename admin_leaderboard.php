<?php

    require_once "game_engine/admin_engine.php";
    $adminEngine = new AdminEngine();

    $leaderboardHTML = $adminEngine->getLeaderboard();
    $gamesInPlayHTML = $adminEngine->getPlayersInPlay();

?>

<html
<head>
    <title>Adventure Game - Leaderboard</title>
    <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Asap' rel='stylesheet' type='text/css'>
    <style>
        body {background-color: #b4ccb4;margin:30px;}
        * {font-family:'Asap',Arial; color: #667766;text-shadow: 0px 1px 1px #999;text-transform: uppercase;}
        div {background:#ffffff;border-radius:7px;padding:20px;margin-bottom:20px;box-shadow: 2px 2px 5px #666;text-shadow:0;color:#000;}
        th {color: #000;}
        th, td {text-shadow: 0 0 0;}
    </style>
</head>
<body>

    <div>
        <h2>Leaderboard</h2>
        <?=$leaderboardHTML?>
    </div>
    <div>
        <h2>Games in Play</h2>
        <?=$gamesInPlayHTML?>
    </div>

</body>
</html>
