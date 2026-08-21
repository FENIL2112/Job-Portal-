<?php
require_once __DIR__ . '/connection.php';

echo "=== Initializing Job Portal Database Migration ===\n\n";

// 1. Update 'users' table - add 'role' column if not exists
$checkRoleCol = mysqli_query($con, "SHOW COLUMNS FROM `users` LIKE 'role'");
if (mysqli_num_rows($checkRoleCol) === 0) {
    $alterUsers = "ALTER TABLE `users` ADD `role` VARCHAR(20) NOT NULL DEFAULT 'candidate' AFTER `email`";
    if (mysqli_query($con, $alterUsers)) {
        echo "✓ Added 'role' column to 'users' table.\n";
    } else {
        echo "✗ Failed to alter 'users' table: " . mysqli_error($con) . "\n";
    }
} else {
    echo "✓ 'role' column already exists in 'users' table.\n";
}

// 2. Set Admin role for Admin accounts or create default admin
$adminEmail = 'admin@example.com';
$adminCheck = mysqli_query($con, "SELECT id FROM `users` WHERE `email` = '$adminEmail'");
if (mysqli_num_rows($adminCheck) > 0) {
    mysqli_query($con, "UPDATE `users` SET `role` = 'admin', `is_active` = 1 WHERE `email` = '$adminEmail'");
    echo "✓ Updated existing admin account ($adminEmail) to role 'admin'.\n";
} else {
    $adminPass = password_hash('Admin@12345', PASSWORD_DEFAULT);
    $insertAdmin = "INSERT INTO `users` (`name`, `email`, `role`, `password`, `is_active`, `created_at`) 
                    VALUES ('Portal Administrator', '$adminEmail', 'admin', '$adminPass', 1, NOW())";
    if (mysqli_query($con, $insertAdmin)) {
        echo "✓ Created default admin account: $adminEmail (Password: Admin@12345)\n";
    }
}

// Set Candidate role for other users who don't have a role set
mysqli_query($con, "UPDATE `users` SET `role` = 'candidate' WHERE `role` IS NULL OR `role` = '' OR `role` NOT IN ('admin', 'candidate')");

// Also ensure a sample candidate demo user exists: candidate@example.com (Candidate@12345)
$candidateEmail = 'candidate@example.com';
$candCheck = mysqli_query($con, "SELECT id FROM `users` WHERE `email` = '$candidateEmail'");
if (mysqli_num_rows($candCheck) === 0) {
    $candPass = password_hash('Candidate@12345', PASSWORD_DEFAULT);
    $insertCand = "INSERT INTO `users` (`name`, `email`, `role`, `password`, `is_active`, `created_at`) 
                   VALUES ('Alex Johnson', '$candidateEmail', 'candidate', '$candPass', 1, NOW())";
    if (mysqli_query($con, $insertCand)) {
        echo "✓ Created demo candidate account: $candidateEmail (Password: Candidate@12345)\n";
    }
}

// 3. Create 'candidate_profiles' table
$sqlCandidateProfiles = "CREATE TABLE IF NOT EXISTS `candidate_profiles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL UNIQUE,
    `headline` VARCHAR(255) DEFAULT 'Aspiring Software Developer',
    `phone` VARCHAR(20) DEFAULT NULL,
    `degree` VARCHAR(100) DEFAULT 'B.Tech Computer Science',
    `institution` VARCHAR(255) DEFAULT NULL,
    `graduation_year` VARCHAR(10) DEFAULT '2025',
    `experience_level` VARCHAR(50) DEFAULT 'Fresher',
    `skills` TEXT DEFAULT NULL,
    `bio` TEXT DEFAULT NULL,
    `resume_url` VARCHAR(255) DEFAULT NULL,
    `portfolio_url` VARCHAR(255) DEFAULT NULL,
    `github_url` VARCHAR(255) DEFAULT NULL,
    `linkedin_url` VARCHAR(255) DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT 'Ahmedabad',
    `state` VARCHAR(100) DEFAULT 'Gujarat',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($con, $sqlCandidateProfiles)) {
    echo "✓ Table 'candidate_profiles' ready.\n";
} else {
    echo "✗ Failed to create 'candidate_profiles': " . mysqli_error($con) . "\n";
}

