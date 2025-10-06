<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$courseId = intval($data['course_id'] ?? 0);

if ($courseId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
$stmt->bind_param("i", $courseId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Course deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete course']);
}

$stmt->close();
$conn->close();
?>
