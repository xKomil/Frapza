<?php

$servername = "localhost"; 
$username = "root"; 
$password = "";
$database = "2025_kamil321";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Połączenie nieudane: " . mysqli_connect_error());
}
else {
    
}

?>