# Background Image Remover

A simple web application to remove backgrounds from images. This project consists of a JavaScript frontend for user interaction and a PHP backend to handle the image processing.

## Features
- User-friendly interface to upload an image.
- Sends the image to a PHP backend for background removal.
- Displays the processed image on the frontend.

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (requires image processing extensions like GD or ImageMagick, or integration with an external API)

## Directory Structure
my-image-remover-app/├── .gitignore├── README.md│├── frontend/│   ├── index.html│   ├── style.css│   └── script.js│└── backend/└── api/└── remove-background.php
## Setup and Running

### 1. Local Development
#### Backend Setup (PHP)
1.  **Install PHP and a Web Server:** You'll need PHP (with `GD` or `ImageMagick` extension enabled for actual image processing) and a web server like Apache or Nginx. Tools like XAMPP or Laragon can help set up a local PHP environment easily.
2.  **Place PHP File:** Place the `remove-background.php` file in a directory accessible by your web server (e.g., `htdocs` for Apache).
3.  **Configure CORS:** Ensure your web server or `remove-background.php` itself allows Cross-Origin Resource Sharing (CORS) from your frontend's domain. For local testing, you might temporarily allow `*` for `Access-Control-Allow-Origin`.
4.  **Note Backend URL:** Get the local URL for your `remove-background.php` (e.g., `http://localhost/api/remove-background.php`).

#### Frontend Setup (HTML, CSS, JS)
1.  **Update `script.js`:** In `frontend/script.js`, update the `phpBackendUrl` constant to point to your local PHP backend URL.
2.  **Open in Browser:** Simply open `frontend/index.html` in your web browser.

### 2. Deployment (Using Vercel/Koyeb for Frontend, Koyeb/Other PHP Host for Backend)

#### Backend Deployment (PHP)
Choose a hosting provider that supports PHP (e.g., Koyeb, DigitalOcean, a traditional shared host).
1.  **Deploy `backend/api/remove-background.php`:** Upload this file to your chosen PHP host.
2.  **Ensure PHP Extensions:** Confirm that necessary image processing extensions (like `GD` or `ImageMagick`) are installed and enabled on your hosting server, or configure your PHP to use an external API.
3.  **Configure CORS:** Set the `Access-Control-Allow-Origin` header in `remove-background.php` to your deployed frontend's URL (e.g., `https://your-frontend-domain.vercel.app` or `https://your-frontend-service.koyeb.app`).

#### Frontend Deployment (HTML, CSS, JS)
1.  **Update `script.js`:** In `frontend/script.js`, update the `phpBackendUrl` constant to point to the **public URL of your deployed PHP backend**.
2.  **Push to GitHub:** Ensure your `frontend` directory (containing `index.html`, `style.css`, `script.js`) is pushed to a GitHub repository.
3.  **Deploy via Vercel/Koyeb:**
    * **Vercel:** Connect your GitHub repository to Vercel. Set the "Root Directory" to `frontend/`. Vercel will automatically build and deploy your static site.
    * **Koyeb:** Create a new "Web Service" on Koyeb, connect your GitHub repository, and select the `frontend` directory as the build context.

## How to Use
1.  Open the website in your browser.
2.  Click "Choose File" to select an image from your device.
3.  Click "Remove Background".
4.  Wait for the processed image to appear.

## Contributing
Feel free to open issues or submit pull requests if you have suggestions or improvements!
