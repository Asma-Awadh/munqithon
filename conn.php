<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: text/html; charset=utf-8');
$server="localhost";
$username="root";
$password="";
$dbname="munqithon";
$db=mysqli_connect($server,$username,$password,$dbname);
?>