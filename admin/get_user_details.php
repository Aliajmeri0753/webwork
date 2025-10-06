<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, full_name, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

$coursesStmt = $conn->prepare("
    SELECT c.title, uc.enrolled_at
    FROM user_courses uc
    JOIN courses c ON uc.course_id = c.id
    WHERE uc.user_id = ?
");
$coursesStmt->bind_param("i", $userId);
$coursesStmt->execute();
$coursesResult = $coursesStmt->get_result();

$courses = [];
while ($course = $coursesResult->fetch_assoc()) {
    $courses[] = [
        'title' => $course['title'],
        'enrolled_at' => date('M d, Y', strtotime($course['enrolled_at']))
    ];
}
$coursesStmt->close();

$loginCount = $conn->query("SELECT COUNT(*) as count FROM user_logins WHERE user_id = $userId")->fetch_assoc()['count'];
$lastLoginResult = $conn->query("SELECT MAX(login_time) as last_login FROM user_logins WHERE user_id = $userId")->fetch_assoc();
$lastLogin = $lastLoginResult['last_login'] ? date('M d, Y H:i', strtotime($lastLoginResult['last_login'])) : null;

$conn->close();

echo json_encode([
    'success' => true,
    'user' => [
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'created_at' => date('M d, Y', strtotime($user['created_at']))
    ],
    'courses' => $courses,
    'login_count' => $loginCount,
    'last_login' => $lastLogin
]);
?>
