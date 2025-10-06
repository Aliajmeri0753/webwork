<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.html');
    exit;
}

$conn = getDBConnection();

$usersResult = $conn->query("
    SELECT u.id, u.full_name, u.email, u.created_at,
           COUNT(DISTINCT uc.course_id) as enrolled_courses,
           COUNT(DISTINCT ul.id) as login_count,
           MAX(ul.login_time) as last_login
    FROM users u
    LEFT JOIN user_courses uc ON u.id = uc.user_id
    LEFT JOIN user_logins ul ON u.id = ul.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - EduSphere Admin</title>
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
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="users.php" class="nav-item active">
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
                <h1 class="page-title">Users Management</h1>
                <div class="header-actions">
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-bell"></i>
                    </button>
                    <span class="admin-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchUsers" class="form-control" placeholder="Search users...">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary" onclick="exportUsers()">
                            <i class="fas fa-download me-2"></i>Export Users
                        </button>
                    </div>
                </div>

                <div class="card glass-card fade-in-up">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>All Registered Users
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover admin-table" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User Name</th>
                                        <th>Email</th>
                                        <th>Enrolled Courses</th>
                                        <th>Login Count</th>
                                        <th>Registration Date</th>
                                        <th>Last Login</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $usersResult->fetch_assoc()): ?>
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
                                                <?php echo $user['enrolled_courses']; ?> courses
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <?php echo $user['login_count']; ?> logins
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        <td><?php echo $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
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
        </main>
    </div>

    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user me-2"></i>User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin-script.js"></script>
    <script>
        const searchInput = document.getElementById('searchUsers');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#usersTable tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        function viewUserDetails(userId) {
            fetch('get_user_details.php?id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.user;
                        const courses = data.courses;

                        let coursesHtml = '<ul class="list-group">';
                        if (courses.length > 0) {
                            courses.forEach(course => {
                                coursesHtml += `<li class="list-group-item">${course.title} - <small>${course.enrolled_at}</small></li>`;
                            });
                        } else {
                            coursesHtml += '<li class="list-group-item">No courses enrolled</li>';
                        }
                        coursesHtml += '</ul>';

                        document.getElementById('userDetailsContent').innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> ${user.full_name}</p>
                                    <p><strong>Email:</strong> ${user.email}</p>
                                    <p><strong>Registration Date:</strong> ${user.created_at}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Total Logins:</strong> ${data.login_count}</p>
                                    <p><strong>Last Login:</strong> ${data.last_login || 'Never'}</p>
                                </div>
                            </div>
                            <hr>
                            <h6>Enrolled Courses:</h6>
                            ${coursesHtml}
                        `;

                        new bootstrap.Modal(document.getElementById('userDetailsModal')).show();
                    }
                });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
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

        function exportUsers() {
            window.location.href = 'export_users.php';
        }
    </script>
</body>
</html>
