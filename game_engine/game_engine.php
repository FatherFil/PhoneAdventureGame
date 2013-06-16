<?php

require_once "db_engine/db_engine.php";
require_once "call_session.php";

class gameEngine
{

    // Properties
    protected $_callSession;
    protected $_isPlayingBySms;

    // Constructor
    public function __construct($arrPOST, $playingBySms = false) {
        $this->_isPlayingBySms = $playingBySms;
        $this->_callSession = new callSession($arrPOST, $this->_isPlayingBySms);
    }

    // Methods
    public function callbackReminder() {
        $reminders = array(0 => "0mins", 15 => "15mins", 30 => "30mins", 45 => "45mins", 60 => "60mins", 90 => "90mins", 120 => "2hours", 180 => "3hours");

        $lengthOfCurrentCall = $this->_callSession->getTimeOfCurrentCall();
        $lastCallWarning = $this->_callSession->getLastCallWarning();

        $reminderID = 0;
        foreach ($reminders as $reminder => $audioID) {
            if ($lengthOfCurrentCall < $reminder) {
                break;
            } else {
                $reminderID = $reminder;
            }
        }

        echo "<!-- Current reminder time:". $reminders[$reminderID] ." -->\n";
        echo "<!-- Last call warning played:". $lastCallWarning ." -->\n";
        echo "<!-- Grid cell prior to action:". $this->_callSession->getCurrentGridCell() ." -->\n";

        if ($lastCallWarning < $reminderID) {
            $dbEngine = new dbEngine();
            $dbEngine->setLastCallWarning($reminderID, $this->_callSession->getCallSID());
            return $dbEngine->getAudioFilenameForAudioID($reminders[$reminderID]);
        }
    }

    public function processAction() {
        $dbEngine = new dbEngine();
        $allowedMoves = $dbEngine->getPathsFromGridCell($this->_callSession->getCurrentGridCell());
        $requestedMove = $this->_callSession->getCurrentMoveRequest();

        if (isset($allowedMoves[$requestedMove])) {
            $this->_callSession->movePlayerToNewGridCell($allowedMoves[$requestedMove]);
            echo "<!-- Moving to grid cell:". $this->_callSession->getCurrentGridCell() ." -->\n";
            return true;
        } else {
            return false;
        }
    }

    public function getResponse() {
        $_responses = array();
        $dbEngine = new dbEngine();

        $currentGridCell = $this->_callSession->getCurrentGridCell();
        if ($this->_isPlayingBySms) {
            $_responses[] = $dbEngine->getTextForCurrentCell($currentGridCell);
        } else {
            $_responses[] = $dbEngine->getAudioFilenameForCurrentCell($currentGridCell);
        }

        // does the player interact with an item - load up the inventory response
        $_responses[] = $this->getResponseForInventoryGain($currentGridCell);
        $_responses[] = $this->getResponseForInventoryNeed($currentGridCell);

        $endGame = false;
        if ($currentGridCell == ENDGAME_GRID_CELL) {
            $playerID = $this->_callSession->getPlayerID();
            $inventoryNeed = $dbEngine->checkForInventoryNeedInGridCell($currentGridCell);
            $playerHasEndGameItem = $dbEngine->hasPlayerGotItem($inventoryNeed,$playerID);
            if ($playerHasEndGameItem) {
                if ($this->_isPlayingBySms) {
                    $_responses[] = $dbEngine->getTextForAudioID("game_end");
                } else {
                    $_responses[] = $dbEngine->getAudioFilenameForAudioID("game_end");
                }
                $dbEngine->callerHasCompletedGame($this->_callSession->getCallSID());
                $endGame = true;
            }
        }

        if (!$endGame) {
            // work out the moves the player can make next
            $pathsFromGrid = $dbEngine->getPathsFromGridCell($currentGridCell);
            if (isset($pathsFromGrid["need"])) {
                $validMoves = $this->checkMovesForInventoryNeeds($pathsFromGrid);
            } else {
                $validMoves = $pathsFromGrid;
            }
            $_responses[] = $this->getResponseForPossibleMoves($validMoves);
        }

        if (empty($_responses)) {
            die(ERR_NO_AUDIO);
        }
        return $_responses;
    }

