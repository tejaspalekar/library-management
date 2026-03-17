<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$conn = getDBConnection();

// Search functionality
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $sql .= " AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)";
    $search_param = "%$search%";
    $params[] = &$search_param;
    $params[] = &$search_param;
    $params[] = &$search_param;
    $types .= "sss";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = &$category;
    $types .= "s";
}

$sql .= " ORDER BY title ASC";

if ($types) {
    $stmt = $conn->prepare($sql);
    array_unshift($params, $types);
    call_user_func_array([$stmt, 'bind_param'], $params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Get categories
$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Books - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .book-card {
            transition: transform 0.2s;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-unavailable {
            background-color: #dc3545;
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
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
                <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h2 class="mb-4">Browse Books</h2>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="search" placeholder="Search by title, author, or ISBN" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($book = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card book-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">by <?php echo htmlspecialchars($book['author']); ?></h6>

                                <hr>

                                <p class="mb-1"><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                                <p class="mb-1"><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></p>
                                <p class="mb-1"><strong>Category:</strong>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($book['category']); ?></span>
                                </p>
                                <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($book['shelf_location']); ?></p>

                                <hr>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Availability:</strong><br>
                                        <?php if ($book['available_copies'] > 0): ?>
                                            <span class="badge badge-available">
                                                <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?> Available
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-unavailable">Not Available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No books found.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
closeDBConnection($conn);
?>
