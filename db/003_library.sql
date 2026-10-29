CREATE TABLE IF NOT EXISTS students (
    -- Primary key for students table
    student_id INT AUTO_INCREMENT PRIMARY KEY,


    -- Student Name
    student_first_name VARCHAR(50) NOT NULL,
    student_last_name VARCHAR(50) NOT NULL,

    -- Student Course
    student_course VARCHAR(50) NOT NULL,

    -- Student created at timestamp  
    student_created_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

  -- #2 Books Table

  CREATE TABLE IF NOT EXISTS books (
    -- Primary key for books table
    book_id INT AUTO_INCREMENT PRIMARY KEY,

    -- Book Details
    book_title VARCHAR(50) NOT NULL,
    book_author VARCHAR(100) NOT NULL,
    book_category VARCHAR(50) NOT NULL,


    -- Book created at timestamp
    book_created_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

-- #3 Borrow Table
CREATE TABLE IF NOT EXISTS borrow (
    -- Primary key for borrow table
    borrow_id INT AUTO_INCREMENT PRIMARY KEY,

    -- Foreign Key
    student_id INT NOT NULL,
    book_id INT NOT NULL,

    borrow_date TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    borrow_return_date TIMESTAMP NULL
        DEFAULT NULL,

    CONSTRAINT fk_borrow_student
        FOREIGN KEY (student_id)
        REFERENCES students(student_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_borrow_book
        FOREIGN KEY (book_id)
        REFERENCES books(book_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

-- This is where the relationships are established.

INSERT INTO students (
    student_first_name,
    student_last_name,
    student_course
) VALUES
    ('Mikko Louise', 'Pelvera', 'BSIT'),
    ('Mikko', 'Pelvera', 'BSBA'),
    ('Louise', 'Pelvera', 'BSIT'),
    ('Dudei', 'Pelvera', 'BSCE');

    INSERT INTO books (
    book_title,
    book_author,
    book_category
) VALUES
    ('Solo Leveling', 'Chu-Gong', 'SAction Fantasyn'),
    ('AOT', 'Jayboy', 'Action Fantasy'),
    ('Bleach', 'Mikko', 'Science Fiction');

    INSERT INTO borrow (
    student_id,
    book_id
) VALUES
    (1, 2),
    (2, 1),
    (3, 3);