<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Get statistics
$total_books = 0;
$available_books = 0;
$issued_books = 0;
$overdue_books = 0;
$my_issued_books = 0;

$result = $conn->query("SELECT COUNT(*) as total FROM books");
if ($row = $result->fetch_assoc()) {
    $total_books = $row['total'];
}

$result = $conn->query("SELECT SUM(available_copies) as available FROM books");
if ($row = $result->fetch_assoc()) {
    $available_books = $row['available'];
}

$result = $conn->query("SELECT COUNT(*) as issued FROM book_issues WHERE status = 'issued'");
if ($row = $result->fetch_assoc()) {
    $issued_books = $row['issued'];
}

$result = $conn->query("SELECT COUNT(*) as overdue FROM book_issues WHERE status = 'issued' AND due_date < CURDATE()");
if ($row = $result->fetch_assoc()) {
    $overdue_books = $row['overdue'];
}

if (!isAdmin()) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as my_books FROM book_issues WHERE user_id = ? AND status = 'issued'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $my_issued_books = $row['my_books'];
    }
    $stmt->close();
}

closeDBConnection($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stat-card {
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book"></i> Library Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text text-white me-3">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['full_name']; ?>
                            (<?php echo ucfirst($_SESSION['user_type']); ?>)
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="bi bi-house-door"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books.php">
                                <i class="bi bi-book"></i> Browse Books
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_books.php">
                                <i class="bi bi-plus-circle"></i> Manage Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="issue_book.php">
                                <i class="bi bi-arrow-up-circle"></i> Issue Book
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="return_book.php">
                                <i class="bi bi-arrow-down-circle"></i> Return Book
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="all_issues.php">
                                <i class="bi bi-list-ul"></i> All Issues
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="my_books.php">
                                <i class="bi bi-bookmark"></i> My Books
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <h2 class="mb-4">Dashboard</h2>

                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Total Books</h6>
                                        <h2 class="mb-0"><?php echo $total_books; ?></h2>
                                    </div>
                                    <div class="text-primary fs-1">
                                        <i class="bi bi-book"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Available Books</h6>
                                        <h2 class="mb-0"><?php echo $available_books; ?></h2>
                                    </div>
                                    <div class="text-success fs-1">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted">Currently Issued</h6>
                                        <h2 class="mb-0"><?php echo $issued_books; ?></h2>
                                    </div>
                                    <div class="text-info fs-1">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-4">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="text-muted"><?php echo isAdmin() ? 'Overdue Books' : 'My Issued Books'; ?></h6>
                                        <h2 class="mb-0"><?php echo isAdmin() ? $overdue_books : $my_issued_books; ?></h2>
                                    </div>
                                    <div class="<?php echo isAdmin() ? 'text-danger' : 'text-warning'; ?> fs-1">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php if (isAdmin()): ?>
                                    <div class="col-md-3 mb-3">
                                        <a href="manage_books.php" class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-plus-circle"></i><br>Add New Book
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="issue_book.php" class="btn btn-success w-100 py-3">
                                            <i class="bi bi-arrow-up-circle"></i><br>Issue Book
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="return_book.php" class="btn btn-warning w-100 py-3">
                                            <i class="bi bi-arrow-down-circle"></i><br>Return Book
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="all_issues.php" class="btn btn-info w-100 py-3">
                                            <i class="bi bi-list-ul"></i><br>View All Issues
                                        </a>
                                    </div>
                                    <?php else: ?>
                                    <div class="col-md-4 mb-3">
                                        <a href="books.php" class="btn btn-primary w-100 py-3">
                                            <i class="bi bi-search"></i><br>Browse Books
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="my_books.php" class="btn btn-success w-100 py-3">
                                            <i class="bi bi-bookmark"></i><br>My Issued Books
                                        </a>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <a href="books.php" class="btn btn-info w-100 py-3">
                                            <i class="bi bi-book-half"></i><br>Available Books
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
