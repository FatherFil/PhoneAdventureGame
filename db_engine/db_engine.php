<?php

require_once "meekrodb.2.1.class.php";

class dbEngine
{

    // Properties

    // Constructor

    // Methods
    public function getPlayerID($callSID) {
        $sqlQuery = "SELECT playerID ".
                    "FROM   players ".
                    "WHERE  callID = %s0";
        return DB::queryFirstField($sqlQuery,$callSID);

    }

    public function getTimeOfCurrentCall($callSID) {
        // get the length of time spent on this call
        $sqlQuery = "SELECT TIME_TO_SEC(TIMEDIFF(CURTIME(), startTimeCurrentPlay)) ".
                    "FROM   players ".
                    "WHERE  callID = %s0";
        $secondsOnCurrentCall = DB::queryFirstField($sqlQuery, $callSID);
        return floor($secondsOnCurrentCall / 60);
    }

    public function getLastTimerAlert($callSID) {
        // get the status of the call time warnings from the session table
        $sqlQuery = "SELECT lastTimerAlert ".
                    "FROM   players ".
                    "WHERE  callID = %s0";
        $lastCallWarning = DB::queryFirstField($sqlQuery, $callSID);
        if ($lastCallWarning == null) {
            return 0;
        } else {
            return $lastCallWarning;
        }
    }

    public function getLastGridCell($callSID) {
        // get the last grid cell the player was in
        $sqlQuery = "SELECT gridID ".
                    "FROM   players ".
                    "WHERE  callID = %s0";
        $lastGridCell = DB::queryFirstField($sqlQuery, $callSID);
        if ($lastGridCell == null) {
            return START_GRID_CELL;
        } else {
            return $lastGridCell;
        }
    }

    public function getPathsFromGridCell($currentGridCell) {
        // get the grid cells into which the player can pass
        $sqlQuery = "SELECT path_north as north, path_south as south, ".
                    "       path_east as east, path_west as west, ".
                    "       path_blocked as blocked, itemID_need as need ".
                    "FROM   grid ".
                    "WHERE  gridID = %s0";
        $pathsFromGrid = DB::queryFirstRow($sqlQuery, $currentGridCell);
        return $pathsFromGrid;
    }

    public function checkForInventoryGainInGridCell($currentGridCell) {
        // get any inventory IDs for this current cell
        $sqlQuery = "SELECT itemID_gain ".
                    "FROM   grid ".
                    "WHERE  gridID = %s0";
        $inventoryGainInGridCell = DB::queryFirstField($sqlQuery, $currentGridCell);
        if ($inventoryGainInGridCell == null) {
            return false;
        } else {
            return $inventoryGainInGridCell;
        }
    }

    public function checkForInventoryNeedInGridCell($currentGridCell) {
        // get any inventory IDs for this current cell
        $sqlQuery = "SELECT itemID_need ".
                    "FROM   grid ".
                    "WHERE  gridID = %s0";
        $inventoryNeedInGridCell = DB::queryFirstField($sqlQuery, $currentGridCell);
        if ($inventoryNeedInGridCell == null) {
            return false;
        } else {
            return $inventoryNeedInGridCell;
        }
    }

    public function getTextForCurrentCell($currentGridCell) {
        // get the filename for the audio file for this curent cell
        $sqlQuery = "SELECT audio.smstext ".
                    "FROM   audio, grid ".
                    "WHERE  grid.audioID = audio.audioID ".
                    "AND    grid.gridID = %s0";
        $smsTextForCurrentCell = DB::queryFirstField($sqlQuery, $currentGridCell);
        if (empty($smsTextForCurrentCell)) {$smsTextForCurrentCell="<!-- Unable to locate text for grid ".$currentGridCell."-->";}
        return $smsTextForCurrentCell;
    }

    public function getAudioFilenameForCurrentCell($currentGridCell) {
        // get the filename for the audio file for this curent cell
        $sqlQuery = "SELECT audio.filename ".
                    "FROM   audio, grid ".
                    "WHERE  grid.audioID = audio.audioID ".
                    "AND    grid.gridID = %s0";
        $audioFilenameForCurrentCell = DB::queryFirstField($sqlQuery, $currentGridCell);
        if (empty($audioFilenameForCurrentCell)) {$audioFilenameForCurrentCell="<!-- Unable to locate audio for grid ".$currentGridCell."-->";}
        return $audioFilenameForCurrentCell;
    }

    public function getAudioIDsForInventoryItem($inventoryID) {
        // get the three audio file IDs for the inventory item
        $sqlQuery = "SELECT audioID_gain, audioID_user, audioID_notfound ".
                    "FROM   items ".
                    "WHERE  itemID = %s0";
        $audioIDsForInventoryItem = DB::queryFirstRow($sqlQuery, $inventoryID);
        return $audioIDsForInventoryItem;
    }

