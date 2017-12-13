<?php
include "/config.php";
$link = mysqli_connect($dbhost,$dbuser,$dbpassword);
mysqli_select_db($link,$dbname);
mysqli_query($link,"set names 'utf8'");
?>