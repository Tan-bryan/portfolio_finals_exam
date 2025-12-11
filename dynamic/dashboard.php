<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$userId = getUserId();
$username = getUsername();
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hero Section
    $fullName = sanitize($_POST['full_name']);
    $title = sanitize($_POST['title']);
    $heroDescription = sanitize($_POST['hero_description']);
    
    // Handle profile image upload
    $profileImage = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload = uploadImage($_FILES['profile_image'], 'profiles');
        if ($upload['success']) {
            $profileImage = $upload['filename'];
        }
    } else {
        // Keep existing image if available
        $stmt = $pdo->prepare("SELECT profile_image FROM hero_section WHERE user_id = ?");
        $stmt->execute([$userId]);
        $existing = $stmt->fetch();
        $profileImage = $existing['profile_image'] ?? '';
    }
    
    // Save/Update Hero Section
    $stmt = $pdo->prepare("INSERT INTO hero_section (user_id, full_name, title, description, profile_image) 
                           VALUES (?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE full_name=?, title=?, description=?, profile_image=?");
    $stmt->execute([$userId, $fullName, $title, $heroDescription, $profileImage, $fullName, $title, $heroDescription, $profileImage]);
    
    // About Section (6 items)
    $aboutItems = [
        ['icon' => 'bi-heart-fill', 'title' => 'Interests', 'description' => sanitize($_POST['interests'])],
        ['icon' => 'bi-lightbulb-fill', 'title' => 'Greatest Inspiration', 'description' => sanitize($_POST['inspiration'])],
        ['icon' => 'bi-quote', 'title' => 'Life Motto', 'description' => sanitize($_POST['motto'])],
        ['icon' => 'bi-star-fill', 'title' => 'Strengths', 'description' => sanitize($_POST['strengths'])],
        ['icon' => 'bi-list-check', 'title' => 'Bucket List', 'description' => sanitize($_POST['bucket_list'])],
        ['icon' => 'bi-trophy-fill', 'title' => 'Talents', 'description' => sanitize($_POST['talents'])]
    ];
    
    // Delete old about items and insert new ones
    $stmt = $pdo->prepare("DELETE FROM about_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $stmt = $pdo->prepare("INSERT INTO about_items (user_id, icon, title, description, display_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($aboutItems as $index => $item) {
        $stmt->execute([$userId, $item['icon'], $item['title'], $item['description'], $index]);
    }
    
    // Skills - Parse comma-separated lists
    $skillCategories = [
        'Programming Languages' => sanitize($_POST['prog_languages']),
        'Frameworks' => sanitize($_POST['frameworks']),
        'Tools' => sanitize($_POST['tools']),
        'Databases' => sanitize($_POST['databases'])
    ];
    
    // Delete old skills and insert new ones
    $stmt = $pdo->prepare("DELETE FROM skills WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $stmt = $pdo->prepare("INSERT INTO skills (user_id, category, skill_name) VALUES (?, ?, ?)");
    foreach ($skillCategories as $category => $skillsString) {
        $skills = array_map('trim', explode(',', $skillsString));
        foreach ($skills as $skill) {
            if (!empty($skill)) {
                $stmt->execute([$userId, $category, $skill]);
            }
        }
    }
    
    // Education
    $education = [
        'degree' => sanitize($_POST['degree']),
        'institution' => sanitize($_POST['institution']),
        'start_year' => (int)$_POST['start_year'],
        'end_year' => (int)$_POST['end_year'],
        'description' => sanitize($_POST['edu_description']),
        'address' => sanitize($_POST['edu_address'])
    ];
    
    $stmt = $pdo->prepare("DELETE FROM education WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    $stmt = $pdo->prepare("INSERT INTO education (user_id, degree, institution, start_year, end_year, description, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $education['degree'], $education['institution'], $education['start_year'], $education['end_year'], $education['description'], $education['address']]);
    
    // Contact Info
    $contact = [
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'location' => sanitize($_POST['location']),
        'linkedin_url' => sanitize($_POST['linkedin_url']),
        'github_url' => sanitize($_POST['github_url']),
        'twitter_url' => sanitize($_POST['twitter_url'])
    ];
    
    $stmt = $pdo->prepare("INSERT INTO contact_info (user_id, email, phone, location, linkedin_url, github_url, twitter_url) 
                           VALUES (?, ?, ?, ?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE email=?, phone=?, location=?, linkedin_url=?, github_url=?, twitter_url=?");
    $stmt->execute([$userId, $contact['email'], $contact['phone'], $contact['location'], $contact['linkedin_url'], $contact['github_url'], $contact['twitter_url'],
                    $contact['email'], $contact['phone'], $contact['location'], $contact['linkedin_url'], $contact['github_url'], $contact['twitter_url']]);
    
    $message = '<div class="alert alert-success">Portfolio saved successfully! <a href="generate.php" class="alert-link">Click here to generate your static page</a></div>';
}

// Load existing data
$hero = null;
$stmt = $pdo->prepare("SELECT * FROM hero_section WHERE user_id = ?");
$stmt->execute([$userId]);
$hero = $stmt->fetch();

$aboutItems = [];
$stmt = $pdo->prepare("SELECT * FROM about_items WHERE user_id = ? ORDER BY display_order");
$stmt->execute([$userId]);
$aboutItems = $stmt->fetchAll();

$skills = [];
$stmt = $pdo->prepare("SELECT category, GROUP_CONCAT(skill_name) as skills FROM skills WHERE user_id = ? GROUP BY category");
$stmt->execute([$userId]);
while ($row = $stmt->fetch()) {
    $skills[$row['category']] = $row['skills'];
}

$education = null;
$stmt = $pdo->prepare("SELECT * FROM education WHERE user_id = ? LIMIT 1");
$stmt->execute([$userId]);
$education = $stmt->fetch();

$contact = null;
$stmt = $pdo->prepare("SELECT * FROM contact_info WHERE user_id = ?");
$stmt->execute([$userId]);
$contact = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Your Portfolio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background: #f8f9fa; }
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #2563eb;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <span class="navbar-brand"><i class="bi bi-person-circle me-2"></i><?php echo escape($username); ?>'s Portfolio</span>
            <div>
                <a href="generate.php" class="btn btn-light me-2"><i class="bi bi-file-earmark-code"></i> Generate Page</a>
                <a href="logout.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <?php echo $message; ?>
        
        <div class="text-center mb-4">
            <h2>Build Your Portfolio</h2>
            <p class="text-muted">Fill out the form below, then generate your static portfolio page</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <!-- Hero Section -->
            <div class="form-section">
                <h3 class="section-title"><i class="bi bi-person-badge me-2"></i>Hero Section</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="full_name" value="<?php echo escape($hero['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Title/Position *</label>
                        <input type="text" class="form-control" name="title" value="<?php echo escape($hero['title'] ?? ''); ?>" placeholder="e.g., Aspiring Web Developer" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Welcome Message *</label>
                        <textarea class="form-control" name="hero_description" rows="2" required><?php echo escape($hero['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Profile Image (JPG, PNG, GIF - Max 5MB)</label>
                        <?php if (!empty($hero['profile_image'])): ?>
                            <div class="mb-2">
                                <img src="<?php echo getImageUrl($hero['profile_image']); ?>" alt="Profile" style="max-width: 150px; border-radius: 10px;">
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" name="profile_image" accept=".jpg,.jpeg,.png,.gif">
                    </div>
                </div>
            </div>

            <!-- About Section -->
            <div class="form-section">
                <h3 class="section-title"><i class="bi bi-info-circle me-2"></i>About Me</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Interests *</label>
                        <textarea class="form-control" name="interests" rows="2" required><?php echo escape($aboutItems[0]['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Greatest Inspiration *</label>
                        <textarea class="form-control" name="inspiration" rows="2" required><?php echo escape($aboutItems[1]['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Life Motto *</label>
                        <textarea class="form-control" name="motto" rows="2" required><?php echo escape($aboutItems[2]['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Strengths *</label>
                        <textarea class="form-control" name="strengths" rows="2" required><?php echo escape($aboutItems[3]['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bucket List *</label>
                        <textarea class="form-control" name="bucket_list" rows="2" required><?php echo escape($aboutItems[4]['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Talents *</label>
                        <textarea class="form-control" name="talents" rows="2" required><?php echo escape($aboutItems[5]['description'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Skills Section -->
            <div class="form-section">
                <h3 class="section-title"><i class="bi bi-code-slash me-2"></i>Technical Skills</h3>
                <p class="text-muted">Enter skills separated by commas (e.g., JavaScript, Python, Java)</p>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Programming Languages *</label>
                        <input type="text" class="form-control" name="prog_languages" value="<?php echo escape($skills['Programming Languages'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Frameworks *</label>
                        <input type="text" class="form-control" name="frameworks" value="<?php echo escape($skills['Frameworks'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tools *</label>
                        <input type="text" class="form-control" name="tools" value="<?php echo escape($skills['Tools'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Databases *</label>
                        <input type="text" class="form-control" name="databases" value="<?php echo escape($skills['Databases'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <!-- Education Section -->
            <div class="form-section">
                <h3 class="section-title"><i class="bi bi-mortarboard me-2"></i>Education</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Degree *</label>
                        <input type="text" class="form-control" name="degree" value="<?php echo escape($education['degree'] ?? ''); ?>" placeholder="e.g., Bachelor of Science in Computer Science" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Institution *</label>
                        <input type="text" class="form-control" name="institution" value="<?php echo escape($education['institution'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Start Year *</label>
                        <input type="number" class="form-control" name="start_year" value="<?php echo escape($education['start_year'] ?? ''); ?>" min="1950" max="2030" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">End Year *</label>
                        <input type="number" class="form-control" name="end_year" value="<?php echo escape($education['end_year'] ?? ''); ?>" min="1950" max="2030" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="edu_description" value="<?php echo escape($education['description'] ?? ''); ?>" placeholder="e.g., Motto or tagline">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" class="form-control" name="edu_address" value="<?php echo escape($education['address'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="form-section">
                <h3 class="section-title"><i class="bi bi-envelope me-2"></i>Contact Information</h3>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo escape($contact['email'] ?? ''); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone" value="<?php echo escape($contact['phone'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Location *</label>
                        <input type="text" class="form-control" name="location" value="<?php echo escape($contact['location'] ?? ''); ?>" placeholder="e.g., Dasmariñas City, Cavite, Philippines" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">LinkedIn URL</label>
                        <input type="url" class="form-control" name="linkedin_url" value="<?php echo escape($contact['linkedin_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">GitHub URL</label>
                        <input type="url" class="form-control" name="github_url" value="<?php echo escape($contact['github_url'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Twitter URL</label>
                        <input type="url" class="form-control" name="twitter_url" value="<?php echo escape($contact['twitter_url'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save me-2"></i>Save Portfolio Data
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>