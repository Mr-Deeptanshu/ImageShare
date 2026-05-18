# ImageShare Project Summary

ImageShare is a PHP/MySQL-based image-sharing platform designed as an academic project. It uses a **Database BLOB Architecture** to store images directly in the MySQL database (using `LONGBLOB`) instead of file system directories, ensuring secure, relational storage. The platform supports user authentication, image uploads, interactions (likes, saves, comments), search, and a responsive masonry grid layout. Below is a detailed breakdown of all functionalities implemented, organized by core features and components.

## 1. Database Schema and Data Storage
- **Tables** (from `database.sql`):
  - `users`: Stores user accounts with `id`, `username`, `email`, `password_hash` (hashed with `password_hash()`), `remember_token`, and `created_at`.
  - `categories`: Predefined categories (e.g., Nature, Technology, Movies) with `id` and `name`. Includes default inserts for 8 categories.
  - `images`: Core table for images with `id`, `user_id` (foreign key to users), `title`, `description`, `category_id` (optional foreign key to categories), `tags` (comma-separated text), `image_data` (LONGBLOB for binary image data), `mime_type`, and `created_at`. Foreign keys enforce cascading deletes.
  - `likes`: Junction table for user-image likes (`user_id`, `image_id` as composite primary key).
  - `comments`: Stores comments with `id`, `user_id`, `image_id`, `comment_text`, and `created_at`.
  - `saved`: Junction table for saved/bookmarked images (`user_id`, `image_id` as composite primary key).
- **Functionality**: All data is stored relationally with constraints. Images are encoded as binaries and streamed via PHP (no file system access). Prepared statements prevent SQL injection.

## 2. User Authentication and Session Management
- **Signup** (`auth/signup.php`):
  - Form for username, email, password, and confirm password.
  - Validation: Checks for existing username/email, password strength (length, match).
  - Hashes password with `password_hash()` and inserts into `users` table.
  - Redirects to login on success; displays errors (e.g., duplicate email).
- **Login** (`auth/login.php`):
  - Form for email/username and password.
  - Verifies credentials using `password_verify()` against `password_hash`.
  - Starts PHP session on success, storing `user_id` and `username`.
  - Optional "Remember Me" (sets a token, though implementation is basic).
  - Redirects to home (`index.php`) on success; handles errors (e.g., invalid credentials).
- **Logout** (`auth/logout.php`):
  - Destroys session and redirects to home.
- **Session Checks**: All protected pages (e.g., upload, profile) check `$_SESSION['user_id']` and redirect to login if not set.
- **Functionality**: Secure HTTP-based sessions; no persistent cookies beyond basic remember token.

## 3. Image Upload and Management
- **Upload Page** (`upload.php`):
  - Requires login; fetches categories from DB for dropdown.
  - Form fields: Title (required), Category (optional), Tags (comma-separated), Description (optional), Image file (required, accepts image/*).
  - Validation:
    - File type: Only JPG, JPEG, GIF, PNG allowed.
    - File size: Max 30MB (configurable).
    - Reads file as binary (`file_get_contents()`) and stores in `images` table via prepared statement.
  - Error Handling: Displays messages for invalid format, size, or DB errors (e.g., `max_allowed_packet` issues in MySQL).
  - Success: Inserts image and shows confirmation.
- **Edit Image** (`edit_image.php`):
  - Accessible only by image owner (checks `user_id`).
  - Pre-fills form with existing title, description, category, tags.
  - Updates `images` table on POST; no image re-upload (only metadata).
  - Delete functionality: Button to remove image (cascades deletes for likes/comments/saves).
- **Image Serving** (`image.php`):
  - Streams images from DB `image_data` based on `id`.
  - Sets correct `Content-Type` from `mime_type`.
  - Supports download: Adds `Content-Disposition: attachment` if `download=1` query param.
  - Security: Only serves if image exists; no direct file access.

## 4. Home Page and Gallery Display
- **Index Page** (`index.php`):
  - Displays hero section with search form (text input for title/tags/description, category dropdown).
  - Fetches and displays images in a masonry grid (CSS `column-count` for responsive stacking).
  - Filtering: By search query (LIKE on title/tags/description) and/or category.
  - Sorting: By `created_at` DESC.
  - If no images, shows placeholder message.
  - Navigation: Dynamic based on login status (e.g., "Hi, username", Upload button for logged-in users).
- **Gallery Layout** (`homePage.css`):
  - Masonry grid: Images stack without gaps using CSS columns.
  - Responsive: Adjusts columns on different screen sizes.
  - Tag badges on images for categories.
- **JavaScript** (`homePage.js`):
  - Handles mobile nav toggle (hamburger menu).

## 5. Image Viewing and Interactions
- **View Page** (`view.php`):
  - Displays full image, title, description, category/tags as badges.
  - Uploader info (username).
  - Interaction buttons: Like (with count), Save/Bookmark, Download.
  - Comments section: Lists comments with username and text; form to add new comment (requires login).
  - Edit/Delete buttons for image owner.
- **AJAX Interactions** (`api.php`):
  - Handles POST requests for real-time updates (no page reload).
  - Actions:
    - `get_info`: Fetches likes count, user's like/save status, and comments for an image.
    - `like`: Toggles like (insert/delete in `likes` table); returns updated count and status.
    - `save`: Toggles save (insert/delete in `saved` table); returns status.
    - `comment`: Inserts new comment into `comments` table; refreshes comments list.
  - Uses jQuery for AJAX calls; checks login status before actions.
  - Returns JSON responses with status, data, or errors.

## 6. User Profile
- **Profile Page** (`profile.php`):
  - Displays user's uploaded images in a grid (similar to home gallery).
  - Shows saved/bookmarked images separately.
  - Tabs or sections for "My Images" and "Saved Images".
  - Fetches data via DB queries (joins with `images` and `saved` tables).
  - Edit/Delete links for user's own images.

## 7. Search and Filtering
- **Dynamic Search**:
  - On `index.php`: Text search across title, tags, description (case-insensitive LIKE).
  - Category filter: Dropdown to filter by `category_id`.
  - Combines filters (e.g., search + category).
  - Uses prepared statements for security.
- **Functionality**: Instant results on form submit; no AJAX for search (page reload).

## 8. Additional Features
- **About Page** (`about/about.html`):
  - Static HTML page with project info, styled with `about.css`.
- **Responsive Design**:
  - Uses Bootstrap 5 for modals/tabs, custom CSS for dark theme (black background, white text).
  - Mobile-friendly nav (toggle button).
- **Error Handling and Validation**:
  - Form validation on client/server side.
  - DB errors (e.g., packet size) with user-friendly messages.
  - File upload errors (e.g., size, type).
- **Security**:
  - Prepared statements for all DB queries.
  - Session-based auth.
  - HTML escaping (`htmlspecialchars()`) to prevent XSS.
  - No direct file uploads to filesystem.
- **Dependencies**:
  - PHP 8+, MySQL, XAMPP.
  - Frontend: jQuery, Font Awesome, Bootstrap, Google Fonts.
  - Requires `max_allowed_packet = 32M` in MySQL config for large images.

## Setup and Deployment
- Clone to `c:\xampp\htdocs\ImageShare`.
- Configure MySQL (`my.ini` for packet size).
- Import `database.sql` to create DB and tables.
- Access via `http://localhost/ImageShare/`.
- No build process; runs directly on XAMPP.

This covers all implemented functionalities based on the codebase. The project emphasizes security, real-time interactions, and a clean UI, making it a complete image-sharing platform. If you need details on specific files or code snippets, let me know!