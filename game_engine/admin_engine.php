<?php

require_once "db_engine/db_engine_admin.php";

class AdminEngine {

    // Properties
    
    // Constructor
    
    // Methods
    public function findMissingAudioEntriesInDatabase() {
        $dbEngine = new dbEngine();
        $missingGridAudio = $dbEngine->getMissingGridAudio();
        $missingInventoryGainAudio = $dbEngine->getMissingInventoryAudio("gain");
        $missingInventoryUseAudio = $dbEngine->getMissingInventoryAudio("use");
        $missingInventoryNotFoundAudio = $dbEngine->getMissingInventoryAudio("notfound");

        $missingAudio = array_merge($missingGridAudio,$missingInventoryGainAudio,$missingInventoryUseAudio,$missingInventoryNotFoundAudio);

        return $missingAudio;
    }

    public function findMissingInventoryItems() {
        $dbEngine = new dbEngine();
        $missingInventoryGainItems = $dbEngine->getMissingInventory("gain");
        $missingInventoryNeedItems = $dbEngine->getMissingInventory("need");

        $missingInventory = array_merge($missingInventoryGainItems,$missingInventoryNeedItems);

        return $missingInventory;
    }

    public function findMissingAudioFiles() {
        $dbEngine = new dbEngine();
        $allAudioFiles = $dbEngine->getAllAudioFilenames();
        $missingAudioFiles = array();
        foreach ($allAudioFiles as $audio) {
           if (!file_exists("audio/".$audio["filename"])) {
               $missingAudioFiles[] = $audio;
           }
        }
        return $missingAudioFiles;
    }

    public function getLeaderboard() {
        $output = "<table class=\"table table-striped\"><tr>".
                  "<th>Telephone</th>".
                  "<th>Last Played</th>".
                  "<th>Seconds Played</th>".
                  "<th>Moves Made</th>".
                  "</tr>\n";

        $dbEngine = new dbEngine();
        $leaderboard = $dbEngine->getLeaderboard();

        foreach ($leaderboard as $entry) {
            $output .=  "<tr>".
                        "<td>". $entry["telNumber"] ."</td>\n".
                        "<td>". $entry["lastUpdated"] ."</td>\n".
                        "<td>". $entry["totalSecondsPlay"] ."</td>\n".
                        "<td>". $entry["totalMovesMade"] ."</td>\n".
                        "</tr>";
        }

        $output .= "</table>";
        return $output;
    }

    public function getPlayersInPlay() {
        $output = "<table class=\"table table-striped\"><tr>".
                  "<th>Telephone</th>".
                  "<th>Last Grid</th>".
                  "<th>Last Played</th>".
                  "<th>Seconds Played</th>".
                  "<th>Moves Made</th>".
                  "</tr>\n";
        $dbEngine = new dbEngine();
        $leaderboard = $dbEngine->getAllUncompletedPlayers();

        foreach ($leaderboard as $entry) {
            $output .=  "<tr>".
                "<td>". $entry["telNumber"] ."</td>\n".
                "<td>". $entry["gridID"] ."</td>\n".
                "<td>". $entry["lastUpdated"] ."</td>\n".
                "<td>". $entry["totalSecondsPlay"] ."</td>\n".
                "<td>". $entry["totalMovesMade"] ."</td>\n".
                "</tr>";
        }

        $output .= "</table>";
        return $output;
    }
}
