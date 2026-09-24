<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "pcyouconnect";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('Database connection unavailable.');
}

$conn->set_charset('utf8mb4');

?>