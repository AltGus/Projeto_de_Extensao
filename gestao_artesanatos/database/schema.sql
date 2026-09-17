CREATE TABLE users (
    active TINYINT(1) NOT NULL DEFAULT 1,
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('professor', 'aluno') NOT NULL DEFAULT 'aluno',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workshops (
    active TINYINT(1) NOT NULL DEFAULT 1,
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    responsible_user_id INT NULL,
    image_path VARCHAR(255) NULL,
    color VARCHAR(20) NOT NULL DEFAULT '#4f46e5',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_workshops_responsible FOREIGN KEY (responsible_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE students (
    last_work_number INT NOT NULL DEFAULT 0,
    id INT AUTO_INCREMENT PRIMARY KEY,
    active TINYINT(1) NOT NULL DEFAULT 1,
    full_name VARCHAR(160) NOT NULL,
    guardian_name VARCHAR(200) NOT NULL,
    father_phone VARCHAR(30) NULL,
    mother_phone VARCHAR(30) NULL,
    notes TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_students_name (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE workshop_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    workshop_id INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY uq_workshop_user (workshop_id, user_id),

    CONSTRAINT fk_workshop_user_workshop
        FOREIGN KEY (workshop_id)
        REFERENCES workshops(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_workshop_user_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE student_workshop (
    student_id INT NOT NULL,
    workshop_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (student_id, workshop_id),
    CONSTRAINT fk_student_workshop_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_student_workshop_workshop FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
    active TINYINT(1) NOT NULL DEFAULT 1,
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(80) NOT NULL,
    workshop_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_products_workshop
        FOREIGN KEY (workshop_id)
        REFERENCES workshops(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE productions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    workshop_id INT NOT NULL,
    student_id INT NULL,
    work_number INT NULL,
    availability_status ENUM('disponivel','indisponivel') NOT NULL DEFAULT 'disponivel',
    quantity INT NOT NULL,
    produced_at DATE NOT NULL,
    responsible_user_id INT NULL,
    purpose VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_productions_product
        FOREIGN KEY (product_id)
        REFERENCES products(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_productions_workshop
        FOREIGN KEY (workshop_id)
        REFERENCES workshops(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_productions_student
        FOREIGN KEY (student_id)
        REFERENCES students(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    UNIQUE KEY uq_student_work_number (student_id, work_number),

    CONSTRAINT fk_productions_user
        FOREIGN KEY (responsible_user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    production_id INT NOT NULL,
    cycle_number INT NOT NULL,
    delivered_at DATETIME NOT NULL,
    delivered_by_user_id INT NULL,
    notes TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_delivery_production (production_id),
    UNIQUE KEY uq_delivery_cycle (student_id, cycle_number),
    CONSTRAINT fk_deliveries_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_deliveries_production FOREIGN KEY (production_id) REFERENCES productions(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_deliveries_user FOREIGN KEY (delivered_by_user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE materials (
    active TINYINT(1) NOT NULL DEFAULT 1,
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    category VARCHAR(80) NOT NULL,
    unit VARCHAR(20) NOT NULL DEFAULT 'un',
    quantity_mode ENUM('integer','decimal') NOT NULL DEFAULT 'decimal',
    applies_to_all TINYINT(1) NOT NULL DEFAULT 1,
    current_quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    min_quantity DECIMAL(12,3) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE stock_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    material_id INT NOT NULL,
    movement_type ENUM('entrada', 'saida') NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    notes TEXT NOT NULL,
    movement_date DATE NOT NULL,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_stock_material
        FOREIGN KEY (material_id)
        REFERENCES materials(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,

    CONSTRAINT fk_stock_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_activity_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE material_workshop (
    material_id INT NOT NULL,
    workshop_id INT NOT NULL,
    PRIMARY KEY (material_id, workshop_id),
    FOREIGN KEY (material_id) REFERENCES materials(id) ON DELETE CASCADE,
    FOREIGN KEY (workshop_id) REFERENCES workshops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
