<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.html');
    exit;
}

$conn = getDBConnection();

$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalLogins = $conn->query("SELECT COUNT(*) as count FROM user_logins")->fetch_assoc()['count'];
$totalCourses = $conn->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'];
$totalEnrollments = $conn->query("SELECT COUNT(*) as count FROM user_courses")->fetch_assoc()['count'];

$recentUsers = $conn->query("
    SELECT u.id, u.full_name, u.email, u.created_at,
           GROUP_CONCAT(c.title SEPARATOR ', ') as courses,
           MAX(ul.login_time) as last_login
    FROM users u
    LEFT JOIN user_courses uc ON u.id = uc.user_id
    LEFT JOIN courses c ON uc.course_id = c.id
    LEFT JOIN user_logins ul ON u.id = ul.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT 10
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EduSphere Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-graduation-cap logo-icon"></i>
                <span class="logo-text">EduSphere</span>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="admin-profile">
                <div class="admin-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h5>
                <p><?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>
            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="courses.php" class="nav-item">
                    <i class="fas fa-book"></i>
                    <span>Courses</span>
                </a>
                <a href="settings.php" class="nav-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="admin-header">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
                <div class="header-actions">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-bell"></i>
                    </button>
                    <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="row g-4 mb-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card fade-in-up">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="counter" data-target="<?php echo $totalUsers; ?>">0</h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card fade-in-up" style="animation-delay: 0.1s">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-sign-in-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="counter" data-target="<?php echo $totalLogins; ?>">0</h3>
                                <p>Total Logins</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card fade-in-up" style="animation-delay: 0.2s">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="counter" data-target="<?php echo $totalCourses; ?>">0</h3>
                                <p>Total Courses</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-card fade-in-up" style="animation-delay: 0.3s">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="counter" data-target="<?php echo $totalEnrollments; ?>">0</h3>
                                <p>Enrollments</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12">
                        <div class="card glass-card fade-in-up">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-users me-2"></i>Recent Users
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User Name</th>
                                                <th>Email</th>
                                                <th>Enrolled Courses</th>
                                                <th>Registration Date</th>
                                                <th>Last Login</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $recentUsers->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $user['id']; ?></td>
                                                <td>
                                                    <div class="user-info">
                                                        <i class="fas fa-user-circle"></i>
                                                        <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo $user['courses'] ? htmlspecialchars($user['courses']) : 'No courses'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary" onclick="viewUser(<?php echo $user['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin-script.js"></script>
    <script>
        function viewUser(userId) {
            window.location.href = 'users.php?view=' + userId;
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ user_id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('User deleted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>