// 4. Create 'companies' table
$sqlCompanies = "CREATE TABLE IF NOT EXISTS `companies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `industry` VARCHAR(100) NOT NULL DEFAULT 'Information Technology',
    `location` VARCHAR(100) NOT NULL DEFAULT 'Bengaluru, India',
    `website` VARCHAR(255) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `logo` VARCHAR(255) DEFAULT NULL,
    `about` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($con, $sqlCompanies)) {
    echo "✓ Table 'companies' ready.\n";
} else {
    echo "✗ Failed to create 'companies': " . mysqli_error($con) . "\n";
}

// 5. Create 'jobs' table
$sqlJobs = "CREATE TABLE IF NOT EXISTS `jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_id` INT DEFAULT NULL,
    `company_name` VARCHAR(255) NOT NULL,
    `company_logo` VARCHAR(255) DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `job_type` VARCHAR(50) NOT NULL DEFAULT 'Full-Time',
    `experience_level` VARCHAR(50) NOT NULL DEFAULT 'Fresher',
    `location` VARCHAR(100) NOT NULL DEFAULT 'Remote / Hybrid',
    `salary_range` VARCHAR(100) NOT NULL DEFAULT '₹6,00,000 - ₹10,00,000 PA',
    `openings` INT NOT NULL DEFAULT 2,
    `description` TEXT NOT NULL,
    `requirements` TEXT NOT NULL,
    `benefits` TEXT DEFAULT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'Active',
    `deadline` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`category`),
    INDEX (`status`),
    INDEX (`job_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($con, $sqlJobs)) {
    echo "✓ Table 'jobs' ready.\n";
} else {
    echo "✗ Failed to create 'jobs': " . mysqli_error($con) . "\n";
}

// 6. Create 'job_applications' table (ATS)
$sqlJobApplications = "CREATE TABLE IF NOT EXISTS `job_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `mobile` VARCHAR(50) NOT NULL,
    `degree` VARCHAR(255) NOT NULL,
    `refer` VARCHAR(255) DEFAULT 'None',
    `jobpost` VARCHAR(255) NOT NULL,
    `skills` TEXT DEFAULT NULL,
    `experience` VARCHAR(50) DEFAULT 'Fresher',
    `resume_link` VARCHAR(255) DEFAULT NULL,
    `cover_note` TEXT DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'Applied',
    `admin_notes` TEXT DEFAULT NULL,
    `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX (`job_id`),
    INDEX (`user_id`),
    INDEX (`status`),
    INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($con, $sqlJobApplications)) {
    echo "✓ Table 'job_applications' ready.\n";
} else {
    echo "✗ Failed to create 'job_applications': " . mysqli_error($con) . "\n";
}

// 7. Create 'saved_jobs' table
$sqlSavedJobs = "CREATE TABLE IF NOT EXISTS `saved_jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `job_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_job` (`user_id`, `job_id`),
    INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($con, $sqlSavedJobs)) {
    echo "✓ Table 'saved_jobs' ready.\n";
} else {
    echo "✗ Failed to create 'saved_jobs': " . mysqli_error($con) . "\n";
}

// 8. Seed Companies if empty
$compCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `companies`");
$compCount = mysqli_fetch_assoc($compCountRes)['cnt'] ?? 0;

