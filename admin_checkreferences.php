<?php

    require_once "game_engine/admin_engine.php";
    $adminEngine = new AdminEngine();
    echo "\n";

    $arrMissingInventory = $adminEngine->findMissingInventoryItems();
    foreach ($arrMissingInventory as $missingInventory) {
        echo "* Inventory missing: ". $missingInventory["itemID"] ."\n";
    }

    $arrMissingAudio = $adminEngine->findMissingAudioEntriesInDatabase();
    foreach ($arrMissingAudio as $missingAudio) {
        echo "* Audio missing: ". $missingAudio["audioID"] ."\n";
    }

    $arrMissingAudioFiles = $adminEngine->findMissingAudioFiles();
    foreach ($arrMissingAudioFiles as $missingFile) {
        echo "* File missing: ". $missingFile["audioID"] ." - ". $missingFile["filename"]. "\n";
    }
