<?php

function db_connect() {

    // Define connection as a static variable, to avoid connecting more than once
    static $db;

    // Try and connect to the database, if a connection has not been established yet
    if(!isset($db)) {
         // Load configuration as an array. Use the actual location of your configuration file
			 $db_server = getIniProp('db_host'); //your mysql server
			 $db_user = getIniProp('db_user'); //your mysql server username
			 $db_pass = getIniProp('db_pass'); //your mysql server password
			 $db_name = getIniProp('db_name'); //the mysql database to use

			 $db = new mysqli($db_server, $db_user, $db_pass, $db_name);
    }

    // If connection was not successful, handle the error
    if($db->connect_error) {
        // Handle error - notify administrator, log to a file, show an error screen, etc.
        return $db->connect_error;
    }
    return $db;
}

function db_query($query) {
    // Connect to the database
    $db = db_connect();

    // Query the database
    $result = $db->query($query);
		if($result === false) {
				db_error();
				return false;
		}
    return $result;
}

function db_select($query) {
    $rows = array();
    $result = db_query($query);

    // If query failed, return `false`
    if($result === false) {
        return false;
    }

    // If query was successful, retrieve all the rows into an array
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function db_select_single($query) {
    $rows = array();
    $result = db_query($query);

    // If query failed, return `false`
    if($result === false) {
        return false;
    }

    $row = $result->fetch_assoc();
    return $row;
}

function db_quote($value) {
    $db = db_connect();
    return '"'.mysqli_real_escape_string($db,$value).'"';
}

function db_error() {
    $db = db_connect();
		errorHandle(mysqli_error($db));
    return mysqli_error($db);
}


?>
