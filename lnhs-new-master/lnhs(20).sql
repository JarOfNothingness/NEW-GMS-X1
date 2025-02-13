-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2024 at 11:19 PM
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
-- Database: `lnhs`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `update_teacher_counts` ()   BEGIN
    UPDATE user u
    SET 
        handled_subjects = (
            SELECT COUNT(DISTINCT subject_id) 
            FROM teacher_assignments 
            WHERE teacher_id = u.userid
        ),
        handled_sections = (
            SELECT COUNT(DISTINCT grade_section) 
            FROM teacher_assignments 
            WHERE teacher_id = u.userid
        )
    WHERE u.role = 'Teacher';
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `logs` varchar(250) NOT NULL,
  `datetime` datetime NOT NULL,
  `admin_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `userid`, `logs`, `datetime`, `admin_id`) VALUES
(23, 10, ' User deactivated', '2024-10-22 23:28:57', 23),
(24, 16, ' User deactivated', '2024-10-22 23:29:16', 23),
(25, 0, 'Deleted Announcement', '2024-10-22 23:36:43', 23),
(26, 0, 'Added an Announcement', '2024-10-22 23:36:46', 23),
(27, 10, 'User activated', '2024-10-23 21:36:19', 12);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_date` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`, `expiration_date`, `status`) VALUES
(13, '123', '123', '2024-10-24 08:29:50', '2024-10-25 12:00:00', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `approval_history`
--

CREATE TABLE `approval_history` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `approval_history`
--

INSERT INTO `approval_history` (`id`, `userid`, `admin_id`, `comments`, `action_date`) VALUES
(32, 14, 14, '123', '2024-10-05 17:54:32'),
(33, 15, 15, 'TRY', '2024-10-05 18:03:51'),
(34, 16, 16, 'Alternative account to use as a teacher', '2024-10-05 18:04:57'),
(35, 17, 12, '1', '2024-10-05 18:35:46'),
(36, 18, 18, 'gp', '2024-10-05 18:36:54'),
(37, 19, 12, 'Go', '2024-10-05 19:02:32'),
(38, 20, 20, '123', '2024-10-05 19:03:43'),
(39, 21, 22, '123', '2024-10-05 19:07:03'),
(40, 22, 22, '123', '2024-10-05 19:07:07'),
(41, 23, 23, 'A Teacher in the school', '2024-10-08 15:42:40'),
(42, 26, 12, 'a', '2024-10-19 14:40:43'),
(43, 28, 12, '123', '2024-10-22 14:08:53');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `subject_id` int(11) NOT NULL DEFAULT 0,
  `grade_section` varchar(250) NOT NULL,
  `assessment_type_id` int(11) NOT NULL,
  `max_score` float NOT NULL,
  `quarter` enum('1st','2nd','3rd','4th') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `user_id`, `subject_id`, `grade_section`, `assessment_type_id`, `max_score`, `quarter`, `created_at`, `updated_at`) VALUES
(25, 30, 2, '7-Gumamela', 2, 14, '1st', '2024-10-24 18:42:49', '2024-10-24 18:42:49');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_summary`
--

CREATE TABLE `assessment_summary` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `quarter` enum('1st','2nd','3rd','4th') NOT NULL,
  `written_works_total` float DEFAULT NULL,
  `written_works_ps` float DEFAULT NULL,
  `written_works_ws` float DEFAULT NULL,
  `performance_tasks_total` float DEFAULT NULL,
  `performance_tasks_ps` float DEFAULT NULL,
  `performance_tasks_ws` float DEFAULT NULL,
  `quarterly_assessment_score` float DEFAULT NULL,
  `quarterly_assessment_ps` float DEFAULT NULL,
  `quarterly_assessment_ws` float DEFAULT NULL,
  `initial_grade` float DEFAULT NULL,
  `quarterly_grade` float DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessment_summary`
--

INSERT INTO `assessment_summary` (`id`, `user_id`, `student_id`, `subject_id`, `quarter`, `written_works_total`, `written_works_ps`, `written_works_ws`, `performance_tasks_total`, `performance_tasks_ps`, `performance_tasks_ws`, `quarterly_assessment_score`, `quarterly_assessment_ps`, `quarterly_assessment_ws`, `initial_grade`, `quarterly_grade`, `approval_status`) VALUES
(96, 30, 201, 2, '1st', 0, 0, 0, 14, 100, 50, 0, 0, 0, NULL, 72, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_types`
--

CREATE TABLE `assessment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `percentage` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assessment_types`
--

