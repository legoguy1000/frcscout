<?php
echo '<a onclick="if (window.crypto) window.crypto.logout();">reload</a>';
echo '<a onclick="location.reload(true)">reload</a>';
$nameArr = explode('.',$_SERVER["SSL_CLIENT_S_DN_CN"]);
$fname = $nameArr[1];
$lname = $nameArr[0];
$mI = $nameArr[2];
$idNum = $nameArr[3];
echo "<DIV ALIGN=CENTER>";
echo "<H1> Welcome ".$fname." ".$lname."<H1>";
echo "<H2> O = ".$_SERVER["SSL_CLIENT_S_DN_O"]."<H2>";
echo "<H2> OU = ".$_SERVER["SSL_CLIENT_S_DN_OU"]."<H2>";
echo "<H2> OU 2= ".$_SERVER["SSL_CLIENT_S_DN_OU_2"]."<H2>";
echo "<H2>".$idNum."<H2>";
echo "</DIV>";
phpinfo();
?>
