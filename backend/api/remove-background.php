<?php
// PHP Error Reporting (for development, remove/reduce in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set Content-Type for JSON responses by default, unless overridden for image output
// header("Content-Type: application/json"); // This line might be commented out if you output an image directly

// --- CORS (Cross-Origin Resource Sharing) Headers ---
// This is critical for your frontend (running on a different domain/port) to communicate with this backend.
// Replace `https://your-frontend-domain.vercel.app` with the actual URL of your deployed frontend.
// For local development, you might temporarily use "*" but NEVER in production.
header("Access-Control-Allow-Origin: *"); // Example: https://your-frontend-domain.vercel.app
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow POST for file uploads, GET for simple checks, OPTIONS for preflight
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allow necessary headers
header("Access-Control-Max-Age: 86400"); // Cache preflight response for 1 day (optional, but good for performance)

// Handle preflight OPTIONS request (sent by browser before actual POST/GET for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond OK to preflight
    exit(0);
}

/**
 * Sends a JSON error response and exits.
 * @param int $statusCode HTTP status code.
 * @param string $message Error message.
 */
function sendErrorResponse($statusCode, $message) {
    http_response_code($statusCode);
    header("Content-Type: application/json"); // Ensure JSON header for errors
    echo json_encode(['error' => $message]);
    exit;
}

// --- Check for Uploaded File ---
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    sendErrorResponse(400, 'No image uploaded or upload error. Error code: ' . ($_FILES['image']['error'] ?? 'N/A'));
}

$uploadedFile = $_FILES['image']['tmp_name'];
$originalFileName = $_FILES['image']['name'];
$originalFileType = $_FILES['image']['type'];

// Basic validation: Check if it's actually an image
$imageType = exif_imagetype($uploadedFile);
if ($imageType === false) {
    sendErrorResponse(400, 'Invalid image file type.');
}

// Map image type to extension for GD functions
$imageExtensions = [
    IMAGETYPE_GIF => 'gif',
    IMAGETYPE_JPEG => 'jpeg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_BMP => 'bmp',
    IMAGETYPE_WEBP => 'webp' // PHP 5.4+ for IMAGETYPE_WEBP
];

if (!isset($imageExtensions[$imageType])) {
    sendErrorResponse(400, 'Unsupported image format. Only GIF, JPEG, PNG, BMP, WEBP are supported.');
}

$extension = $imageExtensions[$imageType];

// --- PLACEHOLDER FOR ACTUAL BACKGROUND REMOVAL LOGIC ---
// This is the most complex part of your application.
// The Dockerfile ensures GD (and curl for external APIs) is available.

$image = null;
switch ($imageType) {
    case IMAGETYPE_GIF:
        $image = imagecreatefromgif($uploadedFile);
        break;
    case IMAGETYPE_JPEG:
        $image = imagecreatefromjpeg($uploadedFile);
        break;
    case IMAGETYPE_PNG:
        $image = imagecreatefrompng($uploadedFile);
        break;
    case IMAGETYPE_BMP:
        // imagecreatefrombmp requires PHP 7.2+ and GD with BMP support
        if (function_exists('imagecreatefrombmp')) {
            $image = imagecreatefrombmp($uploadedFile);
        } else {
            sendErrorResponse(500, 'BMP support not available on this PHP/GD version.');
        }
        break;
    case IMAGETYPE_WEBP:
        // imagecreatefromwebp requires PHP 5.4+ and GD with WEBP support
        if (function_exists('imagecreatefromwebp')) {
            $image = imagecreatefromwebp($uploadedFile);
        } else {
            sendErrorResponse(500, 'WEBP support not available on this PHP/GD version.');
        }
        break;
    default:
        sendErrorResponse(500, 'Could not create image resource from uploaded file (unsupported format after initial check).');
}

if (!$image) {
    sendErrorResponse(500, 'Failed to load image resource for processing.');
}

// Enable alpha blending and save full alpha channel for transparency for PNG output
imagealphablending($image, false);
imagesavealpha($image, true);

// --- Dummy Background Removal (makes it a transparent PNG without actual removal) ---
// To implement real background removal:
// 1. **Color-based Removal (Simple):** Iterate through pixels, make specific color ranges transparent.
//    (e.g., if background is solid green, find green pixels and set alpha to 0).
// 2. **External AI API (Recommended for Quality):** Use `curl` (already installed in Dockerfile)
//    to send the image to a service like remove.bg, then get their processed image back.

// Example: Making white pixels transparent (very basic, for demonstration)
/*
$width = imagesx($image);
$height = imagesy($image);
for ($x = 0; $x < $width; $x++) {
    for ($y = 0; $y < $height; $y++) {
        $rgb = imagecolorat($image, $x, $y);
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        // If pixel is close to white (e.g., for simple backgrounds)
        if ($r > 240 && $g > 240 && $b > 240) {
            $alpha = 127; // Fully transparent
            $newColor = imagecolorallocatealpha($image, $r, $g, $b, $alpha);
            imagesetpixel($image, $x, $y, $newColor);
        }
    }
}
*/

// --- Output the Processed Image ---
// Set the content type header to PNG, as we are outputting a PNG
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="processed_image.png"');

// Output the image data as PNG
imagepng($image);

// Free up memory
imagedestroy($image);

// Optional: Clean up temporary uploaded file
if (file_exists($uploadedFile)) {
    unlink($uploadedFile);
}
?>
