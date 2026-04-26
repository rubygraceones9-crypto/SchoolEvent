<?php
require_once 'includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if(isset($_POST['upload'])) {
    $event_id = $_POST['event_id'];
    $target_dir = "uploads/";
    
    // Ensure directory exists
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["file"]["name"]);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Broad list of allowed extensions for photos, docs, pdfs, and videos
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'txt', 'mp4', 'mov', 'webm', 'ogg'];
    
    if (!in_array($file_ext, $allowed)) {
        die("Error: File type not supported. Please upload Photos, PDFs, Docs, or Videos.");
    }

    $clean_name = preg_replace("/[^a-zA-Z0-9]/", "_", pathinfo($file_name, PATHINFO_FILENAME));
    $target_file = $target_dir . time() . "_" . $clean_name . "." . $file_ext;
    
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO media_assets (event_id, file_name, file_path, file_type, title) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $event_id, $file_name, $target_file, $file_ext, $title);
        $stmt->execute();
        
        header("Location: index.php?success=1");
    } else {
        echo "Error: Upload failed. Please ensure the file is not too large and the uploads folder is writable.";
    }
}
?>
