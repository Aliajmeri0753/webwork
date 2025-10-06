<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $image = trim($_POST['image'] ?? '');

    if (empty($title) || empty($description) || empty($category) || empty($level) || empty($duration)) {
        echo json_encode(['success' => false, 'message' => 'All required fields must be filled']);
        exit;
    }

    $conn = getDBConnection();

    $stmt = $conn->prepare("INSERT INTO courses (title, description, category, level, duration, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $category, $level, $duration, $image);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Course added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add course']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
