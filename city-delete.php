<?php
include("conn.php");
$id = $_GET['id'];
$sql = "DELETE FROM cities WHERE id = $id";
$query = mysqli_query($db,$sql);
header("location:cities.php");
?>