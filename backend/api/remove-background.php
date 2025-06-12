<?php
// PHP Error Reporting (for development, remove/reduce in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- CORS (Cross-Origin Resource Sharing) Headers ---
// This is critical for your frontend (running on a different domain/port) to communicate with this backend.
// Replace `https://your-frontend-domain.vercel.app` with the actual URL of your deployed frontend.
// For local development, you might temporarily use "*" but be aware of security implications in production.
header("Access-Control-Allow-Origin: *"); // Example: https://your-frontend-domain.vercel.app
header("Access-Control-Allow-Methods: POST, GET, OPTIONS"); // Allow POST for file uploads, GET for simple checks, OPTIONS for preflight
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Allow necessary headers
header("Access-Control-Max-Age: 86400"); // Cache preflight response for 1 day (optional, but good for performance)

// Handle preflight OPTIONS request (sent by browser before actual POST/GET for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // Respond OK to preflight
    exit(0);
}

// --- Check for Uploaded File ---
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'No image uploaded or upload error. Error code: ' . ($_FILES['image']['error'] ?? 'N/A')]);
    exit;
}

$uploadedFile = $_FILES['image']['tmp_name'];
$originalFileName = $_FILES['image']['name'];
$originalFileType = $_FILES['image']['type'];

// Basic validation: Check if it's actually an image
$imageType = exif_imagetype($uploadedFile);
if ($imageType === false) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid image file type.']);
    exit;
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
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported image format. Only GIF, JPEG, PNG, BMP, WEBP are supported.']);
    exit;
}

$extension = $imageExtensions[$imageType];

// --- PLACEHOLDER FOR ACTUAL BACKGROUND REMOVAL LOGIC ---
// This is the most complex part of your application.
// You have two main approaches here:

// Approach 1: Using PHP's GD Library or ImageMagick Extension
// This requires these extensions to be installed and enabled on your PHP server.
// The code below is a simplified example that attempts to load an image,
// make it transparent, and then save it as PNG.
// A real background removal algorithm (e.g., semantic segmentation, chroma keying)
// is significantly more complex and beyond the scope of a simple example.

// To make this demo functional without a full background removal algorithm,
// we'll simply load the image and convert it to a transparent PNG.
// This won't remove the background *content*, but it will make it a PNG
// which is often a format suitable for transparent backgrounds.

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
        $image = imagecreatefrombmp($uploadedFile); // Requires PHP 7.2+ or imagecreatefromwbmp
        break;
    case IMAGETYPE_WEBP:
        $image = imagecreatefromwebp($uploadedFile); // Requires PHP 5.4+ and webp support
        break;
    default:
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Could not create image resource from uploaded file.']);
        exit;
}

if (!$image) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load image resource.']);
    exit;
}

// Enable alpha blending and save full alpha channel for transparency
imagealphablending($image, false);
imagesavealpha($image, true);

// Create a transparent canvas (for GD library to work with transparency)
// This doesn't remove background content, but ensures the image supports transparency if you were to draw onto it.
// To actually remove the background based on color, you would analyze pixel colors.
// For example, to make a specific color transparent (like a green screen):
// $transparentColor = imagecolorallocate($image, 0, 255, 0); // Green
// imagecolortransparent($image, $transparentColor);

// You might also need to resize or manipulate the image here.

// Approach 2: Calling an External Background Removal API (Recommended for real removal)
// For actual advanced background removal using AI, you would typically integrate with a service like:
// - remove.bg (https://www.remove.bg/api)
// - remove-background.ai
// - Google Cloud Vision API (for object detection, then mask creation)
// This would involve using cURL or a library like Guzzle to send the image data to their API
// and receive the processed image back.

/*
// Example using cURL to send image to an external API (CONCEPTUAL, NOT A FULL IMPLEMENTATION)
$api_endpoint = 'https://api.example.com/remove-background'; // Replace with actual API endpoint
$api_key = 'YOUR_EXTERNAL_API_KEY'; // Replace with your actual API key

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['image_file' => new CURLFile($uploadedFile, $originalFileType, $originalFileName)]);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Api-Key: ' . $api_key,
    // Add other headers as required by the API (e.g., Content-Type for JSON response)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get response as a string
$apiResponse = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => 'API call failed: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Process $apiResponse - it might be JSON containing a base64 image or a direct image blob
// For this example, we assume it's a direct image blob for simplicity below.
$processedImageData = $apiResponse; // This would be the actual processed image from the API
*/

// --- Output the Processed Image ---
// Set the content type header based on the output image format
// We are forcing PNG output in this example for transparency.
header('Content-Type: image/png');
header('Content-Disposition: inline; filename="processed_image.png"'); // Suggests filename for download

// Output the image data.
// For GD:
imagepng($image); // Output the image resource as PNG
imagedestroy($image); // Free up memory

// For external API:
// echo $processedImageData; // If API returns raw image data

// Optional: Clean up temporary uploaded file
if (file_exists($uploadedFile)) {
    unlink($uploadedFile);
}
?>