if ($compCount == 0) {
    $companies = [
        ['Google Cloud', 'Cloud Computing & AI', 'Bengaluru, Karnataka', 'https://cloud.google.com', 'careers@google.com', 'fa-brands fa-google', 'Leading the world in scalable cloud infrastructure and enterprise artificial intelligence solutions.'],
        ['Microsoft India', 'Software & Cloud', 'Hyderabad, Telangana', 'https://microsoft.com', 'jobs@microsoft.com', 'fa-brands fa-microsoft', 'Empowering every person and organization on the planet to achieve more through software innovation.'],
        ['Amazon Web Services', 'E-Commerce & Cloud', 'Bengaluru, Karnataka', 'https://aws.amazon.com', 'aws-careers@amazon.com', 'fa-brands fa-aws', 'World leading cloud computing platform powering modern web infrastructure.'],
        ['Razorpay Technologies', 'Fintech & Payments', 'Bengaluru, Karnataka', 'https://razorpay.com', 'careers@razorpay.com', 'fa-solid fa-credit-card', 'Pioneering unified payments and banking gateway solutions for businesses across India.'],
        ['Tata Consultancy Services', 'IT Services & Consulting', 'Mumbai, Maharashtra', 'https://tcs.com', 'recruitment@tcs.com', 'fa-solid fa-building', 'Global IT services and digital transformation leader with a workforce across 50+ countries.'],
        ['Infosys Ltd', 'IT Consulting & Services', 'Bengaluru, Karnataka', 'https://infosys.com', 'jobs@infosys.com', 'fa-solid fa-laptop-code', 'Global leader in next-generation digital services and consulting solutions.']
    ];

    $stmtComp = mysqli_prepare($con, "INSERT INTO `companies` (`name`, `industry`, `location`, `website`, `email`, `logo`, `about`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($companies as $c) {
        mysqli_stmt_bind_param($stmtComp, "sssssss", $c[0], $c[1], $c[2], $c[3], $c[4], $c[5], $c[6]);
        mysqli_stmt_execute($stmtComp);
    }
    mysqli_stmt_close($stmtComp);
    echo "✓ Seeded 6 premier companies.\n";
}

// 9. Seed Jobs if empty
$jobCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `jobs`");
$jobCount = mysqli_fetch_assoc($jobCountRes)['cnt'] ?? 0;

