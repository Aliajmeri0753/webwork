<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.html');
    exit;
}

$conn = getDBConnection();

$coursesResult = $conn->query("
    SELECT c.*, COUNT(uc.user_id) as enrolled_count
    FROM courses c
    LEFT JOIN user_courses uc ON c.id = uc.course_id
    GROUP BY c.id
    ORDER BY c.created_at DESC
");

$totalStudents = $conn->query("SELECT COUNT(DISTINCT user_id) FROM user_courses")->fetch_assoc()['COUNT(DISTINCT user_id)'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - EduSphere Admin</title>
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
                <a href="users.php" class="nav-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="courses.php" class="nav-item active">
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
                <h1 class="page-title">Courses Management</h1>
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
                            <input type="text" id="searchCourses" class="form-control" placeholder="Search courses...">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                            <i class="fas fa-plus me-2"></i>Add New Course
                        </button>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <?php
                    $coursesResult->data_seek(0);
                    while ($course = $coursesResult->fetch_assoc()):
                        $percentage = $totalStudents > 0 ? round(($course['enrolled_count'] / $totalStudents) * 100) : 0;
                    ?>
                    <div class="col-lg-6">
                        <div class="course-admin-card glass-card fade-in-up">
                            <div class="course-admin-header">
                                <div>
                                    <h5><?php echo htmlspecialchars($course['title']); ?></h5>
                                    <span class="badge bg-primary"><?php echo htmlspecialchars($course['category']); ?></span>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($course['level']); ?></span>
                                </div>
                                <div class="course-actions">
                                    <button class="btn btn-sm btn-info" onclick="editCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
                            <div class="course-stats">
                                <div class="stat-item">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $course['enrolled_count']; ?> Students</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo htmlspecialchars($course['duration']); ?></span>
                                </div>
                            </div>
                            <div class="enrollment-progress">
                                <div class="progress-info">
                                    <span>Enrollment Rate</span>
                                    <span><?php echo $percentage; ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="addCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Course</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCourseForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Course Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category" required>
                                    <option value="Technology">Technology</option>
                                    <option value="Business">Business</option>
                                    <option value="Design">Design</option>
                                    <option value="Marketing">Marketing</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Level</label>
                                <select class="form-select" name="level" required>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" placeholder="e.g., 12 weeks" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" class="form-control" name="image" placeholder="https://...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin-script.js"></script>
    <script>
        const searchInput = document.getElementById('searchCourses');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.course-admin-card');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.closest('.col-lg-6').style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.getElementById('addCourseForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('add_course.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Course added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        function editCourse(courseId) {
            alert('Edit functionality - Course ID: ' + courseId);
        }

        function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course?')) {
                fetch('delete_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ course_id: courseId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Course deleted successfully!');
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