    public function getResponseForInventoryGain($currentGridCell) {
        $_response = '';
        $dbEngine = new dbEngine();
        $inventoryGain = $dbEngine->checkForInventoryGainInGridCell($currentGridCell);

        if ($inventoryGain != false) {
            $playerID = $this->_callSession->getPlayerID();
            $playerAlreadyHasInventoryItem = $dbEngine->hasPlayerGotItem($inventoryGain,$playerID);
            if (!$playerAlreadyHasInventoryItem) {
                if ($this->_isPlayingBySms) {
                    $_response = $dbEngine->getTextForInventoryGain($inventoryGain);
                } else {
                    $_response = $dbEngine->getAudioFilenameForInventoryGain($inventoryGain);
                }
                $dbEngine->addItemToInventoryForPlayer($inventoryGain,$playerID);
            }
        }
        return $_response;
    }

    public function getResponseForInventoryNeed($currentGridCell) {
        $audioToPlay = '';
        $dbEngine = new dbEngine();
        $inventoryNeed = $dbEngine->checkForInventoryNeedInGridCell($currentGridCell);
        if ($inventoryNeed != false) {
            $playerID = $this->_callSession->getPlayerID();
            $playerAlreadyHasInventoryItem = $dbEngine->hasPlayerGotItem($inventoryNeed,$playerID);
            if ($playerAlreadyHasInventoryItem) {
                if ($this->_isPlayingBySms) {
                    $audioToPlay = $dbEngine->getTextForInventoryUse($inventoryNeed);
                } else {
                    $audioToPlay = $dbEngine->getAudioFilenameForInventoryUse($inventoryNeed);
                }
            } else {
                if ($this->_isPlayingBySms) {
                    $audioToPlay = $dbEngine->getTextForInventoryNotFound($inventoryNeed);
                } else {
                    $audioToPlay = $dbEngine->getAudioFilenameForInventoryNotFound($inventoryNeed);
                }
            }
        }
        return $audioToPlay;
    }

    public function createReturnXML($returnAudio) {
        $returnXML = "<Response>\n".
                     "<Gather numDigits=\"1\" action=\"process_call.php\" method=\"POST\">\n";

        foreach ($returnAudio as $audio) {
            if (!empty($audio)) {
                $returnXML .= "<Play>audio/". $audio ."</Play>\n";
            }
        }

        $returnXML .= "</Gather>\n</Response>";
        return $returnXML;
    }

    public function createReturnXMLForSMS($returnText) {
        $returnXML = "<Response>\n";
        foreach ($returnText as $text) {
            if (!empty($text)) {
                $returnXML .= "<Sms>". $text ."</Sms>\n";
            }
        }
        $returnXML .= "</Response>";
        return $returnXML;
    }

    public function logEvent($procTime) {
        $dbEngine = new dbEngine();
        // todo: Log the event, the move and the audio files sent back to the player
    }

    public function getResponseForInvalidMove() {
        $dbEngine = new dbEngine();
        if ($this->_isPlayingBySms) {
            $response = $dbEngine->getTextForAudioID("invalid_move");
        } else {
            $response = $dbEngine->getAudioFilenameForAudioID("invalid_move");
        }
        return $response;
    }

    public function getResponseForInvalidKeyPress() {
        $dbEngine = new dbEngine();
        if ($this->_isPlayingBySms) {
            $response = $dbEngine->getTextForAudioID("invalid_keypress");
        } else {
            $response = $dbEngine->getAudioFilenameForAudioID("invalid_keypress");
        }
        return $response;
    }

    private function checkMovesForInventoryNeeds($pathsFromGrid) {
        $blockedGrid = $pathsFromGrid["blocked"];
        $itemIDNeed = $pathsFromGrid["need"];

        $dbEngine = new dbEngine();
        $hasPlayerGotItem = $dbEngine->hasPlayerGotItem($itemIDNeed,$this->_callSession->getPlayerID());
        if ($hasPlayerGotItem === false) {
            $pathsFromGrid[$blockedGrid] = null;
        }
        return $pathsFromGrid;
    }

    private function getResponseForPossibleMoves($validMoves) {
        $possibleMoves = array("north" => $validMoves["north"],
                                "south" => $validMoves["south"],
                                "east" => $validMoves["east"],
                                "west" => $validMoves["west"]);
        echo "<!-- N:". $possibleMoves["north"] ." ".
            "S:". $possibleMoves["south"] ." ".
            "E:". $possibleMoves["east"] ." ".
            "W:". $possibleMoves["west"] ." -->\n";
        $possibleMoveAudioID = "path_";
        foreach ($possibleMoves as $route => $val) {
            if (!empty($val)) {
                $possibleMoveAudioID .= substr($route,0,1);
            }
        }

        $dbEngine = new dbEngine();
        if ($this->_isPlayingBySms) {
            return $dbEngine->getTextForAudioID($possibleMoveAudioID);
        } else {
            return $dbEngine->getAudioFilenameForAudioID($possibleMoveAudioID);
        }
    }

}
