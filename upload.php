<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = (!empty($_POST['category_id'])) ? $_POST['category_id'] : null;
    $tags = $_POST['tags'];
    
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
    
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists(strtolower($ext), $allowed)) {
            $error = "Error: Please select a valid file format.";
        }
    
        $maxsize = 30 * 1024 * 1024;
        if ($filesize > $maxsize) {
            $error = "Error: File size is larger than the allowed 30MB limit.";
        }
    
        if (empty($error) && in_array($filetype, $allowed)) {
            $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
            
            $stmt = $conn->prepare("INSERT INTO images (user_id, category_id, title, description, tags, image_data, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $_SESSION['user_id'], $category_id, $title, $description, $tags, $image_data, $filetype);
            
            try {
                if ($stmt->execute()) {
                    $success = "Your file was uploaded securely to the database.";
                } else {
                    $error = "Database Error: " . $stmt->error;
                }
            } catch (mysqli_sql_exception $e) {
                if (strpos($e->getMessage(), 'max_allowed_packet') !== false) {
                    $error = "Error: This image is too large for your database's default configuration! Please try a smaller image, or increase 'max_allowed_packet' in your XAMPP MySQL my.ini file.";
                } else {
                    $error = "Database Exception: " . $e->getMessage();
                }
            }
        } else if(empty($error)) {
            $error = "Error: There was a problem uploading your file. Please try again."; 
        }
    } else {
        $error = "Error: " . $_FILES["image"]["error"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Image | ImageShare</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="homePage.css" />
<style>
    body { background-color: #1a1a1a; color: white; }
    .upload-card { max-width: 500px; margin: 50px auto; background: #222; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.5); }
    .form-control, .form-select { background: #333; color: white; border: 1px solid #444; }
    .form-control:focus, .form-select:focus { background: #444; color: white; border-color: #555; }
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

<div class="container">
    <div class="upload-card">
        <h3 class="mb-4 text-center">Upload New Image</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Image Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">No Category</option>
                    <?php foreach($cats as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tags" class="form-label">Tags (comma separated)</label>
                <input type="text" class="form-control" id="tags" name="tags" placeholder="e.g. nature, forest, green">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Select Image</label>
                <input class="form-control" type="file" id="image" name="image" required accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary w-100">Upload</button>
        </form>
    </div>
</div>

</body>
</html>
