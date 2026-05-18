<?php
session_start();
include 'db.php';

$cats = [];
$res_c = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($res_c) {
    while($row = $res_c->fetch_assoc()) {
        $cats[] = $row;
    }
}

$images = [];
$query = "SELECT i.*, c.name as category_name FROM images i LEFT JOIN categories c ON i.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $query .= " AND (i.title LIKE ? OR i.tags LIKE ? OR i.description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $types .= "sss";
}

if (!empty($_GET['category'])) {
    $category_id = $_GET['category'];
    $query .= " AND i.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$query .= " ORDER BY i.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ImageShare - Professional Image Sharing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="homePage.css" />
    <script src="homePage.js" defer></script>
</head>
<body>
    <header>
        <div class="navbar">
            <div class="logo"><a href="index.php">ImageShare</a></div>
            <ul class="nav_links">
                <li><a href="index.php">Explore</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="profile.php">My Profile</a></li>
                <?php else: ?>
                    <li><a href="about/about.html">About Us</a></li>
                <?php endif; ?>
            </ul>
            <div style="display: flex; gap: 10px; align-items: center;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span style="color: #fff; font-weight: 600; margin-right: 15px;">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="upload.php" class="action_btn"><i class="fa-solid fa-plus"></i> Upload</a>
                    <a href="auth/logout.php" class="action_btn logout_btn">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php" class="nav_links" style="color: #fff; font-weight: 600; margin-right: 15px;">Log in</a>
                    <a href="auth/signup.php" class="action_btn">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="toggle_btn" style="display:none;">
                <i class="fa-solid fa-bars"></i>
            </div>
        </div>
    </header>

    <main>
        <section id="hero">
            <h1>Discover <br><span>Extraordinary</span> Images</h1>
            
            <div class="search-container">
                <form action="index.php" method="GET" class="search-form">
                    <select name="category" class="search-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach($cats as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php if(isset($_GET['category']) && $_GET['category'] == $c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="search" class="search-input" placeholder="Search by title, tags, or description..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="search-btn"><i class="fa-solid fa-search"></i></button>
                </form>
            </div>
        </section>

        <section class="container">
            <?php if(count($images) > 0): ?>
            <div class="gallery">
                <?php foreach ($images as $img): ?>
                    <a href="view.php?id=<?php echo $img['id']; ?>" class="image" style="display:block;">
                        <img src="image.php?id=<?php echo $img['id']; ?>" alt="<?php echo htmlspecialchars($img['title']); ?>" />
                        <?php if(!empty($img['category_name'])): ?>
                            <div class="tag-badge"><?php echo htmlspecialchars($img['category_name']); ?></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div style="text-align: center; width: 100%; margin-top: 50px; color: #888;">
                    <i class="fa-solid fa-image" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h3>No images found.</h3>
                    <p>Try adjusting your search or be the first to upload!</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>
