-- Doorstep Service Booking Platform Database Schema

CREATE DATABASE IF NOT EXISTS doorstep_service_db;
USE doorstep_service_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    required_documents TEXT, -- JSON or comma separated string
    service_charge DECIMAL(10, 2) NOT NULL,
    estimated_days INT NOT NULL,
    service_type ENUM('Remote', 'In-Person Visit', 'Agent-Handled') DEFAULT 'In-Person Visit',
    icon_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Agents Table (Expanded for login and recruitment)
CREATE TABLE IF NOT EXISTS agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agent_name VARCHAR(100) NOT NULL,
    gender ENUM('Male', 'Female', 'Other') DEFAULT 'Male',
    phone VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) DEFAULT NULL, -- Nullable for recruitment phase
    address TEXT DEFAULT NULL,
    profile_summary TEXT DEFAULT NULL,
    status ENUM('pending', 'active', 'inactive', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    agent_id INT DEFAULT NULL,
    booking_date DATE NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    preferred_gender ENUM('Any', 'Male', 'Female') DEFAULT 'Any',
    status ENUM('Booking Placed', 'Agent Assigned', 'En Route', 'Arrived', 'Documents Collected', 'Application Submitted', 'Completed', 'Cancelled') DEFAULT 'Booking Placed',
    address_confirmed TEXT NOT NULL,
    dynamic_checklist TEXT DEFAULT NULL, -- JSON or comma separated
    user_doc_confirmed TINYINT(1) DEFAULT 0,
    agent_doc_confirmed TINYINT(1) DEFAULT 0,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agents(id) ON DELETE SET NULL
);

-- 5. Status Logs Table (For tracking history)
CREATE TABLE IF NOT EXISTS status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    remarks TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- 6. Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('admin', 'user', 'agent') NOT NULL,
    user_id INT DEFAULT NULL, -- NULL for admin notifications
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Settings Table
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value VARCHAR(255)
);

INSERT INTO settings (setting_key, setting_value) VALUES ('auto_approve_agents', '0');

-- Initial Admin Account (Password: admin123 - hashed version below)
-- Note: In real app, use password_hash(). Using a sample hash for 'admin123'
INSERT INTO users (full_name, email, phone, password, address, role) 
VALUES ('System Admin', 'admin@doorstep.com', '9876543210', '$2y$10$8Wv6pX1l2XyY6BqYyR1T9O6.u9k9s.oO8Wv6pX1l2XyY6BqYy', 'Head Office', 'admin');

