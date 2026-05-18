<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$uploads = [];
$res_ul = $conn->query("SELECT * FROM images WHERE user_id = $user_id ORDER BY created_at DESC");
if ($res_ul) {
    while ($r = $res_ul->fetch_assoc()) $uploads[] = $r;
}

$saved = [];
$res_sv = $conn->query("SELECT i.* FROM images i JOIN saved s ON i.id = s.image_id WHERE s.user_id = $user_id ORDER BY s.image_id DESC");
if ($res_sv) {
    while ($r = $res_sv->fetch_assoc()) $saved[] = $r;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | ImageShare</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="homePage.css" />
<style>
    body { background-color: #000; background-image: url(./assets/background.jpg); background-size: cover; background-attachment: fixed; color: white; }
    .nav-tabs .nav-link { color: #ccc; }
    .nav-tabs .nav-link.active { background-color: transparent; color: orange; border-bottom: 2px solid orange; border-top:none; border-left:none; border-right:none;}
</style>
<script src="homePage.js" defer></script>
</head>
<body>

<header>
    <div class="navbar">
        <div class="logo"><a href="index.php">ImageShare</a></div>
        <ul class="nav_links">
            <li><a href="index.php">Home</a></li>
            <li><a href="profile.php" style="color:orange;">Profile</a></li>
        </ul>
        <div>
            <a href="upload.php" class="action_btn" style="margin-right: 10px;">Upload</a>
            <a href="auth/logout.php" class="action_btn logout_btn">Logout</a>
        </div>
        <div class="toggle_btn" style="display:none;">
            <i class="fa-solid fa-bars"></i>
        </div>
    </div>
</header>

<div class="container mt-5" style="padding-top: 40px; max-width: 1400px; margin:0 auto;">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    
    <ul class="nav nav-tabs mt-4" id="profileTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="uploads-tab" data-bs-toggle="tab" data-bs-target="#uploads" type="button" role="tab">My Uploads</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="saved-tab" data-bs-toggle="tab" data-bs-target="#saved" type="button" role="tab">Saved Images</button>
        </li>
    </ul>
    
    <div class="tab-content py-4" id="profileTabsContent">
        <div class="tab-pane fade show active" id="uploads" role="tabpanel">
            <?php if(count($uploads) > 0): ?>
            <div class="gallery">
                    <?php foreach ($uploads as $img): ?>
                        <a href="view.php?id=<?php echo $img['id']; ?>&return=profile" class="image" style="display:block;">
                            <img src="image.php?id=<?php echo $img['id']; ?>" alt="" />
                        </a>
                    <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div style="text-align: center; width: 100%; margin-top: 50px; color: #888;">
                    <i class="fa-solid fa-cloud-arrow-up" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h3>No Uploads</h3>
                    <p>You have not uploaded any images yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="tab-pane fade" id="saved" role="tabpanel">
            <?php if(count($saved) > 0): ?>
            <div class="gallery">
                    <?php foreach ($saved as $img): ?>
                        <a href="view.php?id=<?php echo $img['id']; ?>&return=saved" class="image" style="display:block;">
                            <img src="image.php?id=<?php echo $img['id']; ?>" alt="" />
                        </a>
                    <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div style="text-align: center; width: 100%; margin-top: 50px; color: #888;">
                    <i class="fa-solid fa-bookmark" style="font-size: 4rem; margin-bottom: 20px;"></i>
                    <h3>No Saved Images</h3>
                    <p>You haven't saved any images yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab');
    if (tab === 'saved') {
        document.getElementById('saved-tab').click();
    }
</script>
</body>
</html>
