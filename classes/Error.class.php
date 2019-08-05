<?php 
/**
 * Custom error handling.
 * I'll gather all error handling stuff here 
 * where they can easily be printed into the database.
 */
class Error {
    private $DB;

    function __construct($db) {
        $this->DB = $db;
    }

    function __destruct() {
        $this->DB = NULL;
    }

    /**
     * Custom error handler. Saves error into the database.
     */
    function LogError($errno, $errstr, $errfile, $errline) {
        $input = [
            'ErrNro' => $errno,
            'ErrStr' => $errstr,
            'ErrFile' => $errfile,
            'ErrLine' => $errline,
            'Time' => time(),
        ];
    
        $DB->SetItem("errors", $input);
    }
}
?>