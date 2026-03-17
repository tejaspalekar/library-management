<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$conn = getDBConnection();

// Get all issues
$sql = "SELECT bi.issue_id, b.title, b.author, u.full_name, u.username,
        bi.issue_date, bi.due_date, bi.return_date, bi.fine_amount, bi.status,
        DATEDIFF(CURDATE(), bi.due_date) as days_overdue
        FROM book_issues bi
        JOIN books b ON bi.book_id = b.book_id
        JOIN users u ON bi.user_id = u.user_id
        ORDER BY bi.issue_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Issues - Library Management System</title>
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

    <div class="container-fluid py-4">
        <h2 class="mb-4"><i class="bi bi-list-ul"></i> All Book Issues</h2>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Complete Issue History</h5>
            </div>
            <div class="card-body">
                <?php if ($result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Student</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                    <th>Fine</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $is_overdue = ($row['status'] === 'issued' && $row['days_overdue'] > 0);
                                ?>
                                    <tr class="<?php echo $is_overdue ? 'overdue' : ''; ?>">
                                        <td><?php echo $row['issue_id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                                        <td><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($row['full_name']); ?><br>
                                            <small class="text-muted">(<?php echo htmlspecialchars($row['username']); ?>)</small>
                                        </td>
                                        <td><?php echo formatDate($row['issue_date']); ?></td>
                                        <td><?php echo formatDate($row['due_date']); ?></td>
                                        <td>
                                            <?php echo $row['return_date'] ? formatDate($row['return_date']) : '-'; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['status'] === 'returned'): ?>
                                                <span class="badge bg-success">Returned</span>
                                            <?php elseif ($is_overdue): ?>
                                                <span class="badge bg-danger">
                                                    Overdue (<?php echo $row['days_overdue']; ?> days)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-primary">Issued</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $fine = $row['status'] === 'returned' ? $row['fine_amount'] : calculateFine($row['due_date']);
                                            if ($fine > 0):
                                            ?>
                                                <span class="text-danger fw-bold">₹<?php echo number_format($fine, 2); ?></span>
                                            <?php else: ?>
                                                <span class="text-success">₹0.00</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No book issues found.
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
