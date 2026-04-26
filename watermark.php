<?php
// watermark.php - Automatically apply a school logo watermark to images

if (!isset($_GET['image'])) {
    die("No image specified.");
}

$imagePath = $_GET['image'];

// Security check: ensure the image path is within the uploads directory
if (strpos(realpath($imagePath), realpath('uploads')) !== 0) {
    die("Invalid image path.");
}

if (!file_exists($imagePath)) {
    die("Image not found.");
}

// Load the source image
$info = getimagesize($imagePath);
$mime = $info['mime'];

switch ($mime) {
    case 'image/jpeg':
        $image = imagecreatefromjpeg($imagePath);
        break;
    case 'image/png':
        $image = imagecreatefrompng($imagePath);
        break;
    default:
        die("Unsupported image type.");
}

// Load the watermark logo
$logoPath = 'logo.png';
if (file_exists($logoPath)) {
    $logo = imagecreatefrompng($logoPath);
    
    // Get dimensions
    $imageW = imagesx($image);
    $imageH = imagesy($image);
    $logoW = imagesx($logo);
    $logoH = imagesy($logo);

    // Set watermark size (e.g., 20% of the image width)
    $targetLogoW = $imageW * 0.25;
    $targetLogoH = $logoH * ($targetLogoW / $logoW);

    // Create a temporary canvas for the resized logo
    $resizedLogo = imagecreatetruecolor($targetLogoW, $targetLogoH);
    imagealphablending($resizedLogo, false);
    imagesavealpha($resizedLogo, true);
    $transparent = imagecolorallocatealpha($resizedLogo, 255, 255, 255, 127);
    imagefill($resizedLogo, 0, 0, $transparent);
    
    imagecopyresampled($resizedLogo, $logo, 0, 0, 0, 0, $targetLogoW, $targetLogoH, $logoW, $logoH);

    // Position: Center with some transparency
    $destX = ($imageW - $targetLogoW) / 2;
    $destY = ($imageH - $targetLogoH) / 2;

    // Apply the watermark with transparency
    // imagecopymerge doesn't support alpha per-pixel well for PNGs
    // So we'll use imagecopy if we want the logo as is, or a manual loop for custom alpha
    // Faster way: just simple imagecopy with the resized logo which has alpha
    imagealphablending($image, true);
    imagecopy($image, $resizedLogo, $destX, $destY, 0, 0, $targetLogoW, $targetLogoH);

    imagedestroy($logo);
    imagedestroy($resizedLogo);
}

// Output the image
header('Content-Type: ' . $mime);
switch ($mime) {
    case 'image/jpeg':
        imagejpeg($image);
        break;
    case 'image/png':
        imagepng($image);
        break;
}

imagedestroy($image);
?>
