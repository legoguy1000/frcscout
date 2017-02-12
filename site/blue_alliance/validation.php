<?php
include('../db.php');
include('../functions/user_functions.php');
include('../functions/item_functions.php');
include('../functions/general_functions.php');
require('../libraries/PHPMailer-master/PHPMailerAutoload.php');

$json = file_get_contents('php://input'); 
$formData = json_decode($json,true);

$verification = 'No Data';
if(isset($formData))
{
	$verification = json_encode($formData);
}
$mail = new PHPMailer;

$mail->setFrom('validation@bluealliance.resnick-tech.com', 'Blue Alliance Validation');
$mail->addAddress('adr8292@gmail.com', 'Alex resnick');     // Add a recipient
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Blue Alliance Validation Info';
$mail->Body    = $verification;

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}
?>
