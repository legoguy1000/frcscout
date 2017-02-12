<?php

require $_SERVER['DOCUMENT_ROOT'].'/site/includes/vendor/autoload.php';

include($_SERVER['DOCUMENT_ROOT'].'/site/includes/db.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/user_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/general_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/match_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/tba_api_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/report_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/report_table_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/mq_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/email_functions.php');
include($_SERVER['DOCUMENT_ROOT'].'/site/includes/functions/stats_functions.php');


require $_SERVER['DOCUMENT_ROOT'].'/site/includes/libraries/password_compat-master/lib/password.php';
include $_SERVER['DOCUMENT_ROOT'].'/site/includes/libraries/simple_html_dom.php';
/* require($_SERVER['DOCUMENT_ROOT'].'/site/libraries/PHPMailer-master/PHPMailerAutoload.php'); */
require $_SERVER['DOCUMENT_ROOT'].'/site/includes/libraries/ATriggerPHP.v0.1.1/ATrigger.php';


ATrigger::init("4975539067521118538","b5KOag5leSNWdKhTJ1D7kRXG5AovYD");

	



?>