<?php

function getDatabaseConnection()
{
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'pdf-viewer';
    $port = 3306;

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

$conn = getDatabaseConnection();