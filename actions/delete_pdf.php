<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once '../config/db.php';
$conn = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid or missing PDF ID']);
    exit();
}

$pdf_id = (int)$_POST['id'];

// Prepare and execute delete query
$query = "DELETE FROM pdf WHERE id = ?";
$stmt = $conn->prepare($query);
if ($stmt === false) {
    error_log("Error preparing statement: " . htmlspecialchars($conn->error));
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

$stmt->bind_param('i', $pdf_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'PDF not found']);
    }
} else {
    error_log("Database execute error: " . htmlspecialchars($stmt->error));
    echo json_encode(['success' => false, 'message' => 'Error deleting PDF']);
}

$stmt->close();
$conn->close();
