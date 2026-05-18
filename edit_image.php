<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: profile.php");
    exit();
}

$image_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM images WHERE id = ?");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$img = $stmt->get_result()->fetch_assoc();

if (!$img) {
    echo "Image not found.";
    exit();
}

if ($img['user_id'] != $user_id) {
    echo "Unauthorized access.";
    exit();
}

$cats = [];
$res_c = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($res_c) {
    while($row = $res_c->fetch_assoc()) {
        $cats[] = $row;
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (!empty($_POST['category_id'])) ? $_POST['category_id'] : null;
    $tags = trim($_POST['tags'] ?? '');

    if ($title === '') {
        $error = 'Title is required.';
    } else {
        $update = $conn->prepare("UPDATE images SET title = ?, description = ?, category_id = ?, tags = ? WHERE id = ? AND user_id = ?");
        $update->bind_param('ssisii', $title, $description, $category_id, $tags, $image_id, $user_id);
        if ($update->execute()) {
            $success = 'Image details updated successfully.';
            // Refresh image data
            $stmt = $conn->prepare("SELECT * FROM images WHERE id = ?");
            $stmt->bind_param("i", $image_id);
            $stmt->execute();
            $img = $stmt->get_result()->fetch_assoc();
        } else {
            $error = 'Update failed: ' . $update->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Image | ImageShare</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="homePage.css" />
<style>
    body { background-color: #1a1a1a; color: white; }
    .card { background: #222; border: none; }
    .form-control, .form-select { background: #333; color: #fff; border: 1px solid #444; }
    .form-control:focus, .form-select:focus { background: #444; color: #fff; border-color: #555; }
</style>
</head>
<body>
<header>
    <div class="navbar">
        <div class="logo"><a href="index.php">ImageShare</a></div>
        <ul class="nav_links">
            <li><a href="index.php">Home</a></li>
            <li><a href="profile.php">Profile</a></li>
        </ul>
        <div>
            <a href="auth/logout.php" class="action_btn">Logout</a>
        </div>
    </div>
</header>
<div class="container" style="max-width: 650px; margin-top: 50px;">
    <div class="card p-4">
        <div class="mb-4">
            <h2 class="mb-2" style="color: #ffa500;">Edit Your Image Details</h2>
            <p style="color: #ccc;">Update title, description, category, or tags for your upload.</p>
        </div>
        <div class="mb-4" style="text-align:center;">
            <img src="image.php?id=<?php echo $image_id; ?>" alt="Image preview" style="max-width:100%; max-height:250px; border-radius: 8px; border: 1px solid #444;" />
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="edit_image.php?id=<?php echo $image_id; ?>">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" id="title" name="title" class="form-control" required value="<?php echo htmlspecialchars($img['title']); ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($img['description']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">No Category</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?php echo $c['id']; ?>" <?php echo ($img['category_id'] == $c['id'] ? 'selected' : ''); ?>><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma separated)</label>
                <input type="text" id="tags" name="tags" class="form-control" value="<?php echo htmlspecialchars($img['tags']); ?>">
            </div>
            <div class="d-flex justify-content-between">
                <a href="view.php?id=<?php echo $image_id; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>