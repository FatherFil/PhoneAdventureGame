<?php

require_once "meekrodb.2.1.class.php";

class dbEngine
{

    // Properties

    // Constructor

    // Methods
    public function getMissingGridAudio() {
        // get a list of audio references in the grid table that don't appear in the audio table
        $sqlQuery = "SELECT grid.audioID ".
                    "FROM   grid ".
                    "WHERE  NOT EXISTS (".
                    "   SELECT 'x' ".
                    "   FROM   audio ".
                    "   WHERE  audioID = grid.audioID ".
                    ")";
        $sqlResult = DB::query($sqlQuery);
        return $sqlResult;
    }

    public function getMissingInventoryAudio($type) {
        // get a list of audio references in the inventory table that don't appear in the audio table
        $sqlQuery = "SELECT items.audioID_". $type ." as audioID ".
                    "FROM   items ".
                    "WHERE  NOT EXISTS (".
                    "   SELECT 'x' ".
                    "   FROM   audio ".
                    "   WHERE  audioID = items.audioID_". $type.
                    ")";
        $sqlResult = DB::query($sqlQuery);
        return $sqlResult;
    }

    public function getMissingInventory($type) {
        // get a list of inventory references that are required in the grid and not in the inventory table
        $sqlQuery = "SELECT itemID_". $type ." as itemID ".
                    "FROM   grid ".
                    "WHERE  itemID_". $type ." is not null ".
                    "AND    NOT EXISTS (".
                    "   SELECT 'x' ".
                    "   FROM   items ".
                    "   WHERE  itemID = grid.itemID_". $type.
                    ")";
        $sqlResult = DB::query($sqlQuery);
        return $sqlResult;
    }

    function getAllAudioFilenames() {
        $sqlQuery = "SELECT audioID, filename ".
                    "FROM   audio";
        $sqlResult = DB::query($sqlQuery);
        return $sqlResult;
    }

    function getLeaderboard() {
        $sqlQuery = "SELECT * ".
                    "FROM   players ".
                    "WHERE  completedStory = '1' ".
                    "ORDER BY totalMovesMade ASC, totalSecondsPlay ASC";
        $leaderboard = DB::query($sqlQuery);
        return $leaderboard;
    }

    function getAllUncompletedPlayers() {
        $sqlQuery = "SELECT * ".
                    "FROM   players ".
                    "WHERE  completedStory = '0' ".
                    "ORDER BY telNumber";
        $uncompleted = DB::query($sqlQuery);
        return $uncompleted;
    }

}
