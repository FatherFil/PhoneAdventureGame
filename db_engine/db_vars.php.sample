<?php

class dbVars
{

    // Properties
    private $_devmachine;
    private $_hostName;
    private $_username;
    private $_password;
    private $_appName;
    private $_dbVer;
    private $_dbName;

    // Constructor
    public function __construct() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->_hostName = "localhost";
            $this->_username = "root";
            $this->_password = "";
            $this->_appName = "advgame";
            $this->_dbVer = "0_1";
            $this->_dbName = $this->_appName."_v".$this->_dbVer;
            $this->_devmachine = true;
        } else {
            // Running on a public facing server
            $this->_hostName = "localhost";
            $this->_username = "your_db_username";
            $this->_password = "your_db_password";
            $this->_appName = "advgame";
            $this->_dbVer = "0_1";
            $this->_dbName = $this->_appName."_v".$this->_dbVer;
            $this->_devmachine = false;
        }
    }

    // Methods
    public function dbName()
    {
        return $this->_dbName;
    }

    public function host()
    {
        return $this->_hostName;
    }

    public function pass()
    {
        return $this->_password;
    }

    public function user()
    {
        return $this->_username;
    }

    public function runningOnDevMachine() {
        return $this->_devmachine;
    }

    // Destructor

}
