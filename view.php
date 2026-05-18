<?php
session_start();
include 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$image_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'] ?? 0;

$return_to = 'index.php';
if (!empty($_GET['return']) && $_GET['return'] === 'profile') {
    $return_to = 'profile.php';
} elseif (!empty($_GET['return']) && $_GET['return'] === 'saved') {
    $return_to = 'profile.php?tab=saved';
}

$stmt = $conn->prepare("
    SELECT i.*, u.username, c.name as category_name 
    FROM images i 
    JOIN users u ON i.user_id = u.id 
    LEFT JOIN categories c ON i.category_id = c.id 
    WHERE i.id = ?
");
$stmt->bind_param("i", $image_id);
$stmt->execute();
$img = $stmt->get_result()->fetch_assoc();

if (!$img) {
    echo "Image not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($img['title']); ?> | ImageShare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="homePage.css" />
    <style>
        body { background-color: #000; background-image: url(./assets/background.jpg); background-size: cover; background-attachment: fixed; color: #fff; margin: 0; padding: 0;}
        .view-container { max-width: 1200px; margin: 10px auto; display: flex; background: #fff; color: #000; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); overflow: hidden; height: 75vh; min-height: 500px; }
        .view-image { flex: 1.5; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .view-image img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .view-sidebar { flex: 1; padding: 25px; display: flex; flex-direction: column; overflow-y: auto; background: #fff;}
        .uploader { font-weight: bold; font-size: 1rem; color: #333; margin-bottom: 10px; }
        .title { font-size: 1.6rem; font-weight: 700; margin-bottom: 8px; line-height: 1.2;}
        .description { color: #555; margin-bottom: 15px; line-height: 1.5; font-size: 0.95rem; }
        .badge { background: #eee; color: #333; font-weight: 600; padding: 4px 10px; border-radius: 12px; margin-right: 5px; font-size: 0.85rem;}
        
        .interaction-btns { display: flex; gap: 10px; margin-top: 15px; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 15px; }
        .interaction-btn { background: #f1f1f1; border: none; padding: 8px 16px; border-radius: 20px; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px; font-size: 0.9rem;}
        .interaction-btn:hover { background: #e0e0e0; }
        .interaction-btn.active { background: #fffaf0; color: orange; border: 1px solid orange; }
        
        #commentsList { flex: 1; margin-bottom: 15px; min-height: 180px; padding-right: 5px;}
        .comment-item { padding: 8px 0; border-bottom: 1px solid #eee; }
        .comment-user { font-weight: bold; color: orange; display: block; margin-bottom: 2px; font-size: 0.85rem;}
        .comment-text { color: #333; font-size: 0.9rem; }
        
        .comment-input-area { display: flex; flex-direction: column; gap: 8px; margin-top: auto;}
        #commentText { border-radius: 8px; border: 1px solid #ccc; padding: 10px; outline: none; resize: none; width: 100%; font-size: 0.9rem;}
        #submitComment { background: orange; border: none; color: white; font-weight: bold; padding: 10px; border-radius: 8px; cursor: pointer; transition: 0.3s; font-size: 0.95rem;}
        #submitComment:hover { background: darkorange; }
        
        @media (max-width: 900px) {
            .view-container { flex-direction: column; margin: 20px; }
            .view-image img { max-height: 60vh; }
            .view-sidebar { max-height: none; }
        }
    </style>
</head>
<body>

<div class="container-fluid d-flex flex-column align-items-center justify-content-center" style="min-height: 100vh; padding: 20px;">
    <div class="w-100 mb-3" style="max-width: 1300px;">
        <a href="<?php echo htmlspecialchars($return_to); ?>" class="btn" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(10px); color: #fff; border: 1px solid rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 30px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;" onmouseover="this.style.background='rgba(0,0,0,0.8)'" onmouseout="this.style.background='rgba(0,0,0,0.6)'"><i class="fas fa-arrow-left"></i> <?php 
            if (!empty($_GET['return']) && $_GET['return'] === 'saved') {
                echo 'Back to Saved Images';
            } elseif (!empty($_GET['return']) && $_GET['return'] === 'profile') {
                echo 'Back to My Uploads';
            } else {
                echo 'Back to Gallery';
            }
        ?></a>
    </div>
    <div class="view-container" style="width: 100%; max-width: 1300px; margin: 0; height: 85vh; border-radius: 24px; box-shadow: 0 25px 50px rgba(0,0,0,0.8);">
        <div class="view-image">
            <img src="image.php?id=<?php echo $img['id']; ?>" alt="<?php echo htmlspecialchars($img['title']); ?>">
        </div>
        <div class="view-sidebar">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="uploader"><i class="fas fa-user-circle fs-4 text-secondary me-2"></i> <?php echo htmlspecialchars($img['username']); ?></div>
            </div>
            
            <h1 class="title"><?php echo htmlspecialchars($img['title']); ?></h1>
            <p class="description"><?php echo nl2br(htmlspecialchars($img['description'])); ?></p>
            
            <div class="tags-area mb-3">
                <?php if($img['category_name']): ?>
                    <span class="badge" style="background: orange; color: white;"><?php echo htmlspecialchars($img['category_name']); ?></span>
                <?php endif; ?>
                <?php if(!empty($img['tags'])): ?>
                    <?php foreach(explode(',', $img['tags']) as $tag): ?>
                        <span class="badge">#<?php echo htmlspecialchars(trim($tag)); ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($user_id && $user_id == $img['user_id']): ?>
                <div class="mb-3" style="display:flex; gap:10px;">
                    <a href="edit_image.php?id=<?php echo $img['id']; ?>" class="btn btn-warning">Edit Details</a>
                    <button id="deleteBtn" class="btn btn-danger">Delete Image</button>
                </div>
            <?php endif; ?>

            <div class="interaction-btns">
                <button id="likeBtn" class="interaction-btn"><i class="fa-solid fa-heart"></i> <span id="likeCount">0</span></button>
                <button id="saveBtn" class="interaction-btn"><i class="fa-solid fa-bookmark"></i> Save</button>
                <a href="image.php?id=<?php echo $img['id']; ?>&download=1" class="interaction-btn" style="text-decoration: none; color: inherit; margin-left: auto;">
                    <i class="fa-solid fa-download"></i> Download
                </a>
            </div>

            <h4 class="mb-3" style="font-weight:700;">Comments</h4>
            <div id="commentsList"></div>
            
            <div class="comment-input-area mt-auto">
                <?php if ($user_id): ?>
                    <textarea id="commentText" rows="2" placeholder="Add a comment..."></textarea>
                    <button id="submitComment">Post Comment</button>
                <?php else: ?>
                    <div class="alert text-center" style="background: #f8f9fa; border:1px solid #ddd;">
                        <a href="auth/login.php" class="text-decoration-none fw-bold" style="color:orange;">Log in</a> to interact and leave a comment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
const currentImageId = <?php echo $image_id; ?>;
const isLoggedIn = <?php echo $user_id ? 'true' : 'false'; ?>;

function fetchImageInfo() {
    $.post("api.php", { action: 'get_info', image_id: currentImageId }, function(data) {
        if(data.status === 'success') {
            $('#likeCount').text(data.likes);
            if(data.is_liked) {
                $('#likeBtn').addClass('active');
            } else {
                $('#likeBtn').removeClass('active');
            }
            if(data.is_saved) {
                $('#saveBtn').addClass('active').html('<i class="fa-solid fa-bookmark"></i> Saved');
            } else {
                $('#saveBtn').removeClass('active').html('<i class="fa-solid fa-bookmark"></i> Save');
            }
            
            let commentsHtml = '';
            if(data.comments.length === 0) {
                commentsHtml = '<p class="text-muted" style="text-align:center; margin-top:20px;">No comments yet. Be the first!</p>';
            } else {
                data.comments.forEach(c => {
                    commentsHtml += `<div class="comment-item"><span class="comment-user">${c.username}</span><div class="comment-text">${c.comment_text}</div></div>`;
                });
            }
            $('#commentsList').html(commentsHtml);
        }
    });
}

$(document).ready(function() {
    fetchImageInfo();

    $('#likeBtn').click(function() {
        if (!isLoggedIn) { window.location.href = 'auth/login.php'; return; }
        $.post("api.php", { action: 'like', image_id: currentImageId }, function(data) {
            if(data.status === 'success') {
                $('#likeCount').text(data.likes);
                if(data.is_liked) {
                    $('#likeBtn').addClass('active');
                } else {
                    $('#likeBtn').removeClass('active');
                }
            }
        });
    });

    $('#saveBtn').click(function() {
        if (!isLoggedIn) { window.location.href = 'auth/login.php'; return; }
        $.post("api.php", { action: 'save', image_id: currentImageId }, function(data) {
            if(data.status === 'success') {
                if(data.is_saved) {
                    $('#saveBtn').addClass('active').html('<i class="fa-solid fa-bookmark"></i> Saved');
                } else {
                    $('#saveBtn').removeClass('active').html('<i class="fa-solid fa-bookmark"></i> Save');
                }
            }
        });
    });

    $('#deleteBtn').click(function() {
        if (!confirm('Are you sure you want to delete this image? This cannot be undone.')) {
            return;
        }
        $.post("api.php", { action: 'delete', image_id: currentImageId }, function(data) {
            if (data.status === 'success') {
                window.location.href = 'profile.php';
            } else {
                alert('Delete failed: ' + (data.message || 'Unknown error'));
            }
        });
    });

    $('#submitComment').click(function() {
        if (!isLoggedIn) return;
        let text = $('#commentText').val();
        if(!text.trim()) return;
        
        let initialBtnText = $(this).text();
        $(this).text('Posting...');
        
        $.post("api.php", { action: 'comment', image_id: currentImageId, text: text }, function(data) {
            if(data.status === 'success') {
                $('#commentText').val('');
                fetchImageInfo();
            } else {
                alert("Error: " + data.message);
            }
            $('#submitComment').text(initialBtnText);
        });
    });
});
</script>
</body>
</html>
