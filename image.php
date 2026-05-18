<?php
include 'db.php';

if (!isset($_GET['id'])) {
    exit();
}

$id = intval($_GET['id']);
$stmt = $conn->prepare("SELECT image_data, mime_type FROM images WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($image_data, $mime_type);
    $stmt->fetch();
    
    header("Content-Type: " . $mime_type);
    
    if (isset($_GET['download']) && $_GET['download'] == '1') {
        $ext = str_replace('image/', '', $mime_type);
        if ($ext == 'jpeg') $ext = 'jpg';
        $filename = "ImageShare_" . $id . "." . $ext;
        header('Content-Disposition: attachment; filename="'.$filename.'"');
    }
    
    echo $image_data;
} else {
    header("HTTP/1.0 404 Not Found");
}
?>
