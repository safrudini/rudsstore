CREATE DATABASE IF NOT EXISTS ruds_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ruds_store;

-- Table untuk admin
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table untuk reseller
CREATE TABLE resellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    saldo DECIMAL(15,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table untuk produk
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_unik VARCHAR(50) NOT NULL UNIQUE,
    nama_produk VARCHAR(255) NOT NULL,
    keterangan TEXT,
    harga DECIMAL(15,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table untuk order
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NOT NULL,
    product_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    harga DECIMAL(15,2) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    trxid VARCHAR(255),
    reffid VARCHAR(255) NOT NULL UNIQUE,
    response_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Table untuk topup
CREATE TABLE topups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NOT NULL,
    jumlah DECIMAL(15,2) NOT NULL,
    bukti_transfer VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id)
);

-- Insert admin default
INSERT INTO admins (email, password) VALUES ('rudsprjct@gmail.com', '$2y$10$rS3nD7kKX6LcV8mW2pZJf.9bV1cD3eF5gH7iJ9lL1nO4qR2sT6uV8w');

-- Insert contoh produk
INSERT INTO products (kode_unik, nama_produk, keterangan, harga) VALUES
('BPAL11', 'Kuota XL 11 Hari', 'Paket internet XL 11 hari dengan kuota besar', 65000),
('BPAXXL19', 'Kuota AXIS 19 Hari', 'Paket internet AXIS 19 hari dengan kuota malam', 55000),
('BPXL30', 'Kuota XL 30 Hari', 'Paket internet XL 30 hari full kuota', 120000);