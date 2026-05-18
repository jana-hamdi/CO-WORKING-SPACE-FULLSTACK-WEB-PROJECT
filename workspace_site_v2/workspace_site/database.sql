CREATE DATABASE IF NOT EXISTS workspace_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE workspace_db;

CREATE TABLE IF NOT EXISTS rooms (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  type        ENUM('Individual','Group') NOT NULL,
  capacity    INT NOT NULL,
  base_price  DECIMAL(10,2) NOT NULL,
  description TEXT,
  amenities   VARCHAR(255),
  is_active   TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS individual_bookings (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  room_id         INT NOT NULL,
  room_type       VARCHAR(20),
  customer_name   VARCHAR(100) NOT NULL,
  customer_phone  VARCHAR(20) NOT NULL,
  customer_email  VARCHAR(100),
  scheduled_start DATETIME NOT NULL,
  status          ENUM('Pending','Confirmed','CheckedIn','Completed','Cancelled') DEFAULT 'Pending',
  notes           TEXT,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE IF NOT EXISTS group_bookings (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  room_id             INT NOT NULL,
  room_type           VARCHAR(20),
  customer_name       VARCHAR(100) NOT NULL,
  customer_phone      VARCHAR(20) NOT NULL,
  customer_email      VARCHAR(100),
  scheduled_start     DATETIME NOT NULL,
  scheduled_end       DATETIME NOT NULL,
  expected_attendees  INT NOT NULL DEFAULT 1,
  deposit_amount      DECIMAL(10,2),
  payment_card        VARCHAR(100),
  status              ENUM('Pending','Confirmed','CheckedIn','Completed','Cancelled') DEFAULT 'Pending',
  notes               TEXT,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES rooms(id)
);

CREATE TABLE IF NOT EXISTS products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  category    VARCHAR(60),
  price       DECIMAL(10,2) NOT NULL,
  stock       INT DEFAULT 0,
  description TEXT,
  is_active   TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  customer_name    VARCHAR(100) NOT NULL,
  customer_phone   VARCHAR(20) NOT NULL,
  customer_address VARCHAR(255),
  payment_method   ENUM('Cash','Credit Card','InstaPay') DEFAULT 'Cash',
  total            DECIMAL(10,2),
  status           ENUM('Pending','Processing','Delivered') DEFAULT 'Pending',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_id   INT NOT NULL,
  product_id INT NOT NULL,
  quantity   INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS contacts (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL,
  subject    VARCHAR(200),
  message    TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO rooms (id,name,type,capacity,base_price,description,amenities) VALUES
(1,'Quiet Room','Individual',10,25.00,'A calm, distraction-free space with high-speed Wi-Fi and ergonomic seating.','Wi-Fi, Air Conditioning, Tables & Chairs'),
(2,'Gaming Room','Individual',8,35.00,'Cozy setup with LED ambiance and PlayStation consoles.','Cozy Chairs, LED Lights, PlayStation Console'),
(3,'Meeting Room','Group',12,80.00,'Professional boardroom with projector and printer.','Projector, Printer, Wi-Fi'),
(4,'Discussion Room','Group',8,60.00,'Creative collaboration space with a full whiteboard wall.','Whiteboard, Wi-Fi, Marker Set');

INSERT IGNORE INTO products (id,name,category,price,stock,description) VALUES
(1,'Premium Coffee','Beverages',45.00,50,'Freshly brewed specialty coffee, single origin.'),
(2,'Green Tea','Beverages',30.00,40,'Organic green tea, calming and refreshing.'),
(3,'Notebook A5','Stationery',55.00,30,'Hardcover dotted notebook, 200 pages.'),
(4,'Mechanical Pencil','Stationery',25.00,60,'0.5mm precise mechanical pencil.'),
(5,'USB-C Hub','Tech',180.00,15,'7-in-1 USB-C hub with HDMI and SD card.'),
(6,'Protein Bar','Snacks',40.00,45,'High-protein energy bar, chocolate fudge.');