if ($jobCount == 0) {
    $jobs = [
        [
            1, 'Google Cloud', 'fa-brands fa-google', 'Full Stack Web Developer', 'Web Development', 'Full-Time', '1-3 Years',
            'Bengaluru (Hybrid)', '₹12,00,000 - ₹18,00,000 PA', 3,
            'We are looking for an ambitious Full Stack Developer to build scalable enterprise cloud portals and high-performance microservices. You will collaborate with cross-functional engineering teams to craft responsive user experiences and solid backend services.',
            "• Strong proficiency in PHP, JavaScript (ES6+), React/Vue, and Node.js or Python.\n• Experience designing RESTful APIs and working with MySQL/PostgreSQL databases.\n• Familiarity with Git version control, Docker containers, and CI/CD pipelines.\n• Good understanding of security best practices (OAuth, CSRF/XSS protection).",
            "• Comprehensive health, dental, and vision insurance.\n• Flexible hybrid working options and learning allowances.\n• Annual performance bonuses and ESOP equity grants.",
            'Active', date('Y-m-d', strtotime('+45 days'))
        ],
        [
            2, 'Microsoft India', 'fa-brands fa-microsoft', 'Frontend React & UI Engineer', 'Frontend Development', 'Full-Time', 'Fresher / Entry Level',
            'Hyderabad (Remote Friendly)', '₹9,00,000 - ₹14,00,000 PA', 4,
            'Join our UI engineering team to build fluid, pixel-perfect web interfaces that delight millions of daily enterprise users. You will transform Figma designs into accessible, accessible, and ultra-fast web components.',
            "• Solid foundation in HTML5, CSS3, modern JavaScript, and TypeScript.\n• Hands-on experience with modern CSS frameworks (Bootstrap 5, Tailwind CSS, or Vanilla CSS variables).\n• Strong focus on web performance, cross-browser compatibility, and WCAG accessibility standards.",
            "• Generous wellness stipends and home office setup reimbursement.\n• Dedicated mentorship from principal architects.\n• Regular hackathons and international learning conferences.",
            'Active', date('Y-m-d', strtotime('+30 days'))
        ],
        [
            3, 'Amazon Web Services', 'fa-brands fa-aws', 'Backend Systems & API Engineer', 'Backend Development', 'Full-Time', '1-3 Years',
            'Bengaluru (On-site)', '₹14,00,000 - ₹20,00,000 PA', 2,
            'We are seeking a Backend Engineer with solid database fundamentals to design, optimize, and maintain high-throughput backend services and data pipelines.',
            "• Proficiency with PHP, Node.js, or Go with robust relational database experience (MySQL / Aurora).\n• Experience in indexing strategies, query profiling, and data schema normalization.\n• Understanding of caching architectures (Redis/Memcached) and message queues (RabbitMQ/Kafka).",
            "• World-class compensation package and stock units.\n• Comprehensive relocation assistance.\n• Continuous career advancement pathways.",
            'Active', date('Y-m-d', strtotime('+60 days'))
        ],
        [
            4, 'Razorpay Technologies', 'fa-solid fa-credit-card', 'UI/UX Product Designer', 'UI/UX Design', 'Full-Time', 'Fresher / Entry Level',
            'Bengaluru / Remote', '₹8,00,000 - ₹12,00,000 PA', 2,
            'Craft next-generation fintech checkout and dashboard experiences. You will conduct user research, wireframing, rapid prototyping in Figma, and build cohesive design systems.',
            "• Portfolio demonstrating strong typography, color balance, visual hierarchy, and interaction design.\n• Proficiency in Figma, FigJam, and design system tokenization.\n• Understanding of frontend constraints (HTML/CSS) to communicate effectively with engineering teams.",
            "• Unlimited paid time off (PTO) policy.\n• Premium health cover for candidate and family.\n• Annual gadget upgrade budget.",
            'Active', date('Y-m-d', strtotime('+25 days'))
        ],
        [
            5, 'Tata Consultancy Services', 'fa-solid fa-building', 'Junior Web Developer & Graduate Trainee', 'Web Development', 'Full-Time', 'Fresher',
            'Ahmedabad / Pune', '₹4,50,000 - ₹7,00,000 PA', 10,
            'Great launchpad for engineering graduates and BCA/MCA passouts. You will receive extensive structured training on modern web architectures before contributing to enterprise client systems.',
            "• Degree in Computer Science, IT, BCA, MCA, or related technical disciplines.\n• Good problem-solving skills and working knowledge of HTML, CSS, JavaScript, PHP/MySQL.\n• Excellent communication and teamwork skills.",
            "• Comprehensive 3-month onboarding and cloud certification sponsorship.\n• Fixed working hours and great work-life balance.\n• Provident fund, gratuity, and medical benefits.",
            'Active', date('Y-m-d', strtotime('+40 days'))
        ],
        [
            6, 'Infosys Ltd', 'fa-solid fa-laptop-code', 'DevOps & Cloud Infrastructure Trainee', 'DevOps & Cloud', 'Full-Time', 'Fresher / Entry Level',
            'Bengaluru / Hyderabad', '₹6,50,000 - ₹9,50,000 PA', 3,
            'Automate build, deployment, and monitoring pipelines across hybrid cloud environments. Work with Kubernetes, Linux servers, and GitHub Actions.',
            "• Familiarity with Linux command line environments and Shell/Bash/Python scripting.\n• Basic understanding of web servers (Apache, Nginx), DNS, SSL/TLS, and Docker containers.\n• Eagerness to learn cloud platforms (AWS, Azure, or GCP).",
            "• Global relocation opportunities upon project assignment.\n• Subsidized cafeteria and transport facilities.\n• Higher education assistance programs.",
            'Active', date('Y-m-d', strtotime('+50 days'))
        ]
    ];

    $stmtJob = mysqli_prepare($con, "INSERT INTO `jobs` 
        (`company_id`, `company_name`, `company_logo`, `title`, `category`, `job_type`, `experience_level`, `location`, `salary_range`, `openings`, `description`, `requirements`, `benefits`, `status`, `deadline`) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($jobs as $j) {
        mysqli_stmt_bind_param($stmtJob, "issssssssisssss", 
            $j[0], $j[1], $j[2], $j[3], $j[4], $j[5], $j[6], $j[7], $j[8], $j[9], $j[10], $j[11], $j[12], $j[13], $j[14]
        );
        mysqli_stmt_execute($stmtJob);
    }
    mysqli_stmt_close($stmtJob);
    echo "✓ Seeded 6 featured job postings.\n";
}

// 10. Sync existing records from 'jobregistration' into 'job_applications'
$appCountRes = mysqli_query($con, "SELECT COUNT(*) as cnt FROM `job_applications`");
$appCount = mysqli_fetch_assoc($appCountRes)['cnt'] ?? 0;

if ($appCount == 0) {
    $existingCandidates = mysqli_query($con, "SELECT * FROM `jobregistration` ORDER BY id ASC");
    if ($existingCandidates && mysqli_num_rows($existingCandidates) > 0) {
        $stmtApp = mysqli_prepare($con, "INSERT INTO `job_applications` 
            (`job_id`, `user_id`, `name`, `email`, `mobile`, `degree`, `refer`, `jobpost`, `status`, `skills`, `experience`, `applied_at`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $statuses = ['Applied', 'Under Review', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Applied'];
        $sIdx = 0;

        while ($c = mysqli_fetch_assoc($existingCandidates)) {
            $currStatus = $statuses[$sIdx % count($statuses)];
            $sIdx++;

            // Match user ID by email if exists
            $uRes = mysqli_query($con, "SELECT id FROM `users` WHERE `email` = '" . mysqli_real_escape_string($con, $c['email']) . "'");
            $uRow = mysqli_fetch_assoc($uRes);
            $uId = $uRow ? (int)$uRow['id'] : null;

            // Match job ID by title if exists
            $jRes = mysqli_query($con, "SELECT id FROM `jobs` WHERE `title` LIKE '%" . mysqli_real_escape_string($con, $c['jobpost']) . "%' LIMIT 1");
            $jRow = mysqli_fetch_assoc($jRes);
            $jId = $jRow ? (int)$jRow['id'] : 1;

            $skills = 'HTML, CSS, JavaScript, PHP, MySQL';
            $exp = 'Fresher / 1 Year';

            mysqli_stmt_bind_param($stmtApp, "iisssssssss", 
                $jId, $uId, $c['name'], $c['email'], $c['mobile'], $c['degree'], $c['refer'], $c['jobpost'], $currStatus, $skills, $exp
            );
            mysqli_stmt_execute($stmtApp);
        }
        mysqli_stmt_close($stmtApp);
        echo "✓ Synced " . mysqli_num_rows($existingCandidates) . " existing candidates into 'job_applications'.\n";
    }
}

// 11. Ensure sample candidate profile exists for candidate user
$demoCand = mysqli_query($con, "SELECT id FROM `users` WHERE `email` = 'candidate@example.com'");
if ($demoCandRow = mysqli_fetch_assoc($demoCand)) {
    $demoUid = (int)$demoCandRow['id'];
    $profCheck = mysqli_query($con, "SELECT id FROM `candidate_profiles` WHERE `user_id` = $demoUid");
    if (mysqli_num_rows($profCheck) === 0) {
        mysqli_query($con, "INSERT INTO `candidate_profiles` 
            (`user_id`, `headline`, `phone`, `degree`, `institution`, `graduation_year`, `experience_level`, `skills`, `bio`, `city`, `state`) 
            VALUES ($demoUid, 'Frontend Engineer & UI Specialist', '9876543210', 'B.Tech Computer Science', 'Gujarat Technological University', '2025', 'Fresher', 'React, JavaScript, CSS3, HTML5, Bootstrap, Git, Figma', 'Enthusiastic developer dedicated to building responsive, accessible web applications.', 'Ahmedabad', 'Gujarat')");
        echo "✓ Initialized candidate profile for demo user.\n";
    }
}

echo "\n=== Database Migration Completed Successfully! ===\n";
