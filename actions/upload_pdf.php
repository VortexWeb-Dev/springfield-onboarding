<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/db.php';
$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pdf_name = isset($_POST['pdf_name']) ? trim($_POST['pdf_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate PDF name
    if (empty($pdf_name) || strlen($pdf_name) > 255) {
        // Redirect or show an error message
        die("Error: Invalid PDF name.");
    }

    if ((isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == UPLOAD_ERR_OK) && (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] == UPLOAD_ERR_OK)) {
        $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
        $fileName = $_FILES['pdf_file']['name'];
        $fileSize = $_FILES['pdf_file']['size'];
        $fileType = $_FILES['pdf_file']['type'];

        $thumbnailFileTmpPath = $_FILES['thumbnail_file']['tmp_name'];
        $thumbnailFileName = $_FILES['thumbnail_file']['name'];
        $thumbnailFileSize = $_FILES['thumbnail_file']['size'];
        $thumbnailFileType = $_FILES['thumbnail_file']['type'];

        // Validate file type and size
        if ($fileType != 'application/pdf') {
            die("Error: Invalid file type.");
        }

        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        if ($fileSize > $maxFileSize || $thumbnailFileSize > $maxFileSize) {
            die("Error: File size exceeds limit of 5 MB.");
            
        }

        // Read file content
        $fileContent = file_get_contents($fileTmpPath);
        $thumbnailFileContent = file_get_contents($thumbnailFileTmpPath);

        if ($fileContent === false || $thumbnailFileContent === false) {
            die("Error: Could not read file content.");
        }

        // Insert into database
        $query = "INSERT INTO pdf (pdf_name, description, pdf_data, thumbnail_data, thumbnail_type) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            die("Error preparing statement: " . htmlspecialchars($conn->error));
        }

        // Bind parameters
        $null = NULL;
        $stmt->bind_param('ssbbs', $pdf_name, $description, $fileContent, $thumbnailFileContent, $thumbnailFileType);

        $stmt->send_long_data(2, $fileContent);
        $stmt->send_long_data(3, $thumbnailFileContent);

        if ($stmt->execute()) {
            header("Location: ../index.php");
            exit();
        } else {
            // Log the error instead of showing it
            error_log("Database execute error: " . htmlspecialchars($stmt->error));
            die("Error: Could not upload PDF. Please try again.");
        }

        $stmt->close();
    } else {
        die("Error: File upload failed with error code: " . $_FILES['pdf_file']['error']);
    }
}

$conn->close();