    public function getAudioFilenameForAudioID($audioID) {
        // get the filename for a given audio ID
        $sqlQuery = "SELECT filename ".
                    "FROM   audio ".
                    "WHERE  audioID = %s0";
        $audioFilename = DB::queryFirstField($sqlQuery, $audioID);
        return $audioFilename;
    }

    public function getTextForAudioID($audioID) {
        $sqlQuery = "SELECT smstext ".
                    "FROM   audio ".
                    "WHERE  audioID = %s0";
        $smsText = DB::queryFirstField($sqlQuery, $audioID);
        return $smsText;
    }

    public function setLastCallWarning($callWarning, $callSID) {
        $sqlQuery = "UPDATE players ".
                    "SET    lastTimerAlert = %s0 ".
                    "WHERE  callID = %s1";
        DB::query($sqlQuery, $callWarning, $callSID);
    }

    public function getCallIDFromTelNumber($telNumber) {
        $sqlQuery = "SELECT callID ".
                    "FROM   players ".
                    "WHERE  telNumber = %s0";
        return DB::queryFirstField($sqlQuery,$telNumber);
    }

    public function updatePlaysTableWithCurrentPlay($telNumber, $callSID) {
        $sqlQuery = "INSERT INTO players ".
                    "(telNumber, callID, gridID, lastTimerAlert, startTimeCurrentPlay) VALUES ".
                    "(%s0, %s1, %s2, 0, now()) ".
                    "ON DUPLICATE KEY UPDATE ".
                    "callID = %s1, lastTimerAlert = 0, startTimeCurrentPlay = NOW()";
        DB::query($sqlQuery, $telNumber, $callSID, START_GRID_CELL);
    }

    public function updatePlaysTableWithNewGridCell($newCell,$callSID) {
        $sqlQuery = "UPDATE players ".
                    "SET    gridID = %s0, ".
                    "       totalMovesMade = totalMovesMade + 1, ".
                    "       totalSecondsPlay = (totalSecondsPlay + (TIME_TO_SEC(CURTIME()) - TIME_TO_SEC(lastUpdated))) ".
                    "WHERE  callID = %s1";
        DB::query($sqlQuery, $newCell, $callSID);
    }

    public function hasPlayerGotItem($itemID,$playerID) {
        $sqlQuery = "SELECT 'x' ".
                    "FROM   inventory ".
                    "WHERE  playerID = %s0 ".
                    "AND    itemID = %s1";
        $hasPlayerGotItem = DB::queryFirstField($sqlQuery,$playerID,$itemID);
        if ($hasPlayerGotItem == null) {
            return false;
        } else {
            return true;
        }
    }

    public function getTextForInventoryGain($itemID) {
        $sqlQuery = "SELECT audio.smstext ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_gain ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function getAudioFilenameForInventoryGain($itemID) {
        $sqlQuery = "SELECT audio.filename ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_gain ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function getTextForInventoryUse($itemID) {
        $sqlQuery = "SELECT audio.smstext ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_use ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function getAudioFilenameForInventoryUse($itemID) {
        $sqlQuery = "SELECT audio.filename ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_use ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function getTextForInventoryNotFound($itemID) {
        $sqlQuery = "SELECT audio.smstext ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_notfound ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function getAudioFilenameForInventoryNotFound($itemID) {
        $sqlQuery = "SELECT audio.filename ".
                    "FROM   audio, items ".
                    "WHERE  audio.audioID = items.audioID_notfound ".
                    "AND    items.itemID = %s0";
        return DB::queryFirstField($sqlQuery,$itemID);
    }

    public function addItemToInventoryForPlayer($itemID,$playerID) {
        $sqlQuery = "INSERT INTO inventory ".
                    "(playerID, itemID) VALUES ".
                    "(%s0, %s1)";
        DB::query($sqlQuery, $playerID, $itemID);
        echo "<!-- Adding inventory item:". $itemID ." -->\n";
    }

    public function hasCallerPlayedBefore($telNumber) {
        $sqlQuery = "SELECT 'x' ".
                    "FROM   players ".
                    "WHERE  telNumber = %s0";
        $isThisTheirFirstTime = DB::queryFirstField($sqlQuery,$telNumber);
        if ($isThisTheirFirstTime == null) {
            return false;
        } else {
            return true;
        }
    }

    public function callerHasCompletedGame($callID) {
        $sqlQuery = "UPDATE players ".
                    "SET    completedStory = 1 ".
                    "WHERE  callID = %s0";
        DB::query($sqlQuery, $callID);
    }

    public function hasCallerCompletedGame($telNumber) {
        $sqlQuery = "SELECT completedStory ".
                    "FROM   players ".
                    "WHERE  telNumber = %s0";
        $hasCallerCompletedGame = DB::queryFirstField($sqlQuery, $telNumber);
        if ($hasCallerCompletedGame == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function resetPlayerEntry($telNumber) {
        $sqlQuery = "UPDATE players ".
                    "SET    telNumber = CONCAT('xx',telNumber), ".
                    "       callID = '' ".
                    "WHERE  telNumber = %s0";
        DB::query($sqlQuery, $telNumber);
    }

}
