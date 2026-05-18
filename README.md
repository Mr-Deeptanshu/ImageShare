# ImageShare 📸

ImageShare is a professional, fully functional image-sharing platform developed as an **AD Lab Academic Project**. It facilitates a dynamic space where logged-in users can securely upload, explore, like, save, and discuss community-shared images. 

Unlike conventional PHP/MySQL applications that store image files in local device directories, ImageShare specifically implements an advanced **Database BLOB Architecture**, encoding raw image binaries safely into the relational hierarchy.

---

## 🚀 Core Features

*   **Custom Authentication**: Isolated login and registration pages securely hashing user payloads via `password_hash()` and maintaining HTTP-based PHP Sessions.
*   **Database Binary System (BLOBs)**: Files don't live in folders—they live strictly inside the MySQL tables wrapped in modern schema constraints to shield structural execution. High-resolution upload chunks directly sync to `max_allowed_packet` configurations.
*   **Stunning Masonry Grid**: The `index.php` feed automatically parses image heights cleanly via CSS3 `column-count` formatting to securely calculate and perfectly stack an interlocking grid array—without squishing, stretching, or leaving awkward white spaces.
*   **Dynamic Search Engine**: Query images instantly by their uploaded titles, custom metadata tags, or specific predefined categories via heavily prepared UI GET queries. 
*   **Instant Interactions**: Like an image, save it to your bookmarks, or leave a discussion comment without waiting for the web page to reload! ImageShare uses jQuery AJAX (`api.php`) to execute instantaneous real-time interactions behind the scenes.
*   **Dedicated Image Viewing & Comments**: Click any feed image to expand it into a visually satisfying, structurally dedicated viewing page displaying the uploader info, tags, and comment feeds seamlessly next to the image. 

---

## 🛠️ Technology Stack

*   **Frontend**: HTML5, Vanilla CSS3 (Masonry Grids, Glassmorphism, Google 'Open Sans' Fonts), jQuery (AJAX API polling), Bootstrap 5 (UI Modals & Tabs).
*   **Backend**: PHP 8+ (Session handling, Data Streaming (`image.php`), Form Validation, Routing).
*   **Database**: MySQL (Advanced `LONGBLOB` execution, Foreign-Key Cascading Constraints, Prepared Statements).

---

## ⚙️ Local Development Instructions (XAMPP)

1. **Clone the Directory**
   Ensure the `ImageShare` folder is accurately pasted directly into your local `c:\xampp\htdocs\` working directory.

2. **Increase Database Limits (Crucial Step)**
   Since ImageShare stores entire images deep inside the SQL engine instead of relying on filesystem folders, MySQL needs to be told to accept mathematically large file packets.
   - Open your **XAMPP Control Panel**.
   - Stop your **MySQL** module if it is currently running.
   - Click the **Config** button next to MySQL and open `my.ini`.
   - Press `CTRL + F` and search strictly for `[mysqld]`.
   - Beneath that header, locate `max_allowed_packet`. Change it to comfortably accept your uploads: 
     `max_allowed_packet = 32M`
   - Save the file and Restart the MySQL module. 

3. **Initialize the Database Schema**
   - Open XAMPP's PhpMyAdmin (`http://localhost/phpmyadmin/`).
   - Create a blank new database named exactly: `imageshare`
   - Select the `imageshare` database, click the **Import** tab, and upload the `database.sql` file located directly in the project folder to instantly initialize all table relationships!

4. **Launch**
   - Open your browser and navigate to: `http://localhost/ImageShare/`
   - Sign up a fresh new account and start uploading! 

---
*Created as a semester project for AD Lab.*
