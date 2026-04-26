<?php
require_once 'includes/db.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    die("Unauthorized.");
}

// Delete Media Asset
if(isset($_GET['delete_media'])) {
    $id = intval($_GET['delete_media']);
    $res = mysqli_query($conn, "SELECT file_path FROM media_assets WHERE id = $id");
    if($row = mysqli_fetch_assoc($res)) {
        if(file_exists($row['file_path'])) unlink($row['file_path']);
        mysqli_query($conn, "DELETE FROM media_assets WHERE id = $id");
    }
    header("Location: index.php?deleted=media");
}

// Delete Event
if(isset($_GET['delete_event'])) {
    $id = intval($_GET['delete_event']);
    
    // First delete all files associated with the event
    $res = mysqli_query($conn, "SELECT file_path FROM media_assets WHERE event_id = $id");
    while($row = mysqli_fetch_assoc($res)) {
        if(file_exists($row['file_path'])) unlink($row['file_path']);
    }
    
    mysqli_query($conn, "DELETE FROM events WHERE id = $id");
    header("Location: index.php?deleted=event");
}
?>
