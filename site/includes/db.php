<?php
//require($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/getConfigFile.php');

// mysql definitions
define("DB_SERVER", getIniProp('db_host')); //your mysql server
define("DB_USER", getIniProp('db_user')); //your mysql server username
define("DB_PASS", getIniProp('db_pass')); //your mysql server password
define("DB_NAME", getIniProp('db_name')); //the mysql database to use



$db = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

if($db->connect_errno > 0){
    die('Unable to connect to database ' . $db->connect_error . '');
}


?>