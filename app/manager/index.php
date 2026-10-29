    <?php

require '../../config/config.php';

requireRole('manager');

// Determine current section
$section = $_GET['section'] ?? 'students' ?? 'books';

// Determine CRUD operation
$action = $_GET['action'] ?? '';

// STUDENTS
    // Fetch students
    if($section === 'students'){
        $stmt = $pdo->query("   
            SELECT *
            FROM students
            ORDER BY student_id DESC
        ");

    $students = $stmt->fetchAll();
}

    // Create Student
    if($section==='students' && $action==='create'){
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $firstName = trim($_POST['student_first_name'] ?? '');
            $lastName = trim($_POST['student_last_name'] ?? '');
            $course = trim($_POST['student_course'] ?? '');

        if($firstName !== '' && $lastName !=='' && $course!==''){

            $sql=("
                INSERT INTO students (
                    student_first_name,
                    student_last_name,
                    student_course
                )
                VALUES (?,?,?)
            ");

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $firstName,
                $lastName,
                $course
            ]);

            //$_SESSION['alert'] = 'Student Saved Successfully';

            header("Location: index.php?section=students");
            exit;

        }
    }

}

    // Update Student
if ($section === 'students' && $action === 'update') {

    $studentId = (int) ($_GET['id'] ?? 0);

    // Retrieve Student Info
    $stmt = $pdo->prepare("
        SELECT *
        FROM students
        WHERE student_id = ?
    ");

        $stmt->execute([$studentId]);

        $student = $stmt->fetch();

    if (!$student) {
        die("Student Not Found");
    }

    // Update student
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $firstName = trim($_POST['student_first_name'] ?? '');
        $lastName = trim($_POST['student_last_name'] ?? '');
        $course = trim($_POST['student_course'] ?? '');

        $sql = "
            UPDATE students
            SET
                student_first_name = ?,
                student_last_name = ?,
                student_course = ?
            WHERE student_id = ?
        ";

            $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $firstName,
            $lastName,
            $course,
            $studentId
        ]);

        header("Location: index.php?section=students");
        exit;
    }
}


// Fetch books
$books = $pdo->query("
    SELECT *
    FROM books
    ORDER BY book_id DESC
")->fetchAll();



// Create Book
if($section === 'books' && $action === 'create'){

    if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $bookTitle = trim($_POST['book_title'] ?? '');
        $bookAuthor = trim($_POST['book_author'] ?? '');
        $bookCategory = trim($_POST['book_category'] ?? '');

        if ($bookTitle !== '' && $bookAuthor !== '' && $bookCategory !== '') {

            $sql = "
                INSERT INTO books (
                    book_title,
                    book_author,
                    book_category
                )
                VALUES (?, ?, ?)
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $bookTitle,
                $bookAuthor,
                $bookCategory
            ]);

            header("Location: index.php?section=books");
            exit;
        }
    }
}


// Update Book
if ($section === 'books' && $action === 'update') {

    $bookId = (int) ($_GET['id'] ?? 0);

    $stmt = $pdo->prepare("
        SELECT *
        FROM books
        WHERE book_id = ?
    ");

    $stmt->execute([$bookId]);

    $book = $stmt->fetch();

    if (!$book) {
        die("Book Not Found");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $title = trim($_POST['book_title'] ?? '');
        $author = trim($_POST['book_author'] ?? '');
        $category = trim($_POST['book_category'] ?? '');

        $sql = "
            UPDATE books
            SET
                book_title = ?,
                book_author = ?,
                book_category = ?
            WHERE book_id = ?
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $title,
            $author,
            $category,
            $bookId
        ]);

        header("Location: index.php?section=books");
        exit;
    }
}


// Retrieve students for borrow form
if ($section === 'borrow') {

    $stmt = $pdo->prepare("
        SELECT
            student_id,
            student_first_name,
            student_last_name
        FROM students
        ORDER BY student_last_name, student_first_name
    ");

    $stmt->execute();

    $students = $stmt->fetchAll();


    // Retrieve books for borrow form
    $stmt = $pdo->prepare("
        SELECT
            book_id,
            book_title,
            book_author
        FROM books
        ORDER BY book_title
    ");

    $stmt->execute();

    $books = $stmt->fetchAll();


    // Retrieve borrow records
    $stmt = $pdo->prepare("
        SELECT
            b.borrow_id,
            b.student_id,
            b.book_id,
            b.borrow_date,
            b.borrow_return_date,
            s.student_first_name,
            s.student_last_name,
            bk.book_title
            
        FROM borrow b
        INNER JOIN students s
            ON b.student_id = s.student_id
        INNER JOIN books bk
            ON b.book_id = bk.book_id
        ORDER BY b.borrow_id DESC
    ");

    $stmt->execute();

    $borrows = $stmt->fetchAll();
}


// Create Borrow
if ($section === 'borrow' && $action === 'create') {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $bookId = (int) ($_POST['book_id'] ?? 0);

        if ($studentId > 0 && $bookId > 0) {

            // Check if student has an unreturned book
            $stmt = $pdo->prepare("
                SELECT borrow_id
                FROM borrow
                WHERE student_id = ?
                AND borrow_return_date IS NULL
                LIMIT 1
            ");

            $stmt->execute([$studentId]);

            $studentBorrow = $stmt->fetch();

            if ($studentBorrow) {

                $_SESSION['alert'] =
                    'This student cannot borrow another book because a previous book has not been returned.';

            } else {

                // Check if book is already borrowed
                $stmt = $pdo->prepare("
                    SELECT borrow_id
                    FROM borrow
                    WHERE book_id = ?
                    AND borrow_return_date IS NULL
                    LIMIT 1
                ");

                $stmt->execute([$bookId]);

                $bookBorrow = $stmt->fetch();

                if ($bookBorrow) {

                    $_SESSION['alert'] =
                        'This book is currently borrowed by another student.';

                } else {

                    // Create borrow record
                    $stmt = $pdo->prepare("
                        INSERT INTO borrow (
                            student_id,
                            book_id
                        )
                        VALUES (?, ?)
                    ");

                    $stmt->execute([
                        $studentId,
                        $bookId
                    ]);

                    $_SESSION['alert'] =
                        'Book borrowed successfully.';
                }
            }

            header("Location: index.php?section=borrow");
            exit;
        }
    }
}


if ($section === 'borrow' && $action === 'return') {

    $borrowId = (int) ($_GET['id'] ?? 0);

    if ($borrowId > 0) {

        $stmt = $pdo->prepare("
            UPDATE borrow
            SET borrow_return_date = NOW()
            WHERE borrow_id = ?
            AND borrow_return_date IS NULL
        ");

        $stmt->execute([$borrowId]);

        $_SESSION['alert'] = 'Book returned successfully.';
    }

    header("Location: index.php?section=borrow");
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>

        body {
            font-family: Arial;
            background: #c4adad;
            margin: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        a {
            background: #f30101;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2c2828;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        
    </style>
</head>

<body>
    <title>Library System</title>
</head>
<body>
    <h1>Simple Library System</h1>
    <nav>
        <a href="index.php?section=students">Students</a>
        <a href="index.php?section=books">Books</a>
        <a href="index.php?section=borrow">Borrow</a>
    </nav>
    <hr>
    <a href="../../auth/signout.php">Sign Out</a>
    <?php if($section === 'students'):?>
        <h1>Students</h1>

        <p>
            <a href="index.php?section=students&action=create">
                Add student
            </a>
        </p>

        <?php if($action==='create'): ?>
            <h2>Create Student</h2>

            <form method="POST">
                <p>
                <label>First Name:</label>
                <br>
                <input  type="text"
                        name="student_first_name"
                        required
                />
                </p>
                <p>
                <label>Last Name:</label>
                <br>
                <input  type="text"
                        name="student_last_name"
                        required
                />
                </p>
                <p>
                <label>Course:</label>
                <br>
                <input  type="text"
                        name="student_course"
                        required
                />
                </p>

                    <button type="submit">
                        Save
                    </button>

                    <a href="index.php?section=students">
                        Cancel
                    </a>
                </form>

        <?php elseif($action==='update'):?>
            <h2>Update Student Info</h2>

            <form method="POST">
                <p>
                <label>First Name:</label>
                <br>
                <input  type="text"
                        name="student_first_name"
                        value="<?=htmlspecialchars($student['student_first_name']) ?>"
                        required
                />
                </p>
                <p>
                <label>Last Name:</label>
                <br>
                <input  type="text"
                        name="student_last_name"
                        value="<?=htmlspecialchars($student['student_last_name']) ?>"
                        required
                />
                </p>
                <p>
                <label>Course:</label>
                <br>
                <input  type="text"
                        name="student_course"
                        value="<?=htmlspecialchars($student['student_course']) ?>"
                        required
                />
                </p>

                    <button type="submit">
                        Save
                    </button>

                    <a href="index.php?section=students">
                        Cancel
                    </a>
                </form>

        <?php else: ?>
          <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Course</th>
                    <th>Create at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($students as $student): ?>
                    <tr>
                        <td>
                            <?=htmlspecialchars($student['student_id']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($student['student_first_name']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($student['student_last_name']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($student['student_course']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($student['student_created_at']) ?>
                        </td>
                        <td>
                            <a href="index.php?section=students&action=update&id=<?=$student['student_id']?>">
                                Edit
                            </a>

                            <a>Delete</a>
                        </td>
                    </tr>
                    <?php endforeach?>
            </tbody>
            </table>
        <?php endif;?>
    <?php endif; ?>

    <?php if($section === 'books'):?>
        <h1>Books</h1>
        <p>
            <a href="index.php?section=books&action=create">
                Add books
            </a>
        </p>

        <?php if($action==='create'): ?>
            <h2>Create <i class="fas fa-book-dead    "></i></h2>

            <form method="POST">
                <p>
                <label>Book Title:</label>
                <br>
                <input  type="text"
                        name="book_title"
                        required
                />
                </p>
                <p>
                <label>Book Author:</label>
                <br>
                <input  type="text"
                        name="book_author"
                        required
                />
                </p>
                <p>
                <label>Book Category:</label>
                <br>
                <input  type="text"
                        name="book_category"
                        required
                />
                </p>

                <button type="submit">
                    Save
                </button>

                <a href="index.php?section=books">
                    Cancel
                </a>
            </form>

            <?php elseif($action==='update'):?>
            <h2>Update Book Info</h2>

            <form method="POST">
                <p>
                <label>Book Title:</label>
                <br>
                <input  type="text"
                        name="book_title"
                        value="<?=htmlspecialchars($book['book_title']) ?>"
                        required
                />
                </p>
                <p>
                <label>Book Author:</label>
                <br>
                <input  type="text"
                        name="book_author"
                        value="<?=htmlspecialchars($book['book_author']) ?>"
                        required
                />
                </p>
                <p>
                <label>Book Category:</label>
                <br>
                <input  type="text"
                        name="book_category"
                        value="<?=htmlspecialchars($book['book_category']) ?>"
                        required
                />
                </p>

                <button type="submit">
                    Update
                </button>

                <a href="index.php?section=books">
                    Cancel
                </a>
            </form>
        <?php else: ?>
          <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Book Title</th>
                    <th>Book Author</th>
                    <th>Book Category</th>
                    <th>Create at</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($books as $book): ?>
                    <tr>
                        <td>
                            <?=htmlspecialchars($book['book_id']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($book['book_title']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($book['book_author']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($book['book_category']) ?>
                        </td>
                        <td>
                            <?=htmlspecialchars($book['book_created_at']) ?>
                        </td>
                        <td>
                            <a href="index.php?section=books&action=update&id=<?=$book['book_id']?>">
                                Edit
                            </a>

                            <a>Delete</a>
                        </td>
                    </tr>
                    <?php endforeach?>
            </tbody>
            </table>
        <?php endif;?>
    <?php endif; ?>

   <?php if($section === 'borrow'):?>
    <h1>Borrow</h1>
    <p>
        <a href="index.php?section=borrow&action=create">
            Borrow a Book
        </a>
    </p>

    <?php if ($action=='create'): ?>
        <h2>Borrow a Book</h2>

        <form method="POST">
            
                <label>Student:</label>
                <br>
                <select name="student_id" required>
                    <option value="">
                        -- Select Student --
                    </option>
                    <?php foreach($students as $student): ?>
                        <option value="<?=$student['student_id'] ?>">
                            <?=htmlspecialchars(
                            $student['student_first_name']
                             .' '. 
                            $student['student_last_name']
                            ) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <br>
            <br>
                <label>Book:</label>
                <br>
                <select name="book_id" required>
                    <option value="">
                        -- Select Book --
                    </option>
                    <?php foreach($books as $book): ?>
                        <option value="<?=($book['book_id']) ?> ">
                            <?= htmlspecialchars(
                                $book['book_title']
                                .'-'.
                                $book['book_author']
                            )?> 
                        </option>
                    <?php endforeach; ?>
                </select>
            <br>

            <button type="submit">
                Borrow
            </button>

            <a href="index.php?section=borrow">
                Cancel
            </a
        </form>

    <?php else: ?>
        <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Borrow ID</th>
                <th>Student</th>
                <th>Book</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
           <?php
             $borrows = $borrows ?? [];
                 foreach ($borrows as $br):
                                          ?>
                <tr>
                    <td><?=htmlspecialchars($br['borrow_id']) ?></td>
                    <td>
                        <?=htmlspecialchars($br['student_first_name'] . ' ' . $br['student_last_name']) ?>
                    </td>
                    <td>
                        <?=htmlspecialchars($br['book_title']) ?>
                    </td>
                    <td><?=htmlspecialchars($br['borrow_date']) ?></td>
                    <td>
                        <?=htmlspecialchars($br['borrow_return_date'] ?? 'Not returned') ?>
                    </td>
                    <td>
                        <?php if($br['borrow_return_date']): ?>
                            Returned
                        <?php else: ?>
                            Borrowed
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if(!$br['borrow_return_date']): ?>
                            <a href="index.php?section=borrow&action=return&id=<?=htmlspecialchars($br['borrow_id']) ?>">
                                Return
                            </a>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    <?php endif; ?>

<?php endif; ?>

</body>
    <?php if(isset($_SESSION['alert'])):?>
        <script>
            alert( <?=  json_encode($_SESSION['alert']) ?>);
        </script>
    <?php unset($_SESSION['alert']); ?>

    <?php endif; ?>
</html>