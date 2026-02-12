-- ============================================
-- HealthyLife Hospital - Seed Data
-- 20 sample users across all roles
-- All passwords: password123 (bcrypt hashed)
-- ============================================

-- Password hash for 'password123'
-- $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ========== ADMINS ==========
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `phone`, `gender`, `specialization`, `is_active`) VALUES
('Admin User', 'admin@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+94771234501', 'male', NULL, 1),
('Sarah Admin', 'sarah.admin@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+94771234502', 'female', NULL, 1);

-- ========== DOCTORS ==========
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `phone`, `gender`, `specialization`, `consulting_hours`, `consulting_days`, `consulting_fee`, `is_active`) VALUES
('Dr. James Wilson', 'james.wilson@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234503', 'male', 'Cardiology', '09:00 AM - 05:00 PM', 'Monday,Tuesday,Wednesday,Thursday,Friday', 5000.00, 1),
('Dr. Emily Carter', 'emily.carter@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234504', 'female', 'Neurology', '10:00 AM - 06:00 PM', 'Monday,Wednesday,Friday', 3500.00, 1),
('Dr. Raj Patel', 'raj.patel@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234505', 'male', 'Orthopedics', '08:00 AM - 04:00 PM', 'Monday,Tuesday,Wednesday,Thursday,Friday', 4000.00, 1),
('Dr. Lisa Chen', 'lisa.chen@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234506', 'female', 'Dermatology', '09:00 AM - 03:00 PM', 'Tuesday,Thursday,Saturday', 3000.00, 1),
('Dr. Ahmed Khan', 'ahmed.khan@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234507', 'male', 'Pediatrics', '08:30 AM - 04:30 PM', 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday', 2500.00, 1),
('Dr. Sophia Brown', 'sophia.brown@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'doctor', '+94771234508', 'female', 'General Medicine', '09:00 AM - 05:00 PM', 'Monday,Tuesday,Wednesday,Thursday,Friday', 2000.00, 1);

-- ========== PATIENTS ==========
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `phone`, `gender`, `date_of_birth`, `address`, `is_active`) VALUES
('John Doe', 'john.doe@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234509', 'male', '1990-05-15', '123 Main St, Colombo', 1),
('Jane Smith', 'jane.smith@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234510', 'female', '1985-08-22', '456 Oak Ave, Kandy', 1),
('Michael Johnson', 'michael.j@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234511', 'male', '1978-12-10', '789 Pine Rd, Galle', 1),
('Emma Williams', 'emma.w@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234512', 'female', '1995-03-28', '321 Elm St, Jaffna', 1),
('David Brown', 'david.b@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234513', 'male', '2000-07-04', '654 Maple Dr, Matara', 1),
('Olivia Davis', 'olivia.d@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234514', 'female', '1988-11-19', '987 Cedar Ln, Negombo', 1),
('Robert Miller', 'robert.m@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234515', 'male', '1972-01-30', '147 Birch Ct, Batticaloa', 1),
('Amara Perera', 'amara.p@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'patient', '+94771234516', 'female', '1998-09-12', '258 Willow Way, Trincomalee', 1);

-- ========== RECEPTIONISTS ==========
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `phone`, `gender`, `is_active`) VALUES
('Nadia Fernando', 'nadia.f@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', '+94771234517', 'female', 1),
('Kumar Silva', 'kumar.s@healthylife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptionist', '+94771234518', 'male', 1);

-- ========== DEPARTMENTS ==========
INSERT INTO `departments` (`name`, `description`) VALUES
('Cardiology', 'Heart and cardiovascular system'),
('Neurology', 'Brain and nervous system'),
('Orthopedics', 'Bones, joints, and muscles'),
('Dermatology', 'Skin, hair, and nails'),
('Pediatrics', 'Children and adolescent health'),
('General Medicine', 'Primary care and general health'),
('Emergency', 'Emergency and trauma care'),
('Radiology', 'Medical imaging and diagnostics');

-- ========== ROOMS (assigned to doctors) ==========
INSERT INTO `rooms` (`room_number`, `room_type`, `doctor_id`, `status`) VALUES
('R-101', 'general', 3, 'available'),
('R-102', 'general', 4, 'available'),
('R-103', 'private', 5, 'available'),
('R-104', 'private', 6, 'available'),
('R-201', 'icu', 7, 'available'),
('R-202', 'icu', 8, 'available'),
('R-301', 'operation', NULL, 'available'),
('R-302', 'general', NULL, 'available'),
('R-303', 'private', NULL, 'available'),
('R-304', 'general', NULL, 'available');

-- ========== SAMPLE APPOINTMENTS ==========
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `reason`) VALUES
(9, 3, CURDATE(), '09:00:00', 'approved', 'Annual heart checkup'),
(10, 4, CURDATE(), '10:30:00', 'approved', 'Recurring headaches'),
(11, 5, CURDATE(), '11:00:00', 'pending', 'Knee pain consultation'),
(12, 6, CURDATE(), '14:00:00', 'pending', 'Skin rash treatment'),
(13, 7, CURDATE(), '15:30:00', 'completed', 'Child vaccination'),
(14, 8, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:30:00', 'pending', 'General checkup'),
(15, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 'approved', 'Follow-up visit'),
(16, 4, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 'pending', 'Migraine consultation'),
(9, 3, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'pending', 'Follow-up cardiology'),
(10, 5, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', 'approved', 'Knee X-ray review');

-- ========== SAMPLE BILLING ==========
INSERT INTO `billing` (`patient_id`, `appointment_id`, `amount`, `payment_method`, `payment_status`, `payment_code`, `description`) VALUES
(9, 1, 5000.00, 'card', 'paid', NULL, 'Cardiology consultation fee'),
(10, 2, 3500.00, NULL, 'unpaid', NULL, 'Neurology consultation fee'),
(11, 3, 4000.00, 'cashier', 'pending_verification', 'PAY-HL-20260212-001', 'Orthopedic consultation fee'),
(13, 5, 2500.00, 'cash', 'paid', NULL, 'Pediatric vaccination fee'),
(14, 6, 3000.00, NULL, 'unpaid', NULL, 'General checkup fee');

-- ========== SAMPLE FEEDBACK ==========
INSERT INTO `feedback` (`patient_id`, `subject`, `message`, `type`) VALUES
(9, 'Excellent Service', 'Dr. Wilson was very thorough and professional. Highly recommend!', 'feedback'),
(10, 'Parking Issue', 'The parking area is too small. Please consider expanding it.', 'inquiry'),
(12, 'Great Experience', 'The dermatology department is well-equipped and the staff is friendly.', 'feedback'),
(14, 'Appointment Delay', 'My appointment was delayed by 45 minutes. Please improve scheduling.', 'inquiry');

-- ========== SAMPLE MEDICAL REPORTS ==========
INSERT INTO `medical_reports` (`patient_id`, `doctor_id`, `report_title`, `report_description`) VALUES
(9, 3, 'ECG Report - Feb 2026', 'Normal sinus rhythm. No abnormalities detected.'),
(10, 4, 'Brain MRI Report', 'No structural abnormalities found. Recommend follow-up in 6 months.'),
(13, 7, 'Vaccination Record', 'Completed first dose of DPT vaccine. Next dose scheduled in 4 weeks.');
