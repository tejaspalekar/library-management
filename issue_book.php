<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = intval($_POST['book_id']);
    $user_id = intval($_POST['user_id']);
    $issue_days = intval($_POST['issue_days']);

    // Check if book is available
    $stmt = $conn->prepare("SELECT available_copies FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();

    if ($book && $book['available_copies'] > 0) {
        // Calculate dates
        $issue_date = date('Y-m-d');
        $due_date = date('Y-m-d', strtotime("+$issue_days days"));
        $admin_id = $_SESSION['user_id'];

        // Insert issue record
        $stmt = $conn->prepare("INSERT INTO book_issues (book_id, user_id, issue_date, due_date, issued_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissi", $book_id, $user_id, $issue_date, $due_date, $admin_id);

        if ($stmt->execute()) {
            // Update available copies
            $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = $book_id");
            $message = "Book issued successfully!";
            $message_type = 'success';
        } else {
            $message = "Failed to issue book.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Book is not available!";
        $message_type = 'error';
    }
}

// Get all books
$books = $conn->query("SELECT book_id, title, author, available_copies FROM books ORDER BY title");

// Get all students
$students = $conn->query("SELECT user_id, username, full_name FROM users WHERE user_type = 'student' ORDER BY full_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Book - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book"></i> Library Management
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-light btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-arrow-up-circle"></i> Issue Book</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <?php echo showAlert($message, $message_type); ?>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="book_id" class="form-label">Select Book</label>
                                <select class="form-select" id="book_id" name="book_id" required>
                                    <option value="">Choose a book...</option>
                                    <?php while ($book = $books->fetch_assoc()): ?>
                                        <option value="<?php echo $book['book_id']; ?>"
                                            <?php echo $book['available_copies'] == 0 ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($book['title']); ?> by <?php echo htmlspecialchars($book['author']); ?>
                                            (Available: <?php echo $book['available_copies']; ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="user_id" class="form-label">Select Student</label>
                                <select class="form-select" id="user_id" name="user_id" required>
                                    <option value="">Choose a student...</option>
                                    <?php while ($student = $students->fetch_assoc()): ?>
                                        <option value="<?php echo $student['user_id']; ?>">
                                            <?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['username']); ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="issue_days" class="form-label">Issue Period (Days)</label>
                                <select class="form-select" id="issue_days" name="issue_days" required>
                                    <option value="7">7 Days</option>
                                    <option value="14" selected>14 Days</option>
                                    <option value="21">21 Days</option>
                                    <option value="30">30 Days</option>
                                </select>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Issue Date: <?php echo date('d M Y'); ?><br>
                                <small>Due date will be calculated based on selected issue period.</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Issue Book
                            </button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
closeDBConnection($conn);
?>