-- Sample Services
INSERT INTO services (service_name, description, required_documents, service_charge, estimated_days, service_type) VALUES
('PAN Card Registration', 'New PAN card application or correction in existing one.', 'Aadhaar Card, Passport Size Photo, Signature', 250.00, 7, 'Agent-Handled'),
('Aadhaar Card Update', 'Update name, address, or mobile number in Aadhaar.', 'Aadhaar Card, Supporting Doc (Electricity bill/Passport)', 150.00, 10, 'In-Person Visit'),
('Ration Card', 'New ration card application or member addition.', 'Aadhaar Card of all family members, Income Certificate, Photo', 100.00, 15, 'Agent-Handled'),
('Community Certificate', 'Revenue Department service for community/caste certification.', 'Passport size photo,Address proof (Aadhaar / Voter ID),Parent or sibling community certificate,Self-declaration', 60.00, 5, 'Remote'),
('Nativity Certificate', 'Official proof of residence and origin.', 'Passport size photo,Address proof,Birth certificate or school certificate,Continuous residence self-declaration', 60.00, 5, 'Remote'),
('Income Certificate', 'Official certification of annual family income.', 'Passport size photo,Address proof,Income proof (salary slip / bank statement),Family card / smart card,Self-declaration', 60.00, 5, 'Remote'),
('First Graduate Certificate', 'Certification for first-generation graduates in a family.', 'Passport size photo,Address proof,Transfer certificate,Parent self-declaration,Current academic year certificate', 60.00, 7, 'Remote'),
('Deserted Woman Certificate', 'Certification for social security and welfare benefits.', 'Passport size photo,Address proof,Marriage certificate,Police / court declaration (if applicable),Self-declaration', 60.00, 7, 'Remote'),
('Agricultural Income Certificate', 'Certification of income derived from agricultural sources.', 'Passport size photo,Address proof,Adangal,Chitta,Self-declaration', 60.00, 7, 'Remote'),
('Family Migration Certificate', 'Proof of family relocation for administrative purposes.', 'Passport size photo,Address proof,Previous residence proof,Marriage certificate (if applicable),Self-declaration', 60.00, 10, 'Remote'),
('Unemployment Certificate', 'Proof of current unemployment status for benefits.', 'Passport size photo,Address proof,Educational qualification certificate,Family income certificate,Self-declaration', 60.00, 5, 'Remote'),
('Widow Certificate', 'Official certification for widow status and benefits.', 'Passport size photo,Address proof,Death certificate of husband,Marriage certificate,Self-declaration', 60.00, 5, 'Remote'),
('Certificate for Loss of Educational Records', 'Official document required for applying for duplicate educational records.', 'Passport size photo,Address proof,Police complaint copy,Self-declaration', 60.00, 7, 'Remote'),
('Inter-caste Marriage Certificate', 'Proof of marriage between different community members for government benefits.', 'Passport size photos (husband & wife),Marriage certificate,Community certificates of both,Self-declaration', 60.00, 7, 'Remote'),
('Legal Heir Certificate', 'Document identifying the legal heirs of a deceased person.', 'Death certificate of deceased,Address proof,Birth certificates of legal heirs,Relationship proof,Self-declaration', 60.00, 15, 'In-Person Visit'),
('OBC Certificate', 'Other Backward Classes certification for central government benefits.', 'Passport size photo,Address proof,Parent/sibling OBC certificate,Self-declaration', 60.00, 7, 'Remote'),
('Residence Certificate', 'Proof of current residing address.', 'Passport size photo,Proof of residence,Family card / ration card,Self-declaration', 60.00, 5, 'Remote'),
('Small / Marginal Farmer Certificate', 'Certification for agricultural subsidies and benefits.', 'Passport size photo,Address proof,Land ownership documents,Adangal,Self-declaration', 60.00, 7, 'Remote'),
('Solvency Certificate', 'Certification of financial stability for tenders and legal matters.', 'Passport size photo,Address proof,Bank statement,Property documents,Self-declaration', 60.00, 10, 'Remote'),
('No Male Child Certificate', 'Certification for specific welfare schemes.', 'Passport size photo,Address proof,Birth certificates of children,Self-declaration', 60.00, 7, 'Remote'),
('Unmarried Certificate', 'Proof of single marital status for official purposes.', 'Passport size photo,Address proof,Age proof,Self-declaration', 60.00, 5, 'Remote'),
('Licence under Pawn Broker Act', 'Official licence for operating as a pawn broker.', 'Passport size photo,Address proof,Shop ownership / rental agreement,Police verification,Self-declaration', 120.00, 20, 'Agent-Handled'),
('Money Lender’s Licence', 'Official licence for money lending business.', 'Passport size photo,Address proof,Business proof,Police verification,Self-declaration', 120.00, 20, 'Agent-Handled');

-- Sample Agents (Password: agent123)
INSERT INTO agents (agent_name, gender, phone, email, password, status, address, profile_summary) VALUES
('John Doe', 'Male', '9000011111', 'john@doorstep.com', '$2y$10$7R7iJ6N7Wq9qE3G6O3fV/.p3p2X9e8V9M.xR5N7Wq9qE3G6O3fV/', 'active', '123 Main St, Central City', 'Experienced field agent for Aadhaar and PAN services.'),
('Jane Smith', 'Female', '9000022222', 'jane@doorstep.com', '$2y$10$7R7iJ6N7Wq9qE3G6O3fV/.p3p2X9e8V9M.xR5N7Wq9qE3G6O3fV/', 'active', '456 Oak Rd, Springfield', 'Specialist in Ration card and Income certificate applications.');






http://localhost/project/foodapp/user/dashboard.php  
page la antha doc laam kattalaa and print doc laa mathtghanum