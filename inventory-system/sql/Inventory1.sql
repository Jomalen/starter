CREATE DATABASE Inventory1;
USE Inventory1;

CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	username VARCHAR(50) NOT NULL UNIQUE,
	password VARCHAR(255) NOT NULL,
	full_name VARCHAR(100),
	address VARCHAR(255),
	phone VARCHAR(20),
	email VARCHAR(100),
	position VARCHAR(100),
	office VARCHAR(100),
	role ENUM('admin', 'user') DEFAULT 'user',
	profile_pic VARCHAR(255) DEFAULT 'assets/img/default-avatar.jpg',
	theme ENUM('light', 'dark') DEFAULT 'light',
	is_active BOOLEAN DEFAULT TRUE,
	status VARCHAR(50) DEFAULT 'Pending',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
	);
	
-- Table: items
CREATE TABLE items (
id INT AUTO_INCREMENT PRIMARY KEY,
model VARCHAR(100),
serial_number VARCHAR(100),
property_number VARCHAR(100),
operating_system VARCHAR(100),
brand VARCHAR(100),
memory VARCHAR(100),
end_user VARCHAR(100),
location VARCHAR(100),
property_category VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: user_request
CREATE TABLE user_request (
id INT AUTO_INCREMENT PRIMARY KEY,
request_date DATE NOT NULL,
request_type VARCHAR(100),
other_request_details TEXT,
serial_property_number VARCHAR(100),
requested_by VARCHAR(100),
requesting_office VARCHAR(100),
approved_by VARCHAR(100),
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: maintenance_request
CREATE TABLE maintenance_request (
id INT AUTO_INCREMENT PRIMARY KEY,
date_requested DATE,
request_type VARCHAR(100),
other_details TEXT,
description TEXT,
serial_property_number VARCHAR(100),
requested_by VARCHAR(100),
requesting_office VARCHAR(100),
approved_by VARCHAR(100),
date_received DATE,
pre_maintenance_eval TEXT,
inspected_by VARCHAR(100),
inspection_date DATE,
corrective_action TEXT,
result TEXT,
recommendation TEXT,
accomplished_by VARCHAR(100),
date_accomplished DATE,
service_degree VARCHAR(100),
for_disposal BOOLEAN DEFAULT FALSE,
disposal_type VARCHAR(100),
disposal_equipment_type VARCHAR(100),
disposal_property_no VARCHAR(100),
disposal_serial_no VARCHAR(100),
disposal_confirmed_by VARCHAR(100),
disposal_accepted_by VARCHAR(100),
received_by VARCHAR(100),
received_date DATE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: activity_log
CREATE TABLE activity_log (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
activity TEXT NOT NULL,
logtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

ALTER TABLE items ADD COLUMN description VARCHAR(255);