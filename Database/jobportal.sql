-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2026 at 01:53 PM
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
-- Database: `jobportal`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidate_profiles`
--

CREATE TABLE `candidate_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `headline` varchar(255) DEFAULT 'Aspiring Software Developer',
  `phone` varchar(20) DEFAULT NULL,
  `degree` varchar(100) DEFAULT 'B.Tech Computer Science',
  `institution` varchar(255) DEFAULT NULL,
  `graduation_year` varchar(10) DEFAULT '2025',
  `experience_level` varchar(50) DEFAULT 'Fresher',
  `skills` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `resume_url` varchar(255) DEFAULT NULL,
  `portfolio_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT 'Ahmedabad',
  `state` varchar(100) DEFAULT 'Gujarat',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate_profiles`
--

INSERT INTO `candidate_profiles` (`id`, `user_id`, `headline`, `phone`, `degree`, `institution`, `graduation_year`, `experience_level`, `skills`, `bio`, `resume_url`, `portfolio_url`, `github_url`, `linkedin_url`, `city`, `state`, `created_at`, `updated_at`) VALUES
(1, 9, 'Frontend Engineer & UI Specialist', '9876543210', 'B.Tech Computer Science', 'Gujarat Technological University', '2025', 'Fresher', 'React, JavaScript, CSS3, HTML5, Bootstrap, Git, Figma', 'Enthusiastic developer dedicated to building responsive, accessible web applications.', NULL, NULL, NULL, NULL, 'Ahmedabad', 'Gujarat', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(2, 1, 'Aspiring Professional', NULL, 'Bachelor Degree', NULL, '2025', 'Fresher', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmedabad', 'Gujarat', '2026-08-21 09:32:30', '2026-08-21 09:32:30'),
(3, 5, 'Aspiring Professional', NULL, 'Bachelor Degree', NULL, '2025', 'Fresher', NULL, NULL, NULL, NULL, NULL, NULL, 'Ahmedabad', 'Gujarat', '2026-08-21 11:42:52', '2026-08-21 11:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `industry` varchar(100) NOT NULL DEFAULT 'Information Technology',
  `location` varchar(100) NOT NULL DEFAULT 'Bengaluru, India',
  `website` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `industry`, `location`, `website`, `email`, `logo`, `about`, `created_at`) VALUES
(1, 'Google Cloud', 'Cloud Computing & AI', 'Bengaluru, Karnataka', 'https://cloud.google.com', 'careers@google.com', 'fa-brands fa-google', 'Leading the world in scalable cloud infrastructure and enterprise artificial intelligence solutions.', '2026-08-21 09:07:20'),
(2, 'Microsoft India', 'Software & Cloud', 'Hyderabad, Telangana', 'https://microsoft.com', 'jobs@microsoft.com', 'fa-brands fa-microsoft', 'Empowering every person and organization on the planet to achieve more through software innovation.', '2026-08-21 09:07:20'),
(3, 'Amazon Web Services', 'E-Commerce & Cloud', 'Bengaluru, Karnataka', 'https://aws.amazon.com', 'aws-careers@amazon.com', 'fa-brands fa-aws', 'World leading cloud computing platform powering modern web infrastructure.', '2026-08-21 09:07:20'),
(4, 'Razorpay Technologies', 'Fintech & Payments', 'Bengaluru, Karnataka', 'https://razorpay.com', 'careers@razorpay.com', 'fa-solid fa-credit-card', 'Pioneering unified payments and banking gateway solutions for businesses across India.', '2026-08-21 09:07:20'),
(5, 'Tata Consultancy Services', 'IT Services & Consulting', 'Mumbai, Maharashtra', 'https://tcs.com', 'recruitment@tcs.com', 'fa-solid fa-building', 'Global IT services and digital transformation leader with a workforce across 50+ countries.', '2026-08-21 09:07:20'),
(6, 'Infosys Ltd', 'IT Consulting & Services', 'Bengaluru, Karnataka', 'https://infosys.com', 'jobs@infosys.com', 'fa-solid fa-laptop-code', 'Global leader in next-generation digital services and consulting solutions.', '2026-08-21 09:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `jobregistration`
--

CREATE TABLE `jobregistration` (
  `id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `degree` varchar(255) NOT NULL,
  `mobile` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `refer` varchar(255) NOT NULL,
  `jobpost` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobregistration`
--

INSERT INTO `jobregistration` (`id`, `name`, `degree`, `mobile`, `email`, `refer`, `jobpost`) VALUES
(1, 'Admin', 'BCA', '1236547895', 'admin@gmail.com', 'AAA', 'Web Developer'),
(2, 'User', 'BBA', '0012365478', 'userr@example.com', 'BBB', 'Frontend Developer');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `company_logo` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `job_type` varchar(50) NOT NULL DEFAULT 'Full-Time',
  `experience_level` varchar(50) NOT NULL DEFAULT 'Fresher',
  `location` varchar(100) NOT NULL DEFAULT 'Remote / Hybrid',
  `salary_range` varchar(100) NOT NULL DEFAULT '₹6,00,000 - ₹10,00,000 PA',
  `openings` int(11) NOT NULL DEFAULT 2,
  `description` text NOT NULL,
  `requirements` text NOT NULL,
  `benefits` text DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `deadline` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `company_name`, `company_logo`, `title`, `category`, `job_type`, `experience_level`, `location`, `salary_range`, `openings`, `description`, `requirements`, `benefits`, `status`, `deadline`, `created_at`, `updated_at`) VALUES
(1, 1, 'Google Cloud', 'fa-brands fa-google', 'Full Stack Web Developer', 'Web Development', 'Full-Time', '1-3 Years', 'Bengaluru (Hybrid)', '₹12,00,000 - ₹18,00,000 PA', 3, 'We are looking for an ambitious Full Stack Developer to build scalable enterprise cloud portals and high-performance microservices. You will collaborate with cross-functional engineering teams to craft responsive user experiences and solid backend services.', '• Strong proficiency in PHP, JavaScript (ES6+), React/Vue, and Node.js or Python.\n• Experience designing RESTful APIs and working with MySQL/PostgreSQL databases.\n• Familiarity with Git version control, Docker containers, and CI/CD pipelines.\n• Good understanding of security best practices (OAuth, CSRF/XSS protection).', '• Comprehensive health, dental, and vision insurance.\n• Flexible hybrid working options and learning allowances.\n• Annual performance bonuses and ESOP equity grants.', 'Active', '2026-10-05', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(2, 2, 'Microsoft India', 'fa-brands fa-microsoft', 'Frontend React & UI Engineer', 'Frontend Development', 'Full-Time', 'Fresher / Entry Level', 'Hyderabad (Remote Friendly)', '₹9,00,000 - ₹14,00,000 PA', 4, 'Join our UI engineering team to build fluid, pixel-perfect web interfaces that delight millions of daily enterprise users. You will transform Figma designs into accessible, accessible, and ultra-fast web components.', '• Solid foundation in HTML5, CSS3, modern JavaScript, and TypeScript.\n• Hands-on experience with modern CSS frameworks (Bootstrap 5, Tailwind CSS, or Vanilla CSS variables).\n• Strong focus on web performance, cross-browser compatibility, and WCAG accessibility standards.', '• Generous wellness stipends and home office setup reimbursement.\n• Dedicated mentorship from principal architects.\n• Regular hackathons and international learning conferences.', 'Active', '2026-09-20', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(3, 3, 'Amazon Web Services', 'fa-brands fa-aws', 'Backend Systems & API Engineer', 'Backend Development', 'Full-Time', '1-3 Years', 'Bengaluru (On-site)', '₹14,00,000 - ₹20,00,000 PA', 2, 'We are seeking a Backend Engineer with solid database fundamentals to design, optimize, and maintain high-throughput backend services and data pipelines.', '• Proficiency with PHP, Node.js, or Go with robust relational database experience (MySQL / Aurora).\n• Experience in indexing strategies, query profiling, and data schema normalization.\n• Understanding of caching architectures (Redis/Memcached) and message queues (RabbitMQ/Kafka).', '• World-class compensation package and stock units.\n• Comprehensive relocation assistance.\n• Continuous career advancement pathways.', 'Active', '2026-10-20', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(4, 4, 'Razorpay Technologies', 'fa-solid fa-credit-card', 'UI/UX Product Designer', 'UI/UX Design', 'Full-Time', 'Fresher / Entry Level', 'Bengaluru / Remote', '₹8,00,000 - ₹12,00,000 PA', 2, 'Craft next-generation fintech checkout and dashboard experiences. You will conduct user research, wireframing, rapid prototyping in Figma, and build cohesive design systems.', '• Portfolio demonstrating strong typography, color balance, visual hierarchy, and interaction design.\n• Proficiency in Figma, FigJam, and design system tokenization.\n• Understanding of frontend constraints (HTML/CSS) to communicate effectively with engineering teams.', '• Unlimited paid time off (PTO) policy.\n• Premium health cover for candidate and family.\n• Annual gadget upgrade budget.', 'Active', '2026-09-15', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(5, 5, 'Tata Consultancy Services', 'fa-solid fa-building', 'Junior Web Developer & Graduate Trainee', 'Web Development', 'Full-Time', 'Fresher', 'Ahmedabad / Pune', '₹4,50,000 - ₹7,00,000 PA', 10, 'Great launchpad for engineering graduates and BCA/MCA passouts. You will receive extensive structured training on modern web architectures before contributing to enterprise client systems.', '• Degree in Computer Science, IT, BCA, MCA, or related technical disciplines.\n• Good problem-solving skills and working knowledge of HTML, CSS, JavaScript, PHP/MySQL.\n• Excellent communication and teamwork skills.', '• Comprehensive 3-month onboarding and cloud certification sponsorship.\n• Fixed working hours and great work-life balance.\n• Provident fund, gratuity, and medical benefits.', 'Active', '2026-09-30', '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(6, 6, 'Infosys Ltd', 'fa-solid fa-laptop-code', 'DevOps & Cloud Infrastructure Trainee', 'DevOps & Cloud', 'Full-Time', 'Fresher / Entry Level', 'Bengaluru / Hyderabad', '₹6,50,000 - ₹9,50,000 PA', 3, 'Automate build, deployment, and monitoring pipelines across hybrid cloud environments. Work with Kubernetes, Linux servers, and GitHub Actions.', '• Familiarity with Linux command line environments and Shell/Bash/Python scripting.\n• Basic understanding of web servers (Apache, Nginx), DNS, SSL/TLS, and Docker containers.\n• Eagerness to learn cloud platforms (AWS, Azure, or GCP).', '• Global relocation opportunities upon project assignment.\n• Subsidized cafeteria and transport facilities.\n• Higher education assistance programs.', 'Active', '2026-10-10', '2026-08-21 09:07:20', '2026-08-21 09:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(50) NOT NULL,
  `degree` varchar(255) NOT NULL,
  `refer` varchar(255) DEFAULT 'None',
  `jobpost` varchar(255) NOT NULL,
  `skills` text DEFAULT NULL,
  `experience` varchar(50) DEFAULT 'Fresher',
  `resume_link` varchar(255) DEFAULT NULL,
  `cover_note` text DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Applied',
  `admin_notes` text DEFAULT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_id`, `user_id`, `name`, `email`, `mobile`, `degree`, `refer`, `jobpost`, `skills`, `experience`, `resume_link`, `cover_note`, `status`, `admin_notes`, `applied_at`, `updated_at`) VALUES
(1, 1, 6, 'Admin', 'admin@gmail.com', '1236547895', 'BCA', 'Vishal', 'Web Developer', 'HTML, CSS, JavaScript, PHP, MySQL', 'Fresher / 1 Year', NULL, NULL, 'Applied', NULL, '2026-08-21 09:07:20', '2026-08-21 09:07:20'),
(2, 1, NULL, 'User', 'User@example.com', '0012365478', 'BBA', 'AAA', 'Frontend Developer', 'HTML, CSS, JavaScript, PHP, MySQL', 'Fresher / 1 Year', NULL, NULL, 'Under Review', NULL, '2026-08-21 09:07:20', '2026-08-21 09:07:20');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'testuser_1787138632@example.com', '00ee4e73a00059e1176d48c37f7408a1e51b4a70699085420ca88a36d3de037b', '2026-08-19 14:23:52', '2026-08-19 11:23:52'),
(6, 'admin@gmail.com', '382eb11b61bddc9e291555029e22c1276976f51a106a874271f5b568d5f59230', '2026-08-20 13:55:08', '2026-08-20 07:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `saved_jobs`
--

CREATE TABLE `saved_jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'candidate',
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `password`, `created_at`, `updated_at`, `last_login`, `is_active`) VALUES
(1, 'Admin User', 'admin@example.com', 'admin', '$2y$10$qQ8UI8b5xvziRa4Ijq7Xc.uPZWQ6xh.KowNJRacDX9TOlWoW3GAHO', '2026-08-18 08:02:24', '2026-08-21 11:46:07', '2026-08-21 17:16:07', 1),
(2, 'John Doe', 'john@example.com', 'candidate', 'john123!', '2026-08-18 08:02:24', '2026-08-18 08:08:19', NULL, 1),
(3, 'Test Engineer', 'testuser_1787138632@example.com', 'candidate', '$2y$10$B/Npuz41Q1sfrUfzwLob4.gvnKpeWZKrwrDqiRHK7G/U73JWrbpv.', '2026-08-19 11:23:52', '2026-08-19 11:23:52', NULL, 1),
(5, 'Alex Morgan', 'alex.morgan@example.com', 'candidate', '$2y$10$dsyZ18OL0WcbO2PWczxAROVkQw5/QhUhkJRtfJkUmzKPt5ZSGhCFu', '2026-08-19 11:27:14', '2026-08-21 11:44:02', '2026-08-21 17:14:02', 1),
(6, 'Fenil Modi', 'fenilmodi1@gmail.com', 'candidate', '$2y$10$J9EKcaA76j9ERvZk1vRiHO4XNcyiKgtZlBmt0MTOJnMAURuIaFZOO', '2026-08-19 11:34:51', '2026-08-20 07:22:00', '2026-08-20 12:52:00', 1),
(7, 'Dhavni Gulale', 'dhvani@gmail.com', 'candidate', '$2y$10$KTIiART6l3l1vOuEUFUqV.olG94NsrT0CsP.pW/Jqnmc0KsLt1PdS', '2026-08-19 11:40:34', '2026-08-19 11:41:39', '2026-08-19 17:11:39', 1),
(8, 'Dev Modi', 'dev123@example.com', 'candidate', '$2y$10$hHtGxwHCklX.hcIVA7L4Fuc4DLxJS0D8cSFsV7af7TgFF0EkkifDa', '2026-08-19 14:10:19', '2026-08-19 14:15:41', '2026-08-19 19:45:41', 1),
(9, 'Alex Johnson', 'candidate@example.com', 'candidate', '$2y$10$M0KYMQ0jYkBRoyj8.sCfL.B2Ca5WjdrALC8R/2RDdgXZLtjgxBdqe', '2026-08-21 09:07:20', '2026-08-21 11:07:40', '2026-08-21 16:37:40', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidate_profiles`
--
ALTER TABLE `candidate_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `user_id_2` (`user_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobregistration`
--
ALTER TABLE `jobregistration`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `status` (`status`),
  ADD KEY `job_type` (`job_type`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_job` (`user_id`,`job_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidate_profiles`
--
ALTER TABLE `candidate_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `jobregistration`
--
ALTER TABLE `jobregistration`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `saved_jobs`
--
ALTER TABLE `saved_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
