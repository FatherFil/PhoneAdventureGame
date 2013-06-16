<?php

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

    $_procStart = microtime();
    require_once "game_engine/game_constants.php";
    require_once "game_engine/game_engine.php";

    // Collect the posted array
    $_arrPOST = $_POST;

    // Check to see if we have POST vars, if not set up some test vars.
    if (!isset($_arrPOST["CallSid"])) {
        $_arrPOST["CallSid"] = "test";
        $_arrPOST["From"] = "localtest";
        //$_arrPOST["Digits"] = 0;
    }

    // Check to see if the user has played before
    $_returnAudio = array();
    $dbEngine = new dbEngine();
    $hasCallerPlayedBefore = $dbEngine->hasCallerPlayedBefore($_arrPOST["From"]);
    if ($hasCallerPlayedBefore === false) {
        $_returnAudio[] = $dbEngine->getAudioFilenameForAudioID("game_start");
        $_returnAudio[] = $dbEngine->getAudioFilenameForAudioID("call_costs");
    } else {
        $hasCallerCompletedGame = $dbEngine->hasCallerCompletedGame($_arrPOST["From"]);
        if ($hasCallerCompletedGame === true) {
            if ($_arrPOST["Digits"] == 0) {
                $dbEngine->resetPlayerEntry($_arrPOST["From"]);
                $_arrPOST["Digits"] = null;
            } else {
                $_returnAudio[] = $dbEngine->getAudioFilenameForAudioID("reset_game");
            }
        }
    }

    // Setup a new game engine session
    $_gameEngine = new gameEngine($_arrPOST);

    // Check the game play time interval and load up an audio file if nec.
    $_returnAudio[] = $_gameEngine->callbackReminder();

    // Are there any digits from the user (if there is then we're already in game)
    if (isset($_arrPOST["Digits"])) {
        if (in_array($_arrPOST["Digits"], array(2,4,6,8))) {
            $checkAction = $_gameEngine->processAction();
            if (!$checkAction) {
                $_returnAudio[] = $_gameEngine->getResponseForInvalidMove();
            }
        } else {
            $_returnAudio[] = $_gameEngine->getResponseForInvalidKeyPress();
        }
    } elseif ($hasCallerPlayedBefore === true) {
        $_returnAudio[] = $dbEngine->getAudioFilenameForAudioID("game_start");
        $_returnAudio[] = $dbEngine->getAudioFilenameForAudioID("call_costs");
    }

    // Get the audio files we need to play
    $_playAudio = $_gameEngine->getResponse();
    foreach ($_playAudio as $audio) {
        $_returnAudio[] = $audio;
    }

    // Create the outgoing Twilio XML and echo it to the session
    $_returnXML = $_gameEngine->createReturnXML($_returnAudio);

    // -----------------------------------------------------
    $_procEnd = microtime();
    $_procComplete = $_procEnd - $_procStart;

    // Log this information in the db as a game move
    $_gameEngine->logEvent($_procComplete);

    // Last job is to echo out the return XML to Twilio
    echo "\n".$_returnXML;

?>

