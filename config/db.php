<?php

function getDatabaseConnection()
{

    $servername = 'localhost';
    $username = 'u884492537_user';
    $password = 'REGl?F2p#';
    $dbname = 'u884492537_pdf_viewer';

    // $servername = 'localhost';
    // $username = 'root';
    // $password = '';
    // $dbname = 'pdf-viewer';

    $port = 3306;

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

$conn = getDatabaseConnection();