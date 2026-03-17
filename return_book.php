<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $issue_id = intval($_POST['issue_id']);
    $return_date = date('Y-m-d');
    $admin_id = $_SESSION['user_id'];

    // Get issue details
    $stmt = $conn->prepare("SELECT book_id, due_date FROM book_issues WHERE issue_id = ? AND status = 'issued'");
    $stmt->bind_param("i", $issue_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $issue = $result->fetch_assoc();
    $stmt->close();

    if ($issue) {
        // Calculate fine
        $fine = calculateFine($issue['due_date'], $return_date);

        // Update issue record
        $stmt = $conn->prepare("UPDATE book_issues SET return_date = ?, fine_amount = ?, status = 'returned', returned_to = ? WHERE issue_id = ?");
        $stmt->bind_param("sdii", $return_date, $fine, $admin_id, $issue_id);

        if ($stmt->execute()) {
            // Update available copies
            $conn->query("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = " . $issue['book_id']);

            if ($fine > 0) {
                $message = "Book returned successfully! Fine: ₹" . number_format($fine, 2);
                $message_type = 'warning';
            } else {
                $message = "Book returned successfully!";
                $message_type = 'success';
            }
        } else {
            $message = "Failed to return book.";
            $message_type = 'error';
        }
        $stmt->close();
    } else {
        $message = "Invalid issue ID or book already returned!";
        $message_type = 'error';
    }
}

// Get all issued books
$sql = "SELECT bi.issue_id, b.title, b.author, u.full_name, u.username,
        bi.issue_date, bi.due_date,
        DATEDIFF(CURDATE(), bi.due_date) as days_overdue
        FROM book_issues bi
        JOIN books b ON bi.book_id = b.book_id
        JOIN users u ON bi.user_id = u.user_id
        WHERE bi.status = 'issued'
        ORDER BY bi.due_date ASC";
$issued_books = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Book - Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .overdue {
            background-color: #fff3cd;
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
        <h2 class="mb-4"><i class="bi bi-arrow-down-circle"></i> Return Book</h2>

        <?php if ($message): ?>
            <?php echo showAlert($message, $message_type); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Currently Issued Books</h5>
            </div>
            <div class="card-body">
                <?php if ($issued_books->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Student</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Fine</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $issued_books->fetch_assoc()):
                                    $fine = calculateFine($row['due_date']);
                                    $is_overdue = $row['days_overdue'] > 0;
                                ?>
                                    <tr class="<?php echo $is_overdue ? 'overdue' : ''; ?>">
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['full_name']); ?><br>
                                            <small class="text-muted">(<?php echo htmlspecialchars($row['username']); ?>)</small>
                                        </td>
                                        <td><?php echo formatDate($row['issue_date']); ?></td>
                                        <td><?php echo formatDate($row['due_date']); ?></td>
                                        <td>
                                            <?php if ($is_overdue): ?>
                                                <span class="badge bg-danger">
                                                    Overdue by <?php echo $row['days_overdue']; ?> days
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">On Time</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($fine > 0): ?>
                                                <span class="text-danger fw-bold">₹<?php echo number_format($fine, 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-success">₹0.00</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="issue_id" value="<?php echo $row['issue_id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Confirm return of this book?');">
                                                    <i class="bi bi-check-circle"></i> Return
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No books currently issued.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
closeDBConnection($conn);
?>
