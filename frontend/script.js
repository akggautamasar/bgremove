document.addEventListener('DOMContentLoaded', () => {
    const imageInput = document.getElementById('imageInput');
    const removeBgButton = document.getElementById('removeBgButton');
    const outputImage = document.getElementById('outputImage');
    const downloadLink = document.getElementById('downloadLink');
    const messageBox = document.getElementById('messageBox');

    // --- IMPORTANT: Replace with the actual URL of your deployed PHP backend ---
    // During local development, this might be: 'http://localhost/backend/api/remove-background.php'
    // After deploying your PHP, it will be its public URL (e.g., 'https://your-php-service.koyeb.app/api/remove-background.php')
    const phpBackendUrl = 'YOUR_PHP_BACKEND_URL_HERE'; // <<< === CHANGE THIS ===

    /**
     * Displays a message in the message box.
     * @param {string} message - The message to display.
     * @param {string} type - 'success' or 'error' to change background color.
     */
    function showMessage(message, type = 'error') {
        messageBox.textContent = message;
        messageBox.style.backgroundColor = type === 'success' ? '#27ae60' : '#e74c3c';
        messageBox.style.display = 'block';
        // Hide message after 5 seconds
        setTimeout(() => {
            messageBox.style.display = 'none';
        }, 5000);
    }

    removeBgButton.addEventListener('click', async () => {
        if (phpBackendUrl === 'YOUR_PHP_BACKEND_URL_HERE') {
            showMessage('Error: PHP Backend URL is not configured. Please update script.js.', 'error');
            return;
        }

        if (imageInput.files.length === 0) {
            showMessage('Please select an image first.', 'error');
            return;
        }

        const file = imageInput.files[0];
        const formData = new FormData();
        formData.append('image', file); // 'image' is the name your PHP script will look for

        // Disable button and show loading state
        removeBgButton.disabled = true;
        removeBgButton.textContent = 'Processing...';
        outputImage.src = 'https://placehold.co/400x300/cccccc/333333?text=Loading...'; // Simple loading placeholder
        outputImage.alt = 'Processing...';
        downloadLink.style.display = 'none'; // Hide download link during processing

        try {
            const response = await fetch(phpBackendUrl, {
                method: 'POST',
                body: formData,
                // Do NOT set Content-Type header manually for FormData, fetch does it correctly
            });

            if (!response.ok) {
                // If the server response is an error (e.g., 400, 500)
                const errorText = await response.text(); // Try to read error message from server
                throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
            }

            // Assuming your PHP returns the processed image directly (e.g., as a PNG/JPG blob)
            const processedImageBlob = await response.blob();

            // Check if the returned blob is an actual image
            if (!processedImageBlob.type.startsWith('image/')) {
                const textResponse = await processedImageBlob.text(); // Try to read as text to see error message
                throw new Error(`Unexpected response from server. Not an image. Server message: ${textResponse.substring(0, 200)}...`);
            }

            const imageUrl = URL.createObjectURL(processedImageBlob);
            outputImage.src = imageUrl;
            outputImage.alt = 'Processed Image';
            showMessage('Background removed successfully!', 'success');

            // Enable download link
            downloadLink.href = imageUrl;
            downloadLink.style.display = 'inline-block';

        } catch (error) {
            console.error('Error removing background:', error);
            outputImage.src = 'https://placehold.co/400x300/ffdddd/cc0000?text=Error'; // Error placeholder
            outputImage.alt = 'Error processing image.';
            showMessage('Failed to remove background. Error: ' + error.message, 'error');
            downloadLink.style.display = 'none'; // Ensure download link is hidden
        } finally {
            removeBgButton.disabled = false;
            removeBgButton.textContent = 'Remove Background';
        }
    });

    // Optional: Display selected image preview before upload
    imageInput.addEventListener('change', (event) => {
        if (event.target.files.length > 0) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = (e) => {
                outputImage.src = e.target.result;
                outputImage.alt = 'Selected Image Preview';
                downloadLink.style.display = 'none'; // Hide download link if a new image is selected
            };
            reader.readAsDataURL(file);
        } else {
            outputImage.src = 'https://placehold.co/400x300/e0e0e0/555555?text=Processed+Image+Appears+Here';
            outputImage.alt = 'Processed Image Placeholder';
            downloadLink.style.display = 'none';
        }
    });
});
