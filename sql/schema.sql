-- ============================================
-- CampusNexus — Complete Database Schema
-- Unified Smart Campus Ecosystem
-- ============================================

-- Note: For shared hosting, database must be created via control panel
-- CREATE DATABASE IF NOT EXISTS campusnexus;
-- USE campusnexus;

-- ============================================
-- 1. USERS TABLE
-- ============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'faculty', 'admin') NOT NULL DEFAULT 'student',
    department VARCHAR(100) DEFAULT 'General',
    avatar VARCHAR(255) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. ATTENDANCE SESSIONS TABLE
-- ============================================
CREATE TABLE attendance_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    subject VARCHAR(150) NOT NULL,
    session_code VARCHAR(10) NOT NULL UNIQUE,
    start_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    end_time DATETIME NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (faculty_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_code (session_code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. ATTENDANCE RECORDS TABLE
-- ============================================
CREATE TABLE attendance_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    student_id INT NOT NULL,
    marked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES attendance_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (session_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. RESOURCES TABLE
-- ============================================
CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uploaded_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    subject VARCHAR(150) NOT NULL,
    semester VARCHAR(20) DEFAULT NULL,
    type ENUM('notes', 'slides', 'paper', 'assignment', 'other') NOT NULL DEFAULT 'notes',
    file_path VARCHAR(255) NOT NULL,
    file_size INT DEFAULT 0,
    download_count INT NOT NULL DEFAULT 0,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    rating_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_subject (subject),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. RESOURCE RATINGS TABLE
-- ============================================
CREATE TABLE resource_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (resource_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 6. GRIEVANCES TABLE
-- ============================================
CREATE TABLE grievances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('infrastructure', 'it', 'hygiene', 'safety', 'electrical', 'academic', 'other') NOT NULL,
    location VARCHAR(200) NOT NULL,
    priority ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    image_path VARCHAR(255) DEFAULT NULL,
    upvotes INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 7. GRIEVANCE COMMENTS TABLE
-- ============================================
CREATE TABLE grievance_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grievance_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grievance_id) REFERENCES grievances(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 8. MARKETPLACE LISTINGS TABLE
-- ============================================
CREATE TABLE marketplace_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    category ENUM('books', 'electronics', 'furniture', 'clothing', 'other') NOT NULL,
    condition_status ENUM('new', 'like_new', 'good', 'fair') NOT NULL DEFAULT 'good',
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'sold', 'removed') NOT NULL DEFAULT 'active',
    is_approved TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_category (category),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 9. EVENTS TABLE
-- ============================================
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('technical', 'cultural', 'sports', 'workshop', 'seminar') NOT NULL,
    venue VARCHAR(200) NOT NULL,
    event_date DATETIME NOT NULL,
    capacity INT NOT NULL DEFAULT 100,
    registered_count INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_date (event_date),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 10. EVENT REGISTRATIONS TABLE
-- ============================================
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 11. LOST & FOUND TABLE
-- ============================================
CREATE TABLE lost_found (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    type ENUM('lost', 'found') NOT NULL,
    category ENUM('electronics', 'documents', 'accessories', 'clothing', 'other') NOT NULL,
    location VARCHAR(200) NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    status ENUM('open', 'claimed', 'returned') NOT NULL DEFAULT 'open',
    item_date DATE NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_type (type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 12. MESS MENU TABLE
-- ============================================
CREATE TABLE mess_menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    meal ENUM('breakfast', 'lunch', 'snacks', 'dinner') NOT NULL,
    items TEXT NOT NULL,
    admin_id INT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_menu (day, meal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 13. MESS RATINGS TABLE
-- ============================================
CREATE TABLE mess_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    meal ENUM('breakfast', 'lunch', 'snacks', 'dinner') NOT NULL,
    rating_date DATE NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    feedback TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_meal_rating (user_id, meal, rating_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 14. ANNOUNCEMENTS TABLE
-- ============================================
CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    posted_by INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    priority ENUM('normal', 'important', 'urgent') NOT NULL DEFAULT 'normal',
    department VARCHAR(100) DEFAULT 'General',
    attachment_path VARCHAR(255) DEFAULT NULL,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_priority (priority),
    INDEX idx_department (department)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 15. CONTACT MESSAGES TABLE
-- ============================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 16. GRIEVANCE UPVOTES TABLE
-- ============================================
CREATE TABLE grievance_upvotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grievance_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grievance_id) REFERENCES grievances(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_upvote (grievance_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
-- SAMPLE DATA
-- ============================================

-- Admin user (password: admin123)
INSERT INTO users (name, email, password, role, department, bio) VALUES
('Admin User', 'admin@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Administration', 'System Administrator of CampusNexus'),
-- Faculty (password: faculty123)  
('Dr. Priya Sharma', 'priya@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'Computer Science', 'Associate Professor - Data Structures & Algorithms'),
('Prof. Rajesh Kumar', 'rajesh@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'faculty', 'Electronics', 'Professor - Digital Electronics'),
-- Students (password: student123)
('Arjun Mehta', 'arjun@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Computer Science', 'CSE 3rd Year - Web Technologies enthusiast'),
('Sneha Reddy', 'sneha@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Computer Science', 'CSE 2nd Year - Full Stack Developer'),
('Vikram Singh', 'vikram@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Electronics', 'ECE 4th Year - IoT Researcher'),
('Ananya Patel', 'ananya@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Mechanical', 'ME 2nd Year - CAD/CAM Specialist'),
('Rohan Joshi', 'rohan@campusnexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Computer Science', 'CSE 1st Year - Learning to code');

-- Sample Grievances
INSERT INTO grievances (user_id, title, description, category, location, priority, status, upvotes) VALUES
(4, 'Broken ceiling fan in Room 301', 'The ceiling fan in Room 301 of the CS block has been making grinding noises for a week and has now completely stopped working. With summer approaching, this needs urgent attention as 60+ students attend lectures here daily.', 'infrastructure', 'CS Block - Room 301', 'high', 'open', 12),
(5, 'Wi-Fi dead zone in Library 2nd Floor', 'The entire second floor of the central library has extremely poor Wi-Fi connectivity. Students cannot access online resources or submit assignments. This has been an issue for over 2 weeks.', 'it', 'Central Library - 2nd Floor', 'critical', 'in_progress', 34),
(6, 'Water leakage in Boys Hostel Block C', 'There is a persistent water leak from the ceiling of Room 204 in Boys Hostel Block C. The leak worsens during rain and has damaged personal belongings of students living there.', 'infrastructure', 'Boys Hostel - Block C, Room 204', 'high', 'open', 8),
(7, 'Unhygienic washrooms near Cafeteria', 'The washrooms adjacent to the main cafeteria are consistently dirty and lack basic supplies like soap and paper towels. Despite multiple verbal complaints, no action has been taken.', 'hygiene', 'Main Cafeteria - Ground Floor', 'medium', 'open', 22),
(4, 'Broken projector in Seminar Hall', 'The projector in the main seminar hall has not been working for the past 3 days. Multiple presentations and guest lectures have been affected. Temporary arrangements are inadequate.', 'it', 'Seminar Hall - Admin Block', 'high', 'resolved', 15),
(8, 'Flickering lights in ECE Lab', 'Several tube lights in the Digital Electronics Lab (ECE Block) are flickering constantly. This is causing eye strain during long lab sessions and could be a safety hazard.', 'electrical', 'ECE Block - Digital Lab', 'medium', 'in_progress', 6);

-- Sample Grievance Comments
INSERT INTO grievance_comments (grievance_id, user_id, comment) VALUES
(1, 5, 'Same issue in Room 305 as well. Please check all fans in the CS block.'),
(1, 1, 'Maintenance team has been notified. Expected resolution by end of this week.'),
(2, 4, 'This is really affecting our research work. We need a permanent fix, not just a restart of routers.'),
(2, 2, 'I have escalated this to the IT department head. They are working on adding more access points.'),
(4, 6, 'The washrooms near the sports complex are also in bad condition.');

-- Sample Marketplace Listings
INSERT INTO marketplace_listings (seller_id, title, description, price, category, condition_status, status, is_approved) VALUES
(4, 'Data Structures & Algorithms Textbook - Cormen', 'CLRS 3rd Edition in excellent condition. Used for one semester only. No markings or highlights. Original price was ₹650.', 350.00, 'books', 'like_new', 'active', 1),
(5, 'HP Scientific Calculator', 'HP 35s Scientific Calculator. Works perfectly. Including original case and manual. Battery recently replaced.', 800.00, 'electronics', 'good', 'active', 1),
(6, 'Study Table with Drawer', 'Wooden study table with one drawer. Sturdy and in good condition. Selling because of hostel room change. Buyer needs to pick up from Block C.', 1200.00, 'furniture', 'good', 'active', 1),
(7, 'Engineering Drawing Kit Complete', 'Full engineering drawing kit with compass, divider, set squares, protractor, and French curves. Used for 1st year ED course only.', 250.00, 'other', 'like_new', 'active', 1),
(8, 'Operating Systems by Galvin - 9th Edition', 'OS textbook by Silberschatz, Galvin & Gagne. Some highlighting in early chapters but otherwise in good condition.', 200.00, 'books', 'fair', 'active', 1);

-- Sample Events
INSERT INTO events (created_by, title, description, category, venue, event_date, capacity, registered_count) VALUES
(2, 'TechFest 2026 - Annual Technical Festival', 'Join us for the biggest tech event of the year! Featuring coding competitions, hackathons, robotics challenges, and tech talks by industry leaders. Prizes worth ₹5,00,000!', 'technical', 'Main Auditorium & CS Block', '2026-04-20 09:00:00', 500, 234),
(3, 'Workshop: Introduction to IoT with Arduino', 'Hands-on workshop on building IoT projects using Arduino and various sensors. Bring your own laptop. Arduino kits will be provided. Limited seats!', 'workshop', 'ECE Lab - Room 105', '2026-04-12 14:00:00', 40, 35),
(2, 'Guest Lecture: AI in Healthcare', 'Distinguished lecture by Dr. Sarah Chen from Google DeepMind on applications of Artificial Intelligence in modern healthcare diagnostics and drug discovery.', 'seminar', 'Seminar Hall', '2026-04-15 11:00:00', 200, 145),
(1, 'Annual Sports Meet 2026', 'Three-day inter-department sports competition featuring cricket, football, basketball, badminton, athletics, and chess. Register your team now!', 'sports', 'University Sports Complex', '2026-04-25 07:00:00', 1000, 456),
(3, 'Cultural Night - Rhythms of India', 'An evening of music, dance, drama, and art celebrating the diverse cultural heritage of India. Open mic segment available for all students.', 'cultural', 'Open Air Theatre', '2026-04-18 18:00:00', 800, 320);

-- Sample Event Registrations
INSERT INTO event_registrations (event_id, user_id) VALUES
(1, 4), (1, 5), (1, 6), (1, 7), (1, 8),
(2, 4), (2, 6),
(3, 5), (3, 7),
(4, 4), (4, 5), (4, 6), (4, 7), (4, 8),
(5, 5), (5, 7), (5, 8);

-- Sample Lost & Found
INSERT INTO lost_found (user_id, title, description, type, category, location, status, item_date) VALUES
(4, 'Lost: Blue JBL Earbuds', 'Lost my blue JBL Tune 230NC earbuds somewhere between the CS block and the library. They were in a small black case. Please contact if found!', 'lost', 'electronics', 'CS Block to Library path', 'open', '2026-04-02'),
(5, 'Found: Student ID Card', 'Found a student ID card near the cafeteria entrance. Name on card starts with "R". Owner can claim by describing the full name and department.', 'found', 'documents', 'Main Cafeteria Entrance', 'open', '2026-04-03'),
(6, 'Lost: Black Leather Wallet', 'Lost my black leather wallet in the ECE building washroom. Contains college ID and some cash. No questions asked if returned.', 'lost', 'accessories', 'ECE Block - 2nd Floor Washroom', 'open', '2026-04-01'),
(7, 'Found: Silver Watch', 'Found a silver analog watch on the bench near the basketball court. Has a small scratch on the back. Claim with description.', 'found', 'accessories', 'Sports Complex - Basketball Court', 'claimed', '2026-03-30'),
(8, 'Lost: USB Drive with Project Files', 'Lost a 32GB SanDisk USB drive somewhere in the computer lab. Contains important final year project files. Label says "Rohan - FYP". Urgent!', 'lost', 'electronics', 'CS Block - Computer Lab 2', 'open', '2026-04-03');

-- Sample Mess Menu
INSERT INTO mess_menu (day, meal, items) VALUES
('monday', 'breakfast', 'Poha, Bread & Butter, Boiled Eggs, Tea/Coffee, Milk, Banana'),
('monday', 'lunch', 'Rice, Dal Tadka, Paneer Butter Masala, Roti, Salad, Raita, Gulab Jamun'),
('monday', 'snacks', 'Samosa, Green Chutney, Tea/Coffee'),
('monday', 'dinner', 'Rice, Rajma Masala, Mixed Veg, Roti, Salad, Ice Cream'),
('tuesday', 'breakfast', 'Idli Sambhar, Chutney, Boiled Eggs, Tea/Coffee, Milk, Apple'),
('tuesday', 'lunch', 'Rice, Chole, Aloo Gobi, Roti, Salad, Buttermilk, Kheer'),
('tuesday', 'snacks', 'Bread Pakora, Ketchup, Tea/Coffee'),
('tuesday', 'dinner', 'Rice, Dal Makhani, Egg Curry / Mushroom, Roti, Salad, Fruit Custard'),
('wednesday', 'breakfast', 'Upma, Toast & Jam, Omelette, Tea/Coffee, Milk, Orange'),
('wednesday', 'lunch', 'Rice, Arhar Dal, Chicken Curry / Soya Chunks, Roti, Salad, Raita, Jalebi'),
('wednesday', 'snacks', 'Vada Pav, Chutney, Tea/Coffee'),
('wednesday', 'dinner', 'Rice, Kadhi Pakora, Bhindi Masala, Roti, Salad, Halwa'),
('thursday', 'breakfast', 'Aloo Paratha, Curd, Pickle, Boiled Eggs, Tea/Coffee, Milk, Banana'),
('thursday', 'lunch', 'Rice, Moong Dal, Matar Paneer, Roti, Salad, Lassi, Rasmalai'),
('thursday', 'snacks', 'Maggi, Tea/Coffee'),
('thursday', 'dinner', 'Rice, Chana Dal, Fish Fry / Veg Kolhapuri, Roti, Salad, Phirni'),
('friday', 'breakfast', 'Chole Bhature, Boiled Eggs, Tea/Coffee, Milk, Apple'),
('friday', 'lunch', 'Biryani (Veg/Non-Veg), Raita, Salad, Mirchi Ka Salan, Gulab Jamun'),
('friday', 'snacks', 'Pav Bhaji, Tea/Coffee'),
('friday', 'dinner', 'Rice, Yellow Dal, Palak Paneer, Roti, Salad, Gajar Ka Halwa'),
('saturday', 'breakfast', 'Dosa, Sambhar, Chutney, Boiled Eggs, Tea/Coffee, Milk, Banana'),
('saturday', 'lunch', 'Rice, Toor Dal, Butter Chicken / Paneer Tikka, Roti, Salad, Raita, Cake'),
('saturday', 'snacks', 'Aloo Tikki, Green Chutney, Tea/Coffee'),
('saturday', 'dinner', 'Rice, Mix Dal, Egg Bhurji / Veg Manchurian, Roti, Salad, Fruit Salad'),
('sunday', 'breakfast', 'Puri Sabji, Boiled Eggs, Tea/Coffee, Milk, Mixed Fruit'),
('sunday', 'lunch', 'Rice, Special Dal, Mutton Curry / Shahi Paneer, Roti, Salad, Raita, Sweet'),
('sunday', 'snacks', 'Pasta, Cold Drink'),
('sunday', 'dinner', 'Rice, Dal Fry, Aloo Matar, Roti, Salad, Ice Cream');

-- Sample Mess Ratings
INSERT INTO mess_ratings (user_id, meal, rating_date, rating, feedback) VALUES
(4, 'breakfast', '2026-04-04', 4, 'Poha was good today!'),
(4, 'lunch', '2026-04-04', 3, 'Dal was too watery, paneer was okay'),
(5, 'breakfast', '2026-04-04', 5, 'Loved the poha and tea!'),
(5, 'lunch', '2026-04-04', 2, 'Rice was undercooked'),
(6, 'lunch', '2026-04-04', 4, 'Pretty decent meal'),
(7, 'dinner', '2026-04-03', 1, 'Ice cream was melted, very disappointed'),
(8, 'lunch', '2026-04-04', 3, 'Average food');

-- Sample Announcements
INSERT INTO announcements (posted_by, title, content, priority, department, is_pinned) VALUES
(1, 'Mid-Semester Examination Schedule Released', 'The mid-semester examination schedule for all departments has been released. Exams will commence from April 28, 2026. Students are advised to check the detailed timetable on the university portal and prepare accordingly. Any schedule conflicts must be reported to the exam cell within 3 working days.', 'urgent', 'General', 1),
(2, 'Web Technologies Lab Assignment 5 Due Date Extended', 'The due date for Lab Assignment 5 (PHP & MySQL Integration) has been extended to April 10, 2026. Students who have already submitted need not resubmit. Late submissions after the new deadline will not be accepted.', 'important', 'Computer Science', 0),
(1, 'Library Hours Extended During Exam Period', 'The Central Library will remain open from 7:00 AM to 11:00 PM during the examination period (April 15 - May 10). The reading room on the 3rd floor will be open 24/7 for registered students.', 'normal', 'General', 1),
(3, 'ECE Department - Guest Lecture on 5G Technology', 'The ECE Department is organizing a guest lecture on "5G Technology and Beyond" by Dr. Amit Verma from Qualcomm India. Date: April 16, 2026 at 3:00 PM in the ECE Seminar Hall. All ECE students are expected to attend.', 'important', 'Electronics', 0),
(1, 'Hostel Mess Committee Meeting', 'A meeting of the Hostel Mess Committee will be held on April 8, 2026 at 5:00 PM in the Conference Room. All mess representatives from each hostel block are required to attend. Agenda includes menu revision and hygiene improvements.', 'normal', 'General', 0);

-- Sample Contact Messages
INSERT INTO contact_messages (name, email, subject, message) VALUES
('Amit Kumar', 'amit@student.edu', 'Feature Request: Dark Mode', 'It would be great if CampusNexus had a dark mode option. Many students use the platform late at night and the bright screen is harsh on the eyes.'),
('Parent - Mrs. Gupta', 'mrsgupta@email.com', 'Student Safety Concern', 'I am writing as a parent to express concern about the safety of students in the hostel area. My son mentioned that streetlights near Block D have not been working for weeks.');

-- Sample Resources
INSERT INTO resources (uploaded_by, title, description, subject, semester, type, file_path, download_count, avg_rating, rating_count) VALUES
(2, 'Data Structures Complete Notes', 'Comprehensive notes covering Arrays, Linked Lists, Stacks, Queues, Trees, Graphs, Sorting, and Searching algorithms with examples and complexity analysis.', 'Data Structures', '3rd Sem', 'notes', 'uploads/resources/ds_notes.pdf', 156, 4.50, 24),
(2, 'Web Technologies - PHP & MySQL Slides', 'Lecture slides covering PHP fundamentals, MySQL database operations, CRUD implementation, and session management with practical examples.', 'Web Technologies', '6th Sem', 'slides', 'uploads/resources/web_tech_slides.pdf', 89, 4.20, 15),
(3, 'Digital Electronics Previous Year Papers 2020-2025', 'Collection of previous year mid-semester and end-semester exam papers for Digital Electronics with solutions for selected questions.', 'Digital Electronics', '4th Sem', 'paper', 'uploads/resources/de_papers.pdf', 234, 4.80, 42),
(2, 'DBMS Assignment 3 - ER Diagrams', 'Assignment on Entity-Relationship diagrams. Design ER diagrams for given scenarios. Submission deadline: April 15, 2026.', 'Database Management', '5th Sem', 'assignment', 'uploads/resources/dbms_assignment3.pdf', 67, 3.90, 8),
(3, 'Signals & Systems Formula Sheet', 'Quick reference formula sheet for Signals and Systems covering Fourier Transform, Laplace Transform, Z-Transform, and their properties.', 'Signals & Systems', '4th Sem', 'notes', 'uploads/resources/signals_formulas.pdf', 312, 4.70, 53);
