CREATE DATABASE IF NOT EXISTS bridgeonedb;
USE bridgeonedb;

-- =========================
-- ROOMS
-- =========================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hs_room_id INT NOT NULL,
    code VARCHAR(100) NOT NULL,
    name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_hs_room (hs_room_id)
);

-- =========================
-- RATE PLANS
-- =========================
CREATE TABLE rate_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hs_rate_plan_id INT NOT NULL,
    code VARCHAR(100) NOT NULL,
    meal_plan VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_rate_plan (hs_rate_plan_id)
);

-- =========================
-- RESERVATIONS
-- =========================
CREATE TABLE reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hs_reservation_id INT NOT NULL,
    guest_name VARCHAR(255),
    arrival_date DATE,
    departure_date DATE,
    status VARCHAR(50),
    lock_id VARCHAR(255),
    payload_hash VARCHAR(64),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reservation (hs_reservation_id)
);

-- =========================
-- AUDIT LOG
-- =========================
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    old_hash VARCHAR(64),
    new_hash VARCHAR(64),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- INVOICE QUEUE
-- =========================
CREATE TABLE invoice_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    payload JSON NOT NULL,
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- INVOICE COUNTERS
-- =========================
CREATE TABLE invoice_counters (
    year INT PRIMARY KEY,
    last_number INT DEFAULT 0
);

-- =========================
-- RESERVATION ROOMS (many-to-many)
-- =========================
CREATE TABLE reservation_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    room_id INT NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- =========================
-- RESERVATION RATE PLANS
-- =========================
CREATE TABLE reservation_rate_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    rate_plan_id INT NOT NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (rate_plan_id) REFERENCES rate_plans(id) ON DELETE CASCADE
);

-- =========================
-- INVOICE QUEUE
-- =========================
CREATE TABLE invoice_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE,
    reservation_id INT,
    payload JSON,
    status VARCHAR(50) DEFAULT 'pending',
    retry_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
);

-- =========================
-- WEBHOOK EVENTS
-- =========================
CREATE TABLE webhook_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100),
    payload_hash VARCHAR(64),
    payload JSON,
    processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_webhook (payload_hash)
);

-- =========================
-- INVOICE COUNTER (for safe numbering)
-- =========================
CREATE TABLE invoice_counter (
    year INT PRIMARY KEY,
    last_number INT NOT NULL
);

-- =========================
-- ROOMS
-- =========================
INSERT INTO rooms (hs_room_id, code, name) VALUES
(101, 'HS-101-deluxe-room', 'Deluxe Room'),
(102, 'HS-102-standard-room', 'Standard Room'),
(103, 'HS-103-family-suite', 'Family Suite'),
(104, 'HS-104-single-room', 'Single Room'),
(105, 'HS-105-double-room', 'Double Room'),
(106, 'HS-106-king-suite', 'King Suite'),
(107, 'HS-107-economy-room', 'Economy Room'),
(108, 'HS-108-luxury-suite', 'Luxury Suite'),
(109, 'HS-109-ocean-view', 'Ocean View Room'),
(110, 'HS-110-garden-room', 'Garden Room');

-- =========================
-- RATE PLANS
-- =========================
INSERT INTO rate_plans (hs_rate_plan_id, code, meal_plan) VALUES
(201, 'RP-201-room-only', 'Room Only'),
(202, 'RP-202-breakfast', 'Breakfast'),
(203, 'RP-203-half-board', 'Half Board'),
(204, 'RP-204-full-board', 'Full Board'),
(205, 'RP-205-all-inclusive', 'All Inclusive'),
(206, 'RP-206-business', 'Business'),
(207, 'RP-207-weekend', 'Weekend'),
(208, 'RP-208-family', 'Family'),
(209, 'RP-209-romantic', 'Romantic'),
(210, 'RP-210-long-stay', 'Long Stay');

-- =========================
-- RESERVATIONS
-- =========================
INSERT INTO reservations (hs_reservation_id, guest_name, arrival_date, departure_date, status, lock_id, payload_hash) VALUES
(3001, 'John Smith', '2026-01-02', '2026-01-05', 'confirmed', 'LOCK-3001-2026-01-02', 'hash3001'),
(3002, 'Emma Johnson', '2026-01-03', '2026-01-06', 'confirmed', 'LOCK-3002-2026-01-03', 'hash3002'),
(3003, 'Michael Brown', '2026-01-05', '2026-01-08', 'confirmed', 'LOCK-3003-2026-01-05', 'hash3003'),
(3004, 'Sophia Davis', '2026-01-07', '2026-01-09', 'confirmed', 'LOCK-3004-2026-01-07', 'hash3004'),
(3005, 'Daniel Wilson', '2026-01-10', '2026-01-12', 'cancelled', 'LOCK-3005-2026-01-10', 'hash3005'),
(3006, 'Olivia Taylor', '2026-01-11', '2026-01-15', 'confirmed', 'LOCK-3006-2026-01-11', 'hash3006'),
(3007, 'James Anderson', '2026-01-12', '2026-01-14', 'confirmed', 'LOCK-3007-2026-01-12', 'hash3007'),
(3008, 'Isabella Thomas', '2026-01-15', '2026-01-18', 'confirmed', 'LOCK-3008-2026-01-15', 'hash3008'),
(3009, 'William Jackson', '2026-01-18', '2026-01-21', 'confirmed', 'LOCK-3009-2026-01-18', 'hash3009'),
(3010, 'Ava White', '2026-01-20', '2026-01-22', 'confirmed', 'LOCK-3010-2026-01-20', 'hash3010');

-- =========================
-- RESERVATION ROOMS
-- =========================
INSERT INTO reservation_rooms (reservation_id, room_id, quantity) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2),
(4, 4, 1),
(5, 5, 1),
(6, 6, 1),
(7, 7, 1),
(8, 8, 1),
(9, 9, 1),
(10, 10, 1);

-- =========================
-- RESERVATION RATE PLANS
-- =========================
INSERT INTO reservation_rate_plans (reservation_id, rate_plan_id) VALUES
(1, 2),
(2, 1),
(3, 3),
(4, 2),
(5, 4),
(6, 5),
(7, 1),
(8, 6),
(9, 7),
(10, 8);

-- =========================
-- AUDIT LOG
-- =========================
INSERT INTO audit_log (reservation_id, event_type, description) VALUES
(1, 'created', 'Reservation created'),
(2, 'created', 'Reservation created'),
(3, 'created', 'Reservation created'),
(4, 'updated', 'Guest changed arrival date'),
(5, 'cancelled', 'Reservation cancelled by guest'),
(6, 'created', 'Reservation created'),
(7, 'updated', 'Room quantity updated'),
(8, 'created', 'Reservation created'),
(9, 'updated', 'Rate plan updated'),
(10, 'created', 'Reservation created');

-- =========================
-- INVOICE COUNTER
-- =========================
INSERT INTO invoice_counter (year, last_number) VALUES
(2026, 10);

-- =========================
-- INVOICE QUEUE
-- =========================
INSERT INTO invoice_queue (invoice_number, reservation_id, payload, status, retry_count) VALUES
('HS-INV-2026-000001', 1, '{"guest":"John Smith","total":450}', 'pending', 0),
('HS-INV-2026-000002', 2, '{"guest":"Emma Johnson","total":380}', 'sent', 0),
('HS-INV-2026-000003', 3, '{"guest":"Michael Brown","total":520}', 'pending', 1),
('HS-INV-2026-000004', 4, '{"guest":"Sophia Davis","total":310}', 'pending', 0),
('HS-INV-2026-000005', 5, '{"guest":"Daniel Wilson","total":290}', 'failed', 5),
('HS-INV-2026-000006', 6, '{"guest":"Olivia Taylor","total":720}', 'pending', 0),
('HS-INV-2026-000007', 7, '{"guest":"James Anderson","total":250}', 'sent', 0),
('HS-INV-2026-000008', 8, '{"guest":"Isabella Thomas","total":610}', 'pending', 2),
('HS-INV-2026-000009', 9, '{"guest":"William Jackson","total":470}', 'pending', 0),
('HS-INV-2026-000010', 10, '{"guest":"Ava White","total":340}', 'pending', 0);

-- =========================
-- WEBHOOK EVENTS
-- =========================
INSERT INTO webhook_events (event_type, payload_hash, payload, processed) VALUES
('reservation_created', 'whash1', '{"reservation_id":3001}', 1),
('reservation_updated', 'whash2', '{"reservation_id":3002}', 1),
('reservation_created', 'whash3', '{"reservation_id":3003}', 1),
('reservation_cancelled', 'whash4', '{"reservation_id":3005}', 1),
('reservation_created', 'whash5', '{"reservation_id":3006}', 0),
('reservation_updated', 'whash6', '{"reservation_id":3007}', 0),
('reservation_created', 'whash7', '{"reservation_id":3008}', 0),
('reservation_updated', 'whash8', '{"reservation_id":3009}', 0),
('reservation_created', 'whash9', '{"reservation_id":3010}', 0),
('reservation_updated', 'whash10', '{"reservation_id":3004}', 1);