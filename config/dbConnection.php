<?php
include_once('config.php');
//Database Connection
$conn=mysqli_connect( DB_HOST, DB_USER , DB_PASSWORD , DB_NAME , DB_PORT );//Create Connection
if($conn->connect_error)//Check Connection
{
     die("Connection failed: ".$conn->connect_error);
}
mysqli_set_charset($conn, 'utf8mb4');
?>