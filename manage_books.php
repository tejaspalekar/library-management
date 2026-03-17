<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

requireAdmin();

$conn = getDBConnection();
$message = '';
$message_type = '';

// Handle Add/Edit Book
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = sanitize($_POST['isbn']);
    $title = sanitize($_POST['title']);
    $author = sanitize($_POST['author']);
    $publisher = sanitize($_POST['publisher']);
    $category = sanitize($_POST['category']);
    $total_copies = intval($_POST['total_copies']);
    $shelf_location = sanitize($_POST['shelf_location']);

    if (isset($_POST['book_id']) && $_POST['book_id']) {
        // Update existing book
        $book_id = intval($_POST['book_id']);
        $stmt = $conn->prepare("UPDATE books SET isbn=?, title=?, author=?, publisher=?, category=?, total_copies=?, shelf_location=? WHERE book_id=?");
        $stmt->bind_param("sssssisi", $isbn, $title, $author, $publisher, $category, $total_copies, $shelf_location, $book_id);

        if ($stmt->execute()) {
            $message = "Book updated successfully!";
            $message_type = 'success';
        } else {
            $message = "Failed to update book.";
            $message_type = 'error';
        }
    } else {
        // Add new book
        $available_copies = $total_copies;
        $stmt = $conn->prepare("INSERT INTO books (isbn, title, author, publisher, category, total_copies, available_copies, shelf_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssiis", $isbn, $title, $author, $publisher, $category, $total_copies, $available_copies, $shelf_location);

        if ($stmt->execute()) {
            $message = "Book added successfully!";
            $message_type = 'success';
        } else {
            $message = "Failed to add book.";
            $message_type = 'error';
        }
    }
    $stmt->close();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $book_id = intval($_GET['delete']);
    $conn->query("DELETE FROM books WHERE book_id = $book_id");
    $message = "Book deleted successfully!";
    $message_type = 'success';
}

// Get all books
$books = $conn->query("SELECT * FROM books ORDER BY title");

// Get book for editing
$edit_book = null;
if (isset($_GET['edit'])) {
    $book_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_book = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books - Library Management System</title>
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
        <h2 class="mb-4"><i class="bi bi-plus-circle"></i> Manage Books</h2>

        <?php if ($message): ?>
            <?php echo showAlert($message, $message_type); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Add/Edit Book Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?php echo $edit_book ? 'Edit Book' : 'Add New Book'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_book): ?>
                                <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">ISBN</label>
                                <input type="text" class="form-control" name="isbn"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['isbn']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['title']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Author</label>
                                <input type="text" class="form-control" name="author"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['author']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Publisher</label>
                                <input type="text" class="form-control" name="publisher"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['publisher']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <input type="text" class="form-control" name="category"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['category']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Total Copies</label>
                                <input type="number" class="form-control" name="total_copies" min="1"
                                    value="<?php echo $edit_book ? $edit_book['total_copies'] : '1'; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Shelf Location</label>
                                <input type="text" class="form-control" name="shelf_location"
                                    value="<?php echo $edit_book ? htmlspecialchars($edit_book['shelf_location']) : ''; ?>">
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-check-circle"></i> <?php echo $edit_book ? 'Update Book' : 'Add Book'; ?>
                            </button>
                            <?php if ($edit_book): ?>
                                <a href="manage_books.php" class="btn btn-secondary w-100 mt-2">Cancel</a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Books List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">All Books</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>ISBN</th>
                                        <th>Copies</th>
                                        <th>Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($book = $books->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                            <td><?php echo $book['total_copies']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $book['available_copies'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $book['available_copies']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="?edit=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="?delete=<?php echo $book['book_id']; ?>" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this book?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
closeDBConnection($conn);
?>
