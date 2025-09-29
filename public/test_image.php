<?php
// Test if we can access an image file directly
$imagePath = '../storage/app/public/products/5OjaCxxanCf1Ya2rhJywHPfxxeeUJjNgc21bVvthM.jpg';

if (file_exists($imagePath)) {
    // Set the appropriate content type
    header('Content-Type: image/jpeg');

    // Read and output the image
    readfile($imagePath);
} else {
    echo "Image not found: " . $imagePath;
}
