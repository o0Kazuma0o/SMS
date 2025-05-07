-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 04, 2025 at 01:28 PM
-- Server version: 10.3.39-MariaDB-0ubuntu0.20.04.2
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `admi_bcp_sms3_admission`
--

-- --------------------------------------------------------

--
-- Table structure for table `sms3_academic_years`
--

CREATE TABLE `sms3_academic_years` (
  `id` int(11) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_admissions_data`
--

CREATE TABLE `sms3_admissions_data` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch` varchar(50) NOT NULL,
  `admission_type` varchar(255) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `sex` varchar(50) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `birthday` date NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `working_student` varchar(3) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_year` varchar(4) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year` varchar(4) DEFAULT NULL,
  `last_school` varchar(255) DEFAULT NULL,
  `last_school_year` varchar(4) DEFAULT NULL,
  `referral_source` varchar(255) DEFAULT NULL,
  `form138` varchar(255) DEFAULT NULL,
  `good_moral` varchar(255) DEFAULT NULL,
  `form137` varchar(255) DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `brgy_clearance` varchar(255) DEFAULT NULL,
  `honorable_dismissal` varchar(255) DEFAULT NULL,
  `transcript_of_records` varchar(255) DEFAULT NULL,
  `certificate_of_grades` varchar(255) DEFAULT NULL,
  `status` enum('Accepted','Enrolled','Not Enrolled') DEFAULT 'Not Enrolled',
  `receipt_status` enum('Not Paid','Paid') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_audit_log`
--

CREATE TABLE `sms3_audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `target_table` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_departments`
--

CREATE TABLE `sms3_departments` (
  `id` int(11) NOT NULL,
  `department_code` varchar(10) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_enrollment_data`
--

CREATE TABLE `sms3_enrollment_data` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `timetable_1` int(11) DEFAULT NULL,
  `timetable_2` int(11) DEFAULT NULL,
  `timetable_3` int(11) DEFAULT NULL,
  `timetable_4` int(11) DEFAULT NULL,
  `timetable_5` int(11) DEFAULT NULL,
  `timetable_6` int(11) DEFAULT NULL,
  `timetable_7` int(11) DEFAULT NULL,
  `timetable_8` int(11) DEFAULT NULL,
  `receipt_status` enum('Not Paid','Paid') NOT NULL DEFAULT 'Not Paid',
  `status` enum('Approved','Rejected') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_payments`
--

CREATE TABLE `sms3_payments` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `payment_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_pending_admission`
--

CREATE TABLE `sms3_pending_admission` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch` varchar(50) NOT NULL,
  `admission_type` varchar(255) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `sex` varchar(50) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `birthday` date NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `working_student` varchar(3) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_year` varchar(4) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year` varchar(4) DEFAULT NULL,
  `last_school` varchar(255) DEFAULT NULL,
  `last_school_year` varchar(4) DEFAULT NULL,
  `referral_source` varchar(255) DEFAULT NULL,
  `old_student_number` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Temporarily Enrolled','Processing') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_pending_enrollment`
--

CREATE TABLE `sms3_pending_enrollment` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `timetable_1` int(11) DEFAULT NULL,
  `timetable_2` int(11) DEFAULT NULL,
  `timetable_3` int(11) DEFAULT NULL,
  `timetable_4` int(11) DEFAULT NULL,
  `timetable_5` int(11) DEFAULT NULL,
  `timetable_6` int(11) DEFAULT NULL,
  `timetable_7` int(11) DEFAULT NULL,
  `timetable_8` int(11) DEFAULT NULL,
  `receipt_status` enum('Not Paid','Paid') NOT NULL DEFAULT 'Not Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_rooms`
--

CREATE TABLE `sms3_rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `branch` varchar(50) DEFAULT 'Main'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_sections`
--

CREATE TABLE `sms3_sections` (
  `id` int(11) NOT NULL,
  `section_number` int(11) NOT NULL,
  `year_level` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch` varchar(50) DEFAULT 'Main',
  `capacity` int(11) NOT NULL DEFAULT 50,
  `semester_id` int(11) DEFAULT NULL,
  `available` int(11) NOT NULL DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_semesters`
--

CREATE TABLE `sms3_semesters` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_students`
--

CREATE TABLE `sms3_students` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `academic_year` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Student',
  `department_id` int(11) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `admission_type` varchar(50) NOT NULL,
  `year_level` varchar(10) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `religion` varchar(50) NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `guardian_name` varchar(255) NOT NULL,
  `guardian_contact` varchar(20) NOT NULL,
  `primary_school` varchar(100) NOT NULL,
  `primary_year` varchar(4) NOT NULL,
  `secondary_school` varchar(100) NOT NULL,
  `secondary_year` varchar(4) NOT NULL,
  `last_school` varchar(100) NOT NULL,
  `last_school_year` varchar(4) NOT NULL,
  `referral_source` varchar(100) NOT NULL,
  `form138` varchar(50) DEFAULT NULL,
  `good_moral` varchar(50) DEFAULT NULL,
  `form137` varchar(50) DEFAULT NULL,
  `birth_certificate` varchar(50) DEFAULT NULL,
  `brgy_clearance` varchar(50) DEFAULT NULL,
  `honorable_dismissal` varchar(50) DEFAULT NULL,
  `transcript_of_records` varchar(50) DEFAULT NULL,
  `certificate_of_grades` varchar(50) DEFAULT NULL,
  `working_student` varchar(3) NOT NULL DEFAULT 'No',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `timetable_1` int(11) DEFAULT NULL,
  `timetable_2` int(11) DEFAULT NULL,
  `timetable_3` int(11) DEFAULT NULL,
  `timetable_4` int(11) DEFAULT NULL,
  `timetable_5` int(11) DEFAULT NULL,
  `timetable_6` int(11) DEFAULT NULL,
  `timetable_7` int(11) DEFAULT NULL,
  `timetable_8` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Not Enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_subjects`
--

CREATE TABLE `sms3_subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `year_level` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_temp_enroll`
--

CREATE TABLE `sms3_temp_enroll` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch` varchar(50) NOT NULL,
  `admission_type` varchar(255) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `sex` varchar(50) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `birthday` date NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `working_student` varchar(3) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_year` varchar(4) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year` varchar(4) DEFAULT NULL,
  `last_school` varchar(255) DEFAULT NULL,
  `last_school_year` varchar(4) DEFAULT NULL,
  `referral_source` varchar(255) DEFAULT NULL,
  `form138` varchar(255) DEFAULT NULL,
  `good_moral` varchar(255) DEFAULT NULL,
  `form137` varchar(255) DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `brgy_clearance` varchar(255) DEFAULT NULL,
  `honorable_dismissal` varchar(255) DEFAULT NULL,
  `transcript_of_records` varchar(255) DEFAULT NULL,
  `certificate_of_grades` varchar(255) DEFAULT NULL,
  `status` enum('Processing','Accepted') DEFAULT 'Processing',
  `receipt_status` enum('Not Paid','Paid') NOT NULL DEFAULT 'Not Paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_timetable`
--

CREATE TABLE `sms3_timetable` (
  `id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_user`
--

CREATE TABLE `sms3_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('Staff','Registrar','Admin','Superadmin') NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sms3_academic_years`
--
ALTER TABLE `sms3_academic_years`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `academic_year` (`academic_year`);

--
-- Indexes for table `sms3_admissions_data`
--
ALTER TABLE `sms3_admissions_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pending_admission_department` (`department_id`);

--
-- Indexes for table `sms3_audit_log`
--
ALTER TABLE `sms3_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_audit` (`user_id`);

--
-- Indexes for table `sms3_departments`
--
ALTER TABLE `sms3_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_code`);

--
-- Indexes for table `sms3_enrollment_data`
--
ALTER TABLE `sms3_enrollment_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_10` (`timetable_1`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_11` (`timetable_2`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_12` (`timetable_3`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_13` (`timetable_4`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_14` (`timetable_5`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_15` (`timetable_6`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_16` (`timetable_7`),
  ADD KEY `sms3_pending_enrollment_data_ibfk_17` (`timetable_8`);

--
-- Indexes for table `sms3_payments`
--
ALTER TABLE `sms3_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_temp_enroll` (`student_number`);

--
-- Indexes for table `sms3_pending_admission`
--
ALTER TABLE `sms3_pending_admission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pending_admission_department` (`department_id`);

--
-- Indexes for table `sms3_pending_enrollment`
--
ALTER TABLE `sms3_pending_enrollment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `timetable_1` (`timetable_1`),
  ADD KEY `timetable_2` (`timetable_2`),
  ADD KEY `timetable_3` (`timetable_3`),
  ADD KEY `timetable_4` (`timetable_4`),
  ADD KEY `timetable_5` (`timetable_5`),
  ADD KEY `timetable_6` (`timetable_6`),
  ADD KEY `timetable_7` (`timetable_7`),
  ADD KEY `timetable_8` (`timetable_8`);

--
-- Indexes for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_department_room` (`department_id`);

--
-- Indexes for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `fk_sections_semester` (`semester_id`);

--
-- Indexes for table `sms3_semesters`
--
ALTER TABLE `sms3_semesters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms3_students`
--
ALTER TABLE `sms3_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_students_department` (`department_id`),
  ADD KEY `fk_timetable_1` (`timetable_1`),
  ADD KEY `fk_timetable_2` (`timetable_2`),
  ADD KEY `fk_timetable_3` (`timetable_3`),
  ADD KEY `fk_timetable_4` (`timetable_4`),
  ADD KEY `fk_timetable_5` (`timetable_5`),
  ADD KEY `fk_timetable_6` (`timetable_6`),
  ADD KEY `fk_timetable_7` (`timetable_7`),
  ADD KEY `fk_timetable_8` (`timetable_8`),
  ADD KEY `username_2` (`username`),
  ADD KEY `academic_year` (`academic_year`);

--
-- Indexes for table `sms3_subjects`
--
ALTER TABLE `sms3_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `sms3_temp_enroll`
--
ALTER TABLE `sms3_temp_enroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pending_admission_department` (`department_id`);

--
-- Indexes for table `sms3_timetable`
--
ALTER TABLE `sms3_timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `section_id` (`section_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `sms3_user`
--
ALTER TABLE `sms3_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sms3_academic_years`
--
ALTER TABLE `sms3_academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_admissions_data`
--
ALTER TABLE `sms3_admissions_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_audit_log`
--
ALTER TABLE `sms3_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_departments`
--
ALTER TABLE `sms3_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_enrollment_data`
--
ALTER TABLE `sms3_enrollment_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_payments`
--
ALTER TABLE `sms3_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_pending_admission`
--
ALTER TABLE `sms3_pending_admission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_pending_enrollment`
--
ALTER TABLE `sms3_pending_enrollment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_semesters`
--
ALTER TABLE `sms3_semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_students`
--
ALTER TABLE `sms3_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_subjects`
--
ALTER TABLE `sms3_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_temp_enroll`
--
ALTER TABLE `sms3_temp_enroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_timetable`
--
ALTER TABLE `sms3_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_user`
--
ALTER TABLE `sms3_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sms3_audit_log`
--
ALTER TABLE `sms3_audit_log`
  ADD CONSTRAINT `fk_user_audit` FOREIGN KEY (`user_id`) REFERENCES `sms3_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms3_enrollment_data`
--
ALTER TABLE `sms3_enrollment_data`
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_10` FOREIGN KEY (`timetable_1`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_11` FOREIGN KEY (`timetable_2`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_12` FOREIGN KEY (`timetable_3`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_13` FOREIGN KEY (`timetable_4`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_14` FOREIGN KEY (`timetable_5`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_15` FOREIGN KEY (`timetable_6`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_16` FOREIGN KEY (`timetable_7`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_data_ibfk_17` FOREIGN KEY (`timetable_8`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sms3_pending_admission`
--
ALTER TABLE `sms3_pending_admission`
  ADD CONSTRAINT `fk_pending_admission_department` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sms3_pending_enrollment`
--
ALTER TABLE `sms3_pending_enrollment`
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `sms3_students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_10` FOREIGN KEY (`timetable_1`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_11` FOREIGN KEY (`timetable_2`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_12` FOREIGN KEY (`timetable_3`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_13` FOREIGN KEY (`timetable_4`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_14` FOREIGN KEY (`timetable_5`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_15` FOREIGN KEY (`timetable_6`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_16` FOREIGN KEY (`timetable_7`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_17` FOREIGN KEY (`timetable_8`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_4` FOREIGN KEY (`section_3`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_5` FOREIGN KEY (`section_4`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_6` FOREIGN KEY (`section_5`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_7` FOREIGN KEY (`section_6`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_8` FOREIGN KEY (`section_7`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sms3_pending_enrollment_ibfk_9` FOREIGN KEY (`section_8`) REFERENCES `sms3_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  ADD CONSTRAINT `fk_department_room` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  ADD CONSTRAINT `fk_sections_semester` FOREIGN KEY (`semester_id`) REFERENCES `sms3_semesters` (`id`),
  ADD CONSTRAINT `sms3_sections_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`);

--
-- Constraints for table `sms3_students`
--
ALTER TABLE `sms3_students`
  ADD CONSTRAINT `9` FOREIGN KEY (`academic_year`) REFERENCES `sms3_academic_years` (`id`),
  ADD CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_1` FOREIGN KEY (`timetable_1`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_2` FOREIGN KEY (`timetable_2`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_3` FOREIGN KEY (`timetable_3`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_4` FOREIGN KEY (`timetable_4`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_5` FOREIGN KEY (`timetable_5`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_6` FOREIGN KEY (`timetable_6`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_7` FOREIGN KEY (`timetable_7`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_timetable_8` FOREIGN KEY (`timetable_8`) REFERENCES `sms3_timetable` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sms3_subjects`
--
ALTER TABLE `sms3_subjects`
  ADD CONSTRAINT `sms3_subjects_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sms3_timetable`
--
ALTER TABLE `sms3_timetable`
  ADD CONSTRAINT `sms3_timetable_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `sms3_subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms3_timetable_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sms3_sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sms3_timetable_ibfk_3` FOREIGN KEY (`room_id`) REFERENCES `sms3_rooms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
