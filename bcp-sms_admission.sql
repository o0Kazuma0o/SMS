-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2024 at 02:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bcp-sms_admission`
--

-- --------------------------------------------------------

--
-- Table structure for table `sms3_departments`
--

CREATE TABLE `sms3_departments` (
  `id` int(11) NOT NULL,
  `department_code` varchar(10) NOT NULL,
  `department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_departments`
--

INSERT INTO `sms3_departments` (`id`, `department_code`, `department_name`) VALUES
(1, 'BSIT', 'Bachelor of Science in Information Technology'),
(2, 'BSCS', 'Bachelor of Science in Computer Science');

-- --------------------------------------------------------

--
-- Table structure for table `sms3_pending_admission`
--

CREATE TABLE `sms3_pending_admission` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `admission_type` varchar(255) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `sex` varchar(50) NOT NULL,
  `civil_status` varchar(50) NOT NULL,
  `religion` varchar(255) DEFAULT NULL,
  `birthday` date NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `facebook_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `father_name` varchar(255) DEFAULT NULL,
  `mother_name` varchar(255) DEFAULT NULL,
  `guardian_name` varchar(255) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `primary_school` varchar(255) DEFAULT NULL,
  `primary_year` varchar(4) DEFAULT NULL,
  `secondary_school` varchar(255) DEFAULT NULL,
  `secondary_year` varchar(4) DEFAULT NULL,
  `last_school` varchar(255) DEFAULT NULL,
  `last_school_year` varchar(4) DEFAULT NULL,
  `referral_source` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `working_student` varchar(3) NOT NULL DEFAULT 'No',
  `member4ps` varchar(3) NOT NULL DEFAULT 'No'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_pending_admission`
--

INSERT INTO `sms3_pending_admission` (`id`, `full_name`, `program`, `admission_type`, `year_level`, `sex`, `civil_status`, `religion`, `birthday`, `email`, `contact_number`, `facebook_name`, `address`, `father_name`, `mother_name`, `guardian_name`, `guardian_contact`, `primary_school`, `primary_year`, `secondary_school`, `secondary_year`, `last_school`, `last_school_year`, `referral_source`, `status`, `created_at`, `working_student`, `member4ps`) VALUES
(1, 'Ho Lee Schit', 'BSIT', 'New Regular', '1st', 'Male', 'Single', 'Buddhaism', '2016-09-27', 'holeeschit420@gmail.com', '09123456789', 'HoLeeSchit', 'Block 69 Lot 420, Barangay 187, Tondo Manila - NCR', 'Ay Ken Nat', 'Mader Pa Ker', 'Bo Bo Tea', '09123456789', 'Marites Elementary School', '2022', 'Karen National High School', '2022', 'Gar Technological School', '2022', 'Social Media', 'Pending', '2024-10-12 22:52:32', 'No', 'No');

-- --------------------------------------------------------

--
-- Table structure for table `sms3_rooms`
--

CREATE TABLE `sms3_rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `department_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_rooms`
--

INSERT INTO `sms3_rooms` (`id`, `room_name`, `location`, `department_id`) VALUES
(1, '201', '2nd Floor', 1),
(2, '301', '3rd floor', 2);

-- --------------------------------------------------------

--
-- Table structure for table `sms3_sections`
--

CREATE TABLE `sms3_sections` (
  `id` int(11) NOT NULL,
  `section_number` int(11) NOT NULL,
  `year_level` int(11) NOT NULL,
  `semester` enum('1st Semester','2nd Semester') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 50
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_sections`
--

INSERT INTO `sms3_sections` (`id`, `section_number`, `year_level`, `semester`, `department_id`, `capacity`) VALUES
(1, 1101, 1, '1st Semester', 1, 50),
(2, 1131, 1, '1st Semester', 2, 50);

-- --------------------------------------------------------

--
-- Table structure for table `sms3_students`
--

CREATE TABLE `sms3_students` (
  `id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'Student',
  `program` varchar(100) NOT NULL,
  `admission_type` varchar(50) NOT NULL,
  `year_level` varchar(10) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `civil_status` varchar(20) NOT NULL,
  `religion` varchar(50) NOT NULL,
  `birthday` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `facebook_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `father_name` varchar(255) NOT NULL,
  `mother_name` varchar(255) NOT NULL,
  `guardian_name` varchar(255) NOT NULL,
  `guardian_contact` varchar(20) NOT NULL,
  `primary_school` varchar(100) NOT NULL,
  `primary_year` varchar(4) NOT NULL,
  `secondary_school` varchar(100) NOT NULL,
  `secondary_year` varchar(4) NOT NULL,
  `last_school` varchar(100) NOT NULL,
  `last_school_year` varchar(4) NOT NULL,
  `referral_source` varchar(100) NOT NULL,
  `working_student` varchar(3) NOT NULL DEFAULT 'No',
  `member4ps` varchar(3) NOT NULL DEFAULT 'No',
  `status` varchar(20) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sms3_subjects`
--

CREATE TABLE `sms3_subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_subjects`
--

INSERT INTO `sms3_subjects` (`id`, `subject_code`, `subject_name`, `department_id`) VALUES
(1, 'ITE', 'IT Elective', 1),
(2, 'DATSTRUC101', 'Data Structure', 2),
(3, 'DBMS1', 'Database Base', 2);

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

--
-- Dumping data for table `sms3_timetable`
--

INSERT INTO `sms3_timetable` (`id`, `subject_id`, `section_id`, `room_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(22, 3, 2, 2, 'Thursday', '06:00:00', '20:00:00'),
(23, 2, 2, 2, 'Monday', '06:00:00', '08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `sms3_user`
--

CREATE TABLE `sms3_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('staff','admin','superadmin') NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms3_user`
--

INSERT INTO `sms3_user` (`id`, `username`, `password`, `name`, `role`, `phone`, `email`) VALUES
(1, 'admin', '#Ca8080', 'Laurence Capati', 'admin', '09123456789', 'lpcapati25@gmail.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sms3_departments`
--
ALTER TABLE `sms3_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_code` (`department_code`);

--
-- Indexes for table `sms3_pending_admission`
--
ALTER TABLE `sms3_pending_admission`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_name` (`room_name`),
  ADD KEY `fk_department_room` (`department_id`);

--
-- Indexes for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `sms3_students`
--
ALTER TABLE `sms3_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `sms3_subjects`
--
ALTER TABLE `sms3_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `department_id` (`department_id`);

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
-- AUTO_INCREMENT for table `sms3_departments`
--
ALTER TABLE `sms3_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms3_pending_admission`
--
ALTER TABLE `sms3_pending_admission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms3_students`
--
ALTER TABLE `sms3_students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sms3_subjects`
--
ALTER TABLE `sms3_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sms3_timetable`
--
ALTER TABLE `sms3_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `sms3_user`
--
ALTER TABLE `sms3_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sms3_rooms`
--
ALTER TABLE `sms3_rooms`
  ADD CONSTRAINT `fk_department_room` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sms3_sections`
--
ALTER TABLE `sms3_sections`
  ADD CONSTRAINT `sms3_sections_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `sms3_departments` (`id`);

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