INSERT INTO `assessment_types` (`id`, `name`, `percentage`, `subject_id`) VALUES
(1, 'WRITTEN WORKS', 30, 1),
(2, 'PERFORMANCE TASKS', 50, 1),
(3, 'QUARTERLY ASSESSMENT', 20, 1),
(82, 'WRITTEN WORKS', 30, 2),
(83, 'PERFORMANCE TASKS', 50, 2),
(84, 'QUARTERLY ASSESSMENT', 20, 2),
(85, 'WRITTEN WORKS', 40, 3),
(86, 'PERFORMANCE TASKS', 40, 3),
(87, 'QUARTERLY ASSESSMENT', 20, 3),
(88, 'WRITTEN WORKS', 40, 4),
(89, 'PERFORMANCE TASKS', 40, 4),
(90, 'QUARTERLY ASSESSMENT', 20, 4),
(91, 'WRITTEN WORKS', 30, 5),
(92, 'PERFORMANCE TASKS', 50, 5),
(93, 'QUARTERLY ASSESSMENT', 20, 5),
(94, 'WRITTEN WORKS', 20, 6),
(95, 'PERFORMANCE TASKS', 60, 6),
(96, 'QUARTERLY ASSESSMENT', 20, 6),
(97, 'WRITTEN WORKS', 20, 7),
(98, 'PERFORMANCE TASKS', 60, 7),
(99, 'QUARTERLY ASSESSMENT', 20, 7),
(100, 'WRITTEN WORKS', 30, 8),
(101, 'PERFORMANCE TASKS', 50, 8),
(102, 'QUARTERLY ASSESSMENT', 20, 8),
(103, 'WRITTEN WORKS', 30, 9),
(104, 'PERFORMANCE TASKS', 50, 9),
(105, 'QUARTERLY ASSESSMENT', 20, 9);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `section` varchar(50) DEFAULT NULL,
  `subject_id` int(11) NOT NULL,
  `month` varchar(10) NOT NULL,
  `school_year` varchar(9) NOT NULL DEFAULT '2024-2025',
  `day_01` enum('P','A','L','E') DEFAULT 'P',
  `day_02` enum('P','A','L','E') DEFAULT 'P',
  `day_03` enum('P','A','L','E') DEFAULT 'P',
  `day_04` enum('P','A','L','E') DEFAULT 'P',
  `day_05` enum('P','A','L','E') DEFAULT 'P',
  `day_06` enum('P','A','L','E') DEFAULT 'P',
  `day_07` enum('P','A','L','E') DEFAULT 'P',
  `day_08` enum('P','A','L','E') DEFAULT 'P',
  `day_09` enum('P','A','L','E') DEFAULT 'P',
  `day_10` enum('P','A','L','E') DEFAULT 'P',
  `day_11` enum('P','A','L','E') DEFAULT 'P',
  `day_12` enum('P','A','L','E') DEFAULT 'P',
  `day_13` enum('P','A','L','E') DEFAULT 'P',
  `day_14` enum('P','A','L','E') DEFAULT 'P',
  `day_15` enum('P','A','L','E') DEFAULT 'P',
  `day_16` enum('P','A','L','E') DEFAULT 'P',
  `day_17` enum('P','A','L','E') DEFAULT 'P',
  `day_18` enum('P','A','L','E') DEFAULT 'P',
  `day_19` enum('P','A','L','E') DEFAULT 'P',
  `day_20` enum('P','A','L','E') DEFAULT 'P',
  `day_21` enum('P','A','L','E') DEFAULT 'P',
  `day_22` enum('P','A','L','E') DEFAULT 'P',
  `day_23` enum('P','A','L','E') DEFAULT 'P',
  `day_24` enum('P','A','L','E') DEFAULT 'P',
  `day_25` enum('P','A','L','E') DEFAULT 'P',
  `day_26` enum('P','A','L','E') DEFAULT 'P',
  `day_27` enum('P','A','L','E') DEFAULT 'P',
  `day_28` enum('P','A','L','E') DEFAULT 'P',
  `day_29` enum('P','A','L','E') DEFAULT 'P',
  `day_30` enum('P','A','L','E') DEFAULT 'P',
  `day_31` enum('P','A','L','E') DEFAULT 'P',
  `total_present` int(11) DEFAULT 0,
  `total_absent` int(11) DEFAULT 0,
  `total_late` int(11) DEFAULT 0,
  `total_excused` int(11) DEFAULT 0,
  `total_points` int(11) DEFAULT 0,
  `status` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `user_id`, `section`, `subject_id`, `month`, `school_year`, `day_01`, `day_02`, `day_03`, `day_04`, `day_05`, `day_06`, `day_07`, `day_08`, `day_09`, `day_10`, `day_11`, `day_12`, `day_13`, `day_14`, `day_15`, `day_16`, `day_17`, `day_18`, `day_19`, `day_20`, `day_21`, `day_22`, `day_23`, `day_24`, `day_25`, `day_26`, `day_27`, `day_28`, `day_29`, `day_30`, `day_31`, `total_present`, `total_absent`, `total_late`, `total_excused`, `total_points`, `status`) VALUES
