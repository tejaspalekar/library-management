<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireLogin();

$conn = getDBConnection();
$user_id = $_SESSION['user_id'];

// Get student's issued books
$sql = "SELECT bi.issue_id, b.title, b.author, b.isbn,
        bi.issue_date, bi.due_date, bi.return_date, bi.fine_amount, bi.status,
        DATEDIFF(CURDATE(), bi.due_date) as days_overdue
        FROM book_issues bi
        JOIN books b ON bi.book_id = b.book_id
        WHERE bi.user_id = ?
        ORDER BY bi.issue_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Books - Library Management System</title>
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
        <h2 class="mb-4"><i class="bi bi-bookmark"></i> My Issued Books</h2>

        <!-- Currently Issued -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Currently Issued</h5>
            </div>
            <div class="card-body">
                <?php
                $has_issued = false;
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()):
                    if ($row['status'] === 'issued'):
                        $has_issued = true;
                        $fine = calculateFine($row['due_date']);
                        $is_overdue = $row['days_overdue'] > 0;
                ?>
                    <div class="card mb-3 <?php echo $is_overdue ? 'overdue' : ''; ?>">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5><?php echo htmlspecialchars($row['title']); ?></h5>
                                    <p class="mb-1"><strong>Author:</strong> <?php echo htmlspecialchars($row['author']); ?></p>
                                    <p class="mb-1"><strong>ISBN:</strong> <?php echo htmlspecialchars($row['isbn']); ?></p>
                                    <p class="mb-1"><strong>Issue Date:</strong> <?php echo formatDate($row['issue_date']); ?></p>
                                    <p class="mb-1"><strong>Due Date:</strong> <?php echo formatDate($row['due_date']); ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <?php if ($is_overdue): ?>
                                        <span class="badge bg-danger mb-2">
                                            Overdue by <?php echo $row['days_overdue']; ?> days
                                        </span>
                                        <h4 class="text-danger">Fine: ₹<?php echo number_format($fine, 2); ?></h4>
                                    <?php else: ?>
                                        <span class="badge bg-success mb-2">On Time</span>
                                        <p class="text-muted">No fine</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                    endif;
                endwhile;

                if (!$has_issued):
                ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You don't have any books currently issued.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Return History -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Return History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Fine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $has_history = false;
                            $result->data_seek(0);
                            while ($row = $result->fetch_assoc()):
                                if ($row['status'] === 'returned'):
                                    $has_history = true;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                                    <td><?php echo htmlspecialchars($row['author']); ?></td>
                                    <td><?php echo formatDate($row['issue_date']); ?></td>
                                    <td><?php echo formatDate($row['due_date']); ?></td>
                                    <td><?php echo formatDate($row['return_date']); ?></td>
                                    <td>
                                        <?php if ($row['fine_amount'] > 0): ?>
                                            <span class="text-danger">₹<?php echo number_format($row['fine_amount'], 2); ?></span>
                                        <?php else: ?>
                                            <span class="text-success">₹0.00</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                                endif;
                            endwhile;

                            if (!$has_history):
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No return history available.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
closeDBConnection($conn);
?>
