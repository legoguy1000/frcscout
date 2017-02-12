<?php

// mysql definitions
define("DB_SERVER", '10.100.10.35'); //your mysql server
define("DB_USER", "frc-scout-user"); //your mysql server username
define("DB_PASS", 'DyTckdrIYgtVIkPv'); //your mysql server password
define("DB_NAME", "frcscout_core-data"); //the mysql database to use



$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if($db->connect_errno > 0){
    die('Unable to connect to database ' . $db->connect_error . '');
}


?>