(7, 201, 30, '7-Gumamela', 0, '2024-10', '2024-2025', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'P', 'A', 26, 1, 0, 0, 260, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `encoded_learner_data`
--

CREATE TABLE `encoded_learner_data` (
  `id` int(11) NOT NULL,
  `learner_id` int(11) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `name_extension` varchar(50) DEFAULT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `lrn` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `high_school_completer` tinyint(1) DEFAULT NULL,
  `general_average` decimal(5,2) DEFAULT NULL,
  `citation` varchar(255) DEFAULT NULL,
  `elementary_school_name` varchar(255) DEFAULT NULL,
  `school_id` varchar(50) DEFAULT NULL,
  `school_address` varchar(255) DEFAULT NULL,
  `pept_passer` tinyint(1) DEFAULT NULL,
  `pept_rating` varchar(20) DEFAULT NULL,
  `als_a_e_passer` tinyint(1) DEFAULT NULL,
  `als_rating` varchar(20) DEFAULT NULL,
  `others_specify` tinyint(1) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `testing_center` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `adviser` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `district` varchar(255) DEFAULT NULL,
  `division` varchar(255) DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `encoded_learner_data`
--

INSERT INTO `encoded_learner_data` (`id`, `learner_id`, `last_name`, `first_name`, `name_extension`, `middle_name`, `lrn`, `birthdate`, `sex`, `high_school_completer`, `general_average`, `citation`, `elementary_school_name`, `school_id`, `school_address`, `pept_passer`, `pept_rating`, `als_a_e_passer`, `als_rating`, `others_specify`, `exam_date`, `testing_center`, `signature`, `adviser`, `school`, `district`, `division`, `region`, `school_year`) VALUES
(1, 158, 'Alfar', 'Miya,', '123', '', '123', '2024-10-18', 'Female', 1, 89.90, '', 'asdasd', '303031', NULL, 1, 'asdas', 1, 'asdasd', 1, '2024-10-25', '12312312', '1231', 'Anna', 'Lanao National High School', 'Pilar', 'Cebu', 'VII', '2024-2025'),
(2, 201, 'asd', 'das,', '', 'ads', 'adsasd', '2024-10-28', 'Male', 0, 323.00, 'asdasd', 'asdsda', '303031', NULL, 1, '23', 1, '2323', 1, '2024-10-25', 'asddsa', NULL, 'james', 'Lanao National High School', 'Pilar', 'Cebu', 'VII', '2024-2025'),
(3, 201, 'asd', 'das,', '', 'ads', 'asasd', '2024-10-25', 'Male', 0, 72.00, 'ads', 'teasdasd', '303031', NULL, 1, 'qweqwe', 1, 'asd', 1, '2024-10-24', 'qweqwe', NULL, 'james', 'Lanao National High School', 'Pilar', 'Cebu', 'VII', '2024-2025');

-- --------------------------------------------------------

--
-- Table structure for table `fileserver_files`
--

CREATE TABLE `fileserver_files` (
  `file_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fileserver_files`
--

INSERT INTO `fileserver_files` (`file_id`, `folder_id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(10, 14, 'grading_management (10).sql.gz', 'uploads/grading_management (10).sql.gz', '2024-09-14 14:18:25');

-- --------------------------------------------------------

--
-- Table structure for table `fileserver_folders`
--

CREATE TABLE `fileserver_folders` (
  `folder_id` int(11) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `folder_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fileserver_folders`
--

INSERT INTO `fileserver_folders` (`folder_id`, `folder_name`, `folder_password`) VALUES
(14, 'Test', '$2y$10$Rcb65WF9DtG0jhROHuwv/e2Hc8vK.mgoD8uIp872vsykpO3o0cvHq');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `grade` decimal(5,2) NOT NULL,
  `quarter` enum('1st','2nd','3rd','4th') NOT NULL,
  `datetime_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grade_approvals`
--

CREATE TABLE `grade_approvals` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_section` varchar(50) NOT NULL,
  `quarter` enum('1st','2nd','3rd','4th') NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grading_scale`
--

CREATE TABLE `grading_scale` (
  `id` int(11) NOT NULL,
  `initial_grade_min` decimal(5,2) DEFAULT NULL,
  `initial_grade_max` decimal(5,2) DEFAULT NULL,
  `transmuted_grade` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grading_scale`
--

INSERT INTO `grading_scale` (`id`, `initial_grade_min`, `initial_grade_max`, `transmuted_grade`) VALUES
(1, 100.00, 100.00, 100),
(2, 98.40, 99.99, 99),
(3, 96.80, 98.39, 98),
(4, 95.20, 96.79, 97),
(5, 93.60, 95.19, 96),
(6, 92.00, 93.59, 95),
(7, 90.40, 91.99, 94),
(8, 88.80, 90.39, 93),
(9, 87.20, 88.79, 92),
(10, 85.60, 87.19, 91),
(11, 84.00, 85.59, 90),
(12, 82.40, 83.99, 89),
(13, 80.80, 82.39, 88),
(14, 79.20, 80.79, 87),
(15, 77.60, 79.19, 86),
(16, 76.00, 77.59, 85),
(17, 74.40, 75.99, 84),
(18, 72.80, 74.39, 83),
(19, 71.20, 72.79, 82),
(20, 69.60, 71.19, 81),
(21, 68.00, 69.59, 80),
(22, 66.40, 67.99, 79),
(23, 64.80, 66.39, 78),
(24, 63.20, 64.79, 77),
(25, 61.60, 63.19, 76),
(26, 60.00, 61.59, 75),
(27, 56.00, 59.99, 74),
(28, 52.00, 55.99, 73),
(29, 48.00, 51.99, 72),
(30, 44.00, 47.99, 71),
(31, 40.00, 43.99, 70),
(32, 36.00, 39.99, 69),
(33, 32.00, 35.99, 68),
(34, 28.00, 31.99, 67),
(35, 24.00, 27.99, 66),
(36, 20.00, 23.99, 65),
(37, 16.00, 19.99, 64),
(38, 12.00, 15.99, 63),
(39, 8.00, 11.99, 62),
(40, 4.00, 7.99, 61),
(41, 0.00, 3.99, 60);

-- --------------------------------------------------------

--
-- Table structure for table `point_setter`
--

CREATE TABLE `point_setter` (
  `id` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `subject_id` varchar(250) NOT NULL,
  `points_present` int(11) NOT NULL DEFAULT 10,
  `points_absent` int(11) NOT NULL DEFAULT 0,
  `points_late` int(11) NOT NULL DEFAULT 5,
  `points_excused` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `point_setter`
--

INSERT INTO `point_setter` (`id`, `userid`, `subject_id`, `points_present`, `points_absent`, `points_late`, `points_excused`) VALUES
(1, 10, '2', 9, 0, 5, 0),
(2, 10, '1', 9, 0, 5, 0);

-- --------------------------------------------------------

--
-- Table structure for table `security_questions`
--

CREATE TABLE `security_questions` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `security_questions`
--

INSERT INTO `security_questions` (`id`, `question`) VALUES
(1, 'What is your favorite color?'),
(2, 'What is your favorite book?'),
(3, 'What is the name of your favorite hobby?'),
(4, 'What is your favorite food?'),
(5, 'What is your favorite movie?'),
(6, 'What is the name of your favorite vacation spot?'),
(7, 'What is your favorite sport?'),
(8, 'What was your favorite subject in school?'),
(9, 'What is the name of your favorite music artist?'),
(10, 'What is your favorite animal?');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `learners_name` varchar(255) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `grade & section` varchar(36) NOT NULL,
  `datetime_added` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `quarter` enum('1st','2nd','3rd','4th') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `learners_name`, `school_year`, `gender`, `grade & section`, `datetime_added`, `user_id`, `quarter`) VALUES
(201, 'das, ads asd', '2024-2025', 'Male', '7-Gumamela', '2024-10-25 02:40:59', 30, NULL),
(202, 'ytu, tuy tuy', '2024-2025', 'Male', '8-Narra', '2024-10-25 02:42:22', 30, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `written_exam` float DEFAULT NULL,
  `performance_task` float DEFAULT NULL,
  `quarterly_exam` float DEFAULT NULL,
  `final_grade` float DEFAULT NULL,
  `highest_possible_score` decimal(5,2) DEFAULT NULL,
  `lowest_score` decimal(5,2) DEFAULT NULL,
  `average_mean` decimal(5,2) DEFAULT NULL,
  `mps` decimal(5,2) DEFAULT NULL,
  `students_75_percent` int(11) DEFAULT NULL,
  `percentage_75_percent` decimal(5,2) DEFAULT NULL,
  `quarter` tinyint(4) NOT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `4th_quarter` decimal(5,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `quiz1` int(11) DEFAULT 0,
  `quiz2` int(11) DEFAULT 0,
  `quiz3` int(11) DEFAULT 0,
  `quiz4` int(11) DEFAULT 0,
  `quiz5` int(11) DEFAULT 0,
  `quiz6` int(11) DEFAULT 0,
  `quiz7` int(11) DEFAULT 0,
  `quiz8` int(11) DEFAULT 0,
  `quiz9` int(11) DEFAULT 0,
  `quiz10` int(11) DEFAULT 0,
  `written_scores_total` int(11) DEFAULT 0,
  `act1` int(11) DEFAULT 0,
  `act2` int(11) DEFAULT 0,
  `act3` int(11) DEFAULT 0,
  `act4` int(11) DEFAULT 0,
  `act5` int(11) DEFAULT 0,
  `act6` int(11) DEFAULT 0,
  `act7` int(11) DEFAULT 0,
  `act8` int(11) DEFAULT 0,
  `act9` int(11) DEFAULT 0,
  `act10` int(11) DEFAULT 0,
  `performance_task_total` int(11) DEFAULT 0,
  `date_time` datetime DEFAULT current_timestamp(),
  `transmuted_grade` float DEFAULT NULL,
  `highest_written_exam_score` decimal(5,2) DEFAULT NULL,
  `highest_performance_task_score` decimal(5,2) DEFAULT NULL,
  `highest_quarterly_exam_score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_quiz`
--

CREATE TABLE `student_quiz` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `assessment_type_id` int(11) DEFAULT NULL,
  `raw_score` float NOT NULL,
  `weighted_score` float DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_quiz`
--

INSERT INTO `student_quiz` (`id`, `student_id`, `user_id`, `assessment_id`, `subject_id`, `assessment_type_id`, `raw_score`, `weighted_score`, `created_at`, `updated_at`, `approval_status`) VALUES
(16, 201, 30, 25, 2, NULL, 14, 20, '2024-10-24 18:42:49', '2024-10-24 18:43:18', 'pending'),
(17, 202, 30, 25, 2, NULL, 0, NULL, '2024-10-24 18:42:49', '2024-10-24 18:42:49', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `description`) VALUES
(33, 201, 3, 'Math'),
(34, 201, 2, 'English'),
(35, 202, 3, 'Math'),
(36, 202, 2, 'English');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `description`, `student_id`) VALUES
(1, 'Araling Panlipunan', NULL, NULL),
(2, 'English', NULL, NULL),
(3, 'Math', NULL, NULL),
(4, 'Science', NULL, NULL),
(5, 'Filipino', NULL, NULL),
(6, 'TLE', NULL, NULL),
(7, 'Mapeh', NULL, NULL),
(8, 'ESP', NULL, NULL),
(9, 'Values', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_assignments`
--

CREATE TABLE `teacher_assignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `grade_section` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `teacher_assignments`
--
DELIMITER $$
CREATE TRIGGER `after_assignment_change` AFTER INSERT ON `teacher_assignments` FOR EACH ROW BEGIN
    CALL update_teacher_counts();
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `teacher_details`
-- (See below for the actual view)
--
CREATE TABLE `teacher_details` (
`userid` int(11)
,`name` varchar(36)
,`username` varchar(36)
,`handled_subjects` bigint(21)
,`handled_sections` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userid` int(11) NOT NULL,
  `name` varchar(36) NOT NULL,
  `username` varchar(36) NOT NULL,
  `address` varchar(36) NOT NULL,
  `role` varchar(36) NOT NULL,
  `security_question` int(11) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `security_question_id` int(11) DEFAULT NULL,
  `hashed_password` varchar(255) DEFAULT NULL,
  `handled_subjects` int(11) DEFAULT 0,
  `handled_sections` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userid`, `name`, `username`, `address`, `role`, `security_question`, `security_answer`, `status`, `security_question_id`, `hashed_password`, `handled_subjects`, `handled_sections`) VALUES
(10, 'Anna', 'Anna', 'Anna@gmail.com', 'Teacher', NULL, 'cat', 'approved', 1, '$2y$10$UWt5ibEzkyGrxVAtO7ayrOPz12ySWfKMP2mMBHUeNXJucwSVLnA9.', 0, 0),
(12, 'Jose', 'masteradmin', 'Jose@gmail.com', 'Admin', NULL, 'dog', '', 1, '$2y$10$goIXF4BvZSro7uzqzLQep.sK5s1zXV8UiR7O.WhwCczqmmOHYJ7hi', 0, 0),
(14, 'Testingg101', 'test1235', 'testing101@gmail.com', 'Teacher', NULL, '123', 'approved', 3, '$2y$10$QmCjdUXZ7rky90WU0CvxmePJApxFsKqJ3NYriAZjHfkQa.8Z2q/f.', 0, 0),
(15, 'TRIAL', 'TRYTRY123', 'TRIAL@GMAIL.COM', 'Teacher', NULL, 'TRY', 'approved', 9, '$2y$10$EArS79m1.GJCiA/rLV1ENOCS9RJPXVfl8TTAwpmu9zSLQv1MlD95a', 0, 0),
(16, 'Jose NiÃ±o Macasero Rama', 'LuckyBoywonder123', 'JoseR@gmail.com', 'Teacher', NULL, 'loli', 'rejected', 9, '$2y$10$6jO1/rlygqrcgHKhvGhoVuZy7z1vBLen8ESoiMzy4WeQDibCIDj1m', 0, 0),
(17, 'forgotpasswordtest', 'forgot123', 'forgot@gmail.com', 'Teacher', NULL, 'Gelo', 'approved', 10, '$2y$10$3BWGqyyXB6WCJR8pzfTJae71czfanJxwvvgfT7VjmAAxCjAQwSJI6', 0, 0),
(18, 'Kimberly Torreon', 'KimB123', 'KimB@gmail.com', 'Teacher', NULL, 'Neon', 'approved', 1, '$2y$10$T.Q2WgO6pgKXA9gxvXhow.JsOHvBMOqcJ8Moe9iFSTUcKASaVw7bi', 0, 0),
(19, 'Mommy', 'Mommy123', 'Mommy@gmail.com', 'Teacher', NULL, 'Mami', 'approved', 6, '$2y$10$k6u/8xW1z2wGtkoOiA4JSecJPj2AwkWol8rJPjkj8IZqWqvQOgJsO', 0, 0),
(20, 'Melon', 'MelonoRamyon123', 'Melono123@gmail.com', 'Teacher', NULL, 'Kiwi', 'approved', 2, '$2y$10$jyBUZwZaK7XoA5XhV4TjFem9Yj9A0XYbd6PzxnMDujXSLoqrlNu1W', 0, 0),
(21, 'Chloe Klum', 'Chloe123', 'Chloe@gmail.com', 'Teacher', NULL, 'Frozen', 'rejected', 9, '$2y$10$Gr6Swd3vPwn0xm3K1akioOYWUBVbIrIOpFL7Nvx2XOPF/TBDZnMwW', 0, 0),
(22, 'Klyde Matabang', 'Klyde123', 'Klydematabang@gmail.com', 'Teacher', NULL, '123', 'approved', 10, '$2y$10$NEgvIMhE.RchtxKeBhyT7eBpTWAGQh/SBL4XMcmNanrDkSj5HBZLG', 0, 0),
(23, 'Marlyn tester', 'Marlyn123', 'Marlyn@gmail.com', 'Admin', NULL, 'Volleyball', 'approved', 7, '$2y$10$GQt2sRPqFjU3WwO0l9iEzuHJjv/2H2ZnIaw1H.eMWDRbdi0W0GynW', 0, 0),
(24, 'test', 'test123', 'test@gmail.com', 'Teacher', NULL, 'evemil', 'approved', 10, '$2y$10$0lgXys/byN8FbrY/rjg3TeHx/s4afGhMckxChroqw1Tl5AtCMG.Oq', 0, 0),
(25, 'ShellaCora', 'ShelaCor', 'she@gmail.com', 'Teacher', NULL, 'Zebra', 'pending', 10, '$2y$10$3fYtaoj/8djfaLZmo.AO6uwPANCqkFuZ49eKOQHdbzYjJVaql1.GO', 0, 0),
(26, 'Ines Vanguardia', 'ines2020', 'ines@gmail.com', 'Teacher', NULL, 'pink', 'approved', 1, '$2y$10$xdn0B4ICcViXfVxzHvFw3.eUP5YHkanYvkg5B.X9GT8jpvAylAyae', 0, 0),
(27, 'my ness', 'myness', 'myness@gmail.com', 'Teacher', NULL, 'pink', 'approved', 1, '$2y$10$2s9jteXzm.K4wJ5EvQr10eqPRszgerduazGoDDhLCy5aJgKivSJiu', 0, 0),
(28, 'TesterFreeyo', 'TesterFreeyo', 'Tester@gmail.com', 'Teacher', NULL, '123', 'approved', 1, '$2y$10$1TDBwI1bdkg1984SM27GhOeI7QWno9KY2ZkXDjq9ttdtnna1jcpnu', 0, 0),
(29, 'Adminsub', 'AdminSub123', 'Adminsub@gmail.com', 'Admin', NULL, '123', 'pending', 2, '$2y$10$XkNdrvNdJvYHxDs6btTVOu7iuPZqCD0ZN6r319kcB8VXJOf/ssD3e', 0, 0),
(30, 'james', 'marjameson', 'james@gmail.com', 'Teacher', NULL, 'black', 'approved', 1, '$2y$10$KYs2NA2XqozAALC.sXDTke7c6dNSu34.qhrZczl.bW5Y9qVqG86Oi', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `userfileserverfiles`
--

CREATE TABLE `userfileserverfiles` (
  `file_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userfileserverfiles`
--

INSERT INTO `userfileserverfiles` (`file_id`, `folder_id`, `file_name`, `file_path`, `uploaded_at`, `userid`) VALUES
(1, 1, 'Form 14.jpg', 'uploads/Form 14.jpg', '2024-08-28 09:02:41', 0),
(2, 1, 'Account Settings.png', 'uploads/Account Settings.png', '2024-08-28 09:10:34', 0),
(3, 1, '1233333.png', 'uploads/1233333.png', '2024-08-28 09:11:13', 0),
(4, 1, '1233333.zip', 'uploads/1233333.zip', '2024-08-28 09:12:46', 0),
(5, 2, 'Form 14.jpg', 'uploads/Form 14.jpg', '2024-08-28 09:14:10', 0),
(6, 3, '1233333 (1).png', 'uploads/1233333 (1).png', '2024-08-28 09:52:37', 0),
(9, 13, '456724193_885042150151321_5238812809017422679_n.jpg', 'uploads/456724193_885042150151321_5238812809017422679_n.jpg', '2024-08-28 10:16:56', 0),
(13, 2, '1233333 (1).png', 'uploads/1233333 (1).png', '2024-08-28 11:29:26', 0),
(15, 27, 'Step 1 Implementation.txt', 'uploads/Step 1 Implementation.txt', '2024-08-28 13:13:25', 0),
(16, 14, '457273835_1196100201718595_6999637955955575012_n.jpg', 'C:\\xampp\\htdocs\\LanaoNationalHighschoolTeachersPortal\\Home/uploads/457273835_1196100201718595_6999637955955575012_n.jpg', '2024-08-29 11:49:37', 0),
(19, 13, 'grading_management (10).sql.gz', 'C:\\xampp\\htdocs\\LanaoNationalHighschoolTeachersPortal\\Home/uploads/grading_management (10).sql.gz', '2024-09-14 06:17:35', 10);

-- --------------------------------------------------------

--
-- Table structure for table `userfileserverfolders`
--

CREATE TABLE `userfileserverfolders` (
  `folder_id` int(11) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `folder_password` varchar(255) NOT NULL,
  `userid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `userfileserverfolders`
--

INSERT INTO `userfileserverfolders` (`folder_id`, `folder_name`, `folder_password`, `userid`) VALUES
(1, 'Goods', '$2y$10$.xih6UQdMqRfD34H8aizqOAOxvGehuQ7C.cEmwWR5YZbogufoKOai', 0),
(2, '123', '$2y$10$GtBBd5sK8uKo4nCp4DaB4ejP7JOVtQS08MBn6rTpRG0vbO91UiFvC', 0),
(3, '321', '$2y$10$RMelRG50D8hIuPZYwvGu/.JWWVd1rFsAIF3IZUM0nuYRiMsOqC1yy', 0),
(4, '321', '$2y$10$GCJpzzxyvbQRDAm64ID7h.x3VPakIgQp0by4PGAlX9Cmi2SQG996W', 0),
(13, 'Antonio', '$2y$10$qVtfsJfguWIKtwiRqE0VGOI2LlD707b8856Ca1k99d/xVXN/myv4m', 10),
(14, 'Testing', '$2y$10$cnLK3WBVEDPJjwPRIxmNhux7Pyk5bb75EzmMY/Og9HNGIHvz4V6h2', 10),
(15, 'Degz', '$2y$10$9NszVMgM0.jyGzGsQfuIBuJ6TVNx4J96vLi.FHQooaaAcM8czBL6O', 8),
(16, '123', '$2y$10$1XRGH6BvvJTXdGyUF7bbeOc4ofR9DFIoaFR/mRS9JUByRwRNsSvO2', 12),
(19, '123', '$2y$10$tcENnGUeIgsrLEV0.oqOeOZKmNuF2GRLGUuzygKc3qGFw17P6xj5C', 8),
(21, '123', '$2y$10$MzHYF5VOkdKGHMPu6eHqReFzbdTZ7HzkWHuqMZ3DLwGuqYsDgBlru', 10),
(23, 'Delete', '$2y$10$FvXWgA.20gz.9kSgj9jXi.zZTLZpmfJ1VeM8Ly3N9kMmtD8AquWAe', 8),
(24, '123', '$2y$10$qE5iBpIHQqo/xMkLtRRPG.8tiYzY.kHGOK0zRbnfGaKuFkJNztbLW', 8),
(25, '123', '$2y$10$wHMGPZbJA1Ql6093k/pfX.r8fTTzKX1tjWHKoHl8Wvz7BEMX9gTYm', 12),
(26, 'SchoolWorks', '$2y$10$sjifcUo8WaqobHUSr0CoHex4esEgYZFvOtLHQIOYelgpet761MOBW', 93),
(27, 'SchoolWorksv2', '$2y$10$BXDPfrvEQGEpWSK25cUi8uLBu1kUgd59ANXBzLN9kPUBRkVGZV0ya', 93),
(28, 'Degoma\'s folder', '$2y$10$e.Azbb3XEBSu3jJ3aHx/DeukByGcs9Y52MbmUGNCcf2i8TfpPXBKG', 94),
(29, 'Userfolder', '$2y$10$c28THa1mHfIKGFhA5TtYUurd0B9HYZCAvzF0NFfKsCKJI/DO5sTZq', 12),
(30, 'sample tester', '$2y$10$nCvHttUetn69u6Gqvs8aNe0dgElJSPIXyZbOg2Y.r8AIbH7sLYYPe', 10),
(31, '123', '$2y$10$NF6qrcTxBQg9X20FWvT/6OHN1UrR2CVLYqtLIENTIMYx.BWAiGh/u', 104),
(32, 'ssssssssssssss', '$2y$10$nkwDeraZWmrANVCrgn566O49SFUEOHUHWaG3oFMwypplqQqpfQdUC', 104),
(34, 'Marie\'s documents', '$2y$10$2CxmUVHQna6YqYthrO330eb5y1zbd2moUHeAGAN0kf8E45Y6wWuYC', 131);

-- --------------------------------------------------------

--
-- Structure for view `teacher_details`
--
DROP TABLE IF EXISTS `teacher_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `teacher_details`  AS SELECT `u`.`userid` AS `userid`, `u`.`name` AS `name`, `u`.`username` AS `username`, count(distinct `a`.`subject_id`) AS `handled_subjects`, count(distinct `a`.`grade_section`) AS `handled_sections` FROM (`user` `u` left join `assessments` `a` on(`u`.`userid` = `a`.`subject_id`)) WHERE `u`.`role` = 'Teacher' GROUP BY `u`.`userid`, `u`.`name`, `u`.`username` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `approval_history`
--
ALTER TABLE `approval_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assessment_summary`
--
ALTER TABLE `assessment_summary`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assessment_approval_status` (`approval_status`);

--
-- Indexes for table `assessment_types`
--
ALTER TABLE `assessment_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `encoded_learner_data`
--
ALTER TABLE `encoded_learner_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fileserver_files`
--
ALTER TABLE `fileserver_files`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `fileserver_folders`
--
ALTER TABLE `fileserver_folders`
  ADD PRIMARY KEY (`folder_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indexes for table `grade_approvals`
--
ALTER TABLE `grade_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `grading_scale`
--
ALTER TABLE `grading_scale`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `point_setter`
--
ALTER TABLE `point_setter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_questions`
--
ALTER TABLE `security_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student_grades_student` (`student_id`),
  ADD KEY `fk_student_grades_subject` (`subject_id`);

--
-- Indexes for table `student_quiz`
--
ALTER TABLE `student_quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_student_quiz_student` (`student_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student_id` (`student_id`),
  ADD KEY `idx_subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_assignment` (`teacher_id`,`subject_id`,`grade_section`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userid`),
  ADD KEY `idx_user_status` (`status`);

--
-- Indexes for table `userfileserverfiles`
--
ALTER TABLE `userfileserverfiles`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `userfileserverfolders`
--
ALTER TABLE `userfileserverfolders`
  ADD PRIMARY KEY (`folder_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `approval_history`
--
ALTER TABLE `approval_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `assessment_summary`
--
ALTER TABLE `assessment_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `assessment_types`
--
ALTER TABLE `assessment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `encoded_learner_data`
--
ALTER TABLE `encoded_learner_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fileserver_files`
--
ALTER TABLE `fileserver_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fileserver_folders`
--
ALTER TABLE `fileserver_folders`
  MODIFY `folder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grade_approvals`
--
ALTER TABLE `grade_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grading_scale`
--
ALTER TABLE `grading_scale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `point_setter`
--
ALTER TABLE `point_setter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=203;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_quiz`
--
ALTER TABLE `student_quiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `userfileserverfiles`
--
ALTER TABLE `userfileserverfiles`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `userfileserverfolders`
--
ALTER TABLE `userfileserverfolders`
  MODIFY `folder_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grade_approvals`
--
ALTER TABLE `grade_approvals`
  ADD CONSTRAINT `grade_approvals_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`userid`),
  ADD CONSTRAINT `grade_approvals_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `fk_student_grades_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `fk_student_grades_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `student_quiz`
--
ALTER TABLE `student_quiz`
  ADD CONSTRAINT `fk_student_quiz_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `teacher_assignments`
--
ALTER TABLE `teacher_assignments`
  ADD CONSTRAINT `teacher_assignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`userid`),
  ADD CONSTRAINT `teacher_assignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
