<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$userId = getUserId();
$username = getUsername();

// Fetch all data
$stmt = $pdo->prepare("SELECT * FROM hero_section WHERE user_id = ?");
$stmt->execute([$userId]);
$hero = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM about_items WHERE user_id = ? ORDER BY display_order");
$stmt->execute([$userId]);
$aboutItems = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM skills WHERE user_id = ? ORDER BY category, skill_name");
$stmt->execute([$userId]);
$allSkills = $stmt->fetchAll();

// Group skills by category
$skills = [];
foreach ($allSkills as $skill) {
    $skills[$skill['category']][] = $skill['skill_name'];
}

$stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$education = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM contact_info WHERE user_id = ?");
$stmt->execute([$userId]);
$contact = $stmt->fetch();

if (!$hero) {
    die("Please fill out the form first before generating your page.");
}

// Generate HTML
$profileImageUrl = !empty($hero['profile_image']) ? BASE_URL . 'uploads/' . $hero['profile_image'] : '';

$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$hero['full_name']} - Portfolio</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        :root {
            --primary-blue: #2563eb;
            --secondary-blue: #3b82f6;
            --light-blue: #dbeafe;
            --dark-blue: #1e40af;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .navbar {
            background-color: var(--primary-blue) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .navbar-brand, .nav-link {
            color: white !important;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: var(--light-blue) !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
            color: white;
            padding: 80px 0;
            min-height: 500px;
        }
        
        .profile-image {
            width: 100%;
            max-width: 400px;
            height: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .section-title {
            color: var(--primary-blue);
            font-weight: 700;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--secondary-blue);
        }
        
        .skill-badge {
            background-color: var(--light-blue);
            color: var(--dark-blue);
            padding: 8px 16px;
            border-radius: 20px;
            display: inline-block;
            margin: 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .skill-badge:hover {
            background-color: var(--primary-blue);
            color: white;
            transform: translateY(-2px);
        }
        
        .education-card {
            border-left: 4px solid var(--primary-blue);
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .about-item {
            padding: 15px;
            margin-bottom: 15px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .about-item i {
            color: var(--primary-blue);
            font-size: 24px;
            margin-right: 15px;
        }
        
        footer {
            background-color: var(--dark-blue);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="bi bi-code-square"></i> Portfolio
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#skills">Skills</a></li>
                    <li class="nav-item"><a class="nav-link" href="#education">Education</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">{$hero['full_name']}</h1>
                    <h3 class="mb-4">{$hero['title']}</h3>
                    <p class="lead mb-4">{$hero['description']}</p>
                    <div class="d-flex gap-3">
                        <a href="#contact" class="btn btn-light btn-lg">Get In Touch</a>
                        <a href="#skills" class="btn btn-outline-light btn-lg">View Skills</a>
                    </div>
                </div>
                <div class="col-lg-6 text-center mt-5 mt-lg-0">
                    <div class="profile-image mx-auto">
HTML;

if ($profileImageUrl) {
    $html .= "<img src=\"$profileImageUrl\" alt=\"{$hero['full_name']}\" />";
} else {
    $html .= "<div class=\"d-flex align-items-center justify-content-center h-100 text-muted\">[Profile Image]</div>";
}

$html .= <<<HTML
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <h2 class="section-title">About Me</h2>
            <div class="row" x-data="{ showMore: false }">
                <div class="col-lg-12">
                    <div class="row">
HTML;

// Display first 4 about items
for ($i = 0; $i < min(4, count($aboutItems)); $i++) {
    $item = $aboutItems[$i];
    $html .= <<<HTML
                        <div class="col-md-6 mb-3">
                            <div class="about-item d-flex align-items-start">
                                <i class="bi {$item['icon']}"></i>
                                <div>
                                    <h5 class="mb-2">{$item['title']}</h5>
                                    <p class="mb-0">{$item['description']}</p>
                                </div>
                            </div>
                        </div>
HTML;
}

// Display remaining items with x-show
for ($i = 4; $i < count($aboutItems); $i++) {
    $item = $aboutItems[$i];
    $html .= <<<HTML
                        <div class="col-md-6 mb-3" x-show="showMore" x-transition>
                            <div class="about-item d-flex align-items-start">
                                <i class="bi {$item['icon']}"></i>
                                <div>
                                    <h5 class="mb-2">{$item['title']}</h5>
                                    <p class="mb-0">{$item['description']}</p>
                                </div>
                            </div>
                        </div>
HTML;
}

$html .= <<<HTML
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" @click="showMore = !showMore" x-text="showMore ? 'Show Less' : 'Show More'"></button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Technical Skills Section -->
    <section id="skills" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Technical Skills</h2>
            <div class="row">
HTML;

$skillIcons = [
    'Programming Languages' => 'bi-code-slash',
    'Frameworks' => 'bi-layers',
    'Tools' => 'bi-tools',
    'Databases' => 'bi-database'
];

foreach ($skills as $category => $skillList) {
    $icon = $skillIcons[$category] ?? 'bi-star';
    $html .= <<<HTML
                <div class="col-lg-6 mb-4">
                    <h4 class="mb-3"><i class="bi $icon text-primary"></i> $category</h4>
                    <div>
HTML;
    foreach ($skillList as $skill) {
        $html .= "<span class=\"skill-badge\">$skill</span>\n";
    }
    $html .= <<<HTML
                    </div>
                </div>
HTML;
}

$html .= <<<HTML
            </div>
        </div>
    </section>

    <!-- Education Section -->
    <section id="education" class="py-5">
        <div class="container">
            <h2 class="section-title">Education</h2>
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <h4 class="mb-4"><i class="bi bi-mortarboard-fill text-primary"></i> Education</h4>
                    <div class="education-card">
                        <h5>{$education['degree']}</h5>
                        <p class="text-muted mb-2"><i class="bi bi-building"></i> {$education['institution']}</p>
                        <p class="text-muted mb-2"><i class="bi bi-calendar"></i> {$education['start_year']} - {$education['end_year']}</p>
HTML;

if (!empty($education['description'])) {
    $html .= "<p class=\"mb-0\">{$education['description']}</p>\n";
}
if (!empty($education['address'])) {
    $html .= "<p class=\"mb-0\">{$education['address']}</p>\n";
}

$html .= <<<HTML
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <h4 class="mb-4">Contact Information</h4>
                    <div class="mb-3">
                        <i class="bi bi-envelope-fill text-primary me-2"></i>
                        <strong>Email:</strong> {$contact['email']}
                    </div>
HTML;

if (!empty($contact['phone'])) {
    $html .= <<<HTML
                    <div class="mb-3">
                        <i class="bi bi-phone-fill text-primary me-2"></i>
                        <strong>Phone:</strong> {$contact['phone']}
                    </div>
HTML;
}

$html .= <<<HTML
                    <div class="mb-3">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                        <strong>Location:</strong> {$contact['location']}
                    </div>
                    <div class="mt-4">
                        <h5>Connect With Me</h5>
HTML;

if (!empty($contact['linkedin_url'])) {
    $html .= "<a href=\"{$contact['linkedin_url']}\" class=\"btn btn-primary me-2 mb-2\" target=\"_blank\"><i class=\"bi bi-linkedin\"></i> LinkedIn</a>\n";
}
if (!empty($contact['github_url'])) {
    $html .= "<a href=\"{$contact['github_url']}\" class=\"btn btn-outline-primary me-2 mb-2\" target=\"_blank\"><i class=\"bi bi-github\"></i> GitHub</a>\n";
}
if (!empty($contact['twitter_url'])) {
    $html .= "<a href=\"{$contact['twitter_url']}\" class=\"btn btn-outline-primary mb-2\" target=\"_blank\"><i class=\"bi bi-twitter\"></i> Twitter</a>\n";
}

$html .= <<<HTML
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-2">&copy; 2025 {$hero['full_name']}. All rights reserved.</p>
            <p class="mb-0">Built with Bootstrap, Alpine.js, and PHP</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
HTML;

// Save to file
$generatedDir = __DIR__ . '/../../generated/';
if (!file_exists($generatedDir)) {
    mkdir($generatedDir, 0777, true);
}

$filename = $username . '_portfolio.html';
$filepath = $generatedDir . $filename;

file_put_contents($filepath, $html);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Generated!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
        }
        .success-icon {
            font-size: 80px;
            color: #10b981;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card mx-auto">
            <i class="bi bi-check-circle-fill success-icon"></i>
            <h1 class="mb-4">Portfolio Generated Successfully!</h1>
            <p class="lead mb-4">Your static portfolio page has been created.</p>
            
            <div class="d-grid gap-3">
                <a href="../../generated/<?php echo $filename; ?>" class="btn btn-success btn-lg" target="_blank">
                    <i class="bi bi-eye me-2"></i>View Your Portfolio
                </a>
                <a href="dashboard.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-pencil me-2"></i>Edit Portfolio
                </a>
                <a href="../logout.php" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
            
            <div class="alert alert-info mt-4">
                <small><strong>File Location:</strong> generated/<?php echo $filename; ?></small>
            </div>
        </div>
    </div>
</body>
</html>