-- Users/Roles table for authentication
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'tester', 'manager') DEFAULT 'tester',
    department VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE products (
    product_id VARCHAR(10) PRIMARY KEY,
    product_code VARCHAR(20) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    product_revise VARCHAR(10),
    manufacturing_number VARCHAR(30),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Test types (modular structure)
CREATE TABLE test_types (
    test_id INT PRIMARY KEY AUTO_INCREMENT,
    test_code VARCHAR(20) UNIQUE NOT NULL,
    test_name VARCHAR(100) NOT NULL,
    department VARCHAR(50),
    description TEXT,
    parent_test_id INT NULL,
    FOREIGN KEY (parent_test_id) REFERENCES test_types(test_id)
);

-- Testing records main table
CREATE TABLE testing_records (
    testing_id VARCHAR(12) PRIMARY KEY,
    product_id VARCHAR(10) NOT NULL,
    test_type_id INT NOT NULL,
    testing_date DATE NOT NULL,
    result ENUM('Pass', 'Fail', 'Pending') DEFAULT 'Pending',
    status VARCHAR(50) DEFAULT 'In Progress',
    tested_by INT,
    remarks TEXT,
    output_observed TEXT,
    next_department VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (test_type_id) REFERENCES test_types(test_id),
    FOREIGN KEY (tested_by) REFERENCES users(user_id)
);

-- Test details with remarks
CREATE TABLE test_details (
    detail_id INT PRIMARY KEY AUTO_INCREMENT,
    testing_id VARCHAR(12) NOT NULL,
    test_criteria VARCHAR(255),
    expected_output TEXT,
    actual_output TEXT,
    remarks TEXT,
    tester_name VARCHAR(100),
    testing_time TIME,
    FOREIGN KEY (testing_id) REFERENCES testing_records(testing_id)
);

-- Product testing history
CREATE TABLE product_testing_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id VARCHAR(10) NOT NULL,
    testing_id VARCHAR(12) NOT NULL,
    action VARCHAR(50),
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT,
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    FOREIGN KEY (testing_id) REFERENCES testing_records(testing_id)
);