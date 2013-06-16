<?php

class callSession {

    // Properties
    Protected $_playerID = null;
    Protected $_callSID = null;
    Protected $_from = null;
    Protected $_timeOfCurrentCall = null;
    Protected $_timeOfTotalGameSession = null;
    Protected $_lastTimerAlert = null;
    Protected $_currentGridCell = null;
    Protected $_currentMoveRequest = null;

    // Constructor
    public function __construct($arrPOST, $playingBySMS) {
        if ($playingBySMS) {
            $this->_callSID = $arrPOST["From"]. "_sms";
        } else {
            $this->_callSID = $arrPOST["CallSid"];
        }
        $this->_from = $arrPOST["From"];

        $this->updatePlaysTableWithCallSID();
        $this->_playerID = $this->getPlayerIDFromDatabase();
        $this->_timeOfCurrentCall = $this->getTimeSpentOnCurrentCall();
        $this->_lastTimerAlert = $this->getLastTimerAlert();
        $this->_currentGridCell = $this->getLastGridCell();

        if ($playingBySMS) {
            $this->_currentMoveRequest = $arrPOST["Body"];
        } elseif (isset($arrPOST["Digits"])) {
            $this->_currentMoveRequest = $this->setCurrentMoveRequest($arrPOST["Digits"]);
        }

    }
    
    // Methods
    public function getPlayerID() {
        return $this->_playerID;
    }

    public function getCallSID() {
        return $this->_callSID;
    }

    public function getCallerNumber() {
        return $this->_from;
    }

    public function getCurrentGridCell() {
        return $this->_currentGridCell;
    }

    public function getCurrentMoveRequest() {
        return $this->_currentMoveRequest;
    }

    public function getTimeOfCurrentCall() {
        return $this->_timeOfCurrentCall;
    }

    public function getLastCallWarning() {
        return $this->_lastTimerAlert;
    }

    public function getPlayerIDFromDatabase() {
        $dbEngine = new dbEngine();
        return $dbEngine->getPlayerID($this->_callSID);
    }

    public function movePlayerToNewGridCell($newCell) {
        $dbEngine = new dbEngine();
        $dbEngine->updatePlaysTableWithNewGridCell($newCell,$this->_callSID);
        $this->_currentGridCell = $newCell;
    }

    private function updatePlaysTableWithCallSID() {
        // We need to update the plays table
        // if the callID number is different then reset the callID and
        // set the startTimeCurrentPlay to the current time
        $dbEngine = new dbEngine();
        $callID = $dbEngine->getCallIDFromTelNumber($this->_from);
        if ($callID != $this->_callSID) {
            $dbEngine->updatePlaysTableWithCurrentPlay($this->_from, $this->_callSID);
        }
        echo "<!-- From:". $this->_from ." callSID:". $this->_callSID ." -->\n";
    }

    private function getLastTimerAlert() {
        // there are 7 call warnings that are played. they should be fired at intervals of 15 mins, 30 mins, 45 mins, 60 mins, 90 mins, 2 hours and 3 hours
        $dbEngine = new dbEngine();
        return $dbEngine->getLastTimerAlert($this->_callSID);
    }

    private function getLastGridCell() {
        $dbEngine = new dbEngine();
        return $dbEngine->getLastGridCell($this->_callSID);
    }

    private function getTimeSpentOnCurrentCall() {
        $dbEngine = new dbEngine();
        $timeOfCurrentCall = $dbEngine->getTimeOfCurrentCall($this->_callSID);
        echo "<!-- Time on current call:" .$timeOfCurrentCall ." -->\n";
        return $timeOfCurrentCall;
    }

    private function setCurrentMoveRequest($digits) {
        switch ($digits) {
            case "2":
                return "north";
            case "4":
                return "west";
            case "6":
                return "east";
            case "8":
                return "south";
            default:
                return null;
        }
    }

}
