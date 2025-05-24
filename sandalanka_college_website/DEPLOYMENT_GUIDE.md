# Sandalanka Central College Website - Deployment Guide

This guide provides step-by-step instructions for deploying the Sandalanka Central College website to a live web server.

## 1. Server Requirements

Ensure your hosting environment meets the following minimum requirements:

*   **Web Server:** Apache, Nginx, or any other web server software with PHP support.
*   **PHP:** Version 7.4 or higher (PHP 8.x recommended).
*   **Database:** MySQL or MariaDB server.
*   **PHP Extensions:**
    *   `pdo_mysql` (for database connectivity)
    *   `mbstring` (for multibyte string operations)
    *   `openssl` (for security features, though not explicitly used for crypto in current version, good to have)
    *   `gd` (if image manipulation for uploads were to be added in future, not strictly needed now)

## 2. Domain Name & Hosting

*   **Domain Name:** You will need to purchase a domain name (e.g., `www.sandalankacentralcollege.lk`, `www.sandalanka.lk`, or your preferred domain) from a domain registrar.
*   **Web Hosting:** Choose a web hosting plan that meets the server requirements above. Options include shared hosting, VPS, or cloud hosting.
*   **DNS Configuration:** After acquiring your domain and hosting, you need to point your domain's DNS records (usually A records and CNAME for `www`) to your hosting provider's servers. Your hosting provider will give you the necessary IP addresses or nameservers. DNS propagation can take a few hours to 48 hours.

## 3. Database Setup

On your hosting provider's control panel (e.g., cPanel, Plesk, or custom panel):

1.  **Create a New Database:**
    *   Look for an option like "MySQL Databases" or "Databases".
    *   Create a new database (e.g., `sandalank_scc_db`). Note down the database name.
2.  **Create a Database User:**
    *   Create a new database user (e.g., `sandalank_scc_user`).
    *   Generate a strong password for this user and note it down.
3.  **Add User to Database & Grant Privileges:**
    *   Add the newly created user to the database you created.
    *   Grant all necessary privileges (e.g., `ALL PRIVILEGES` or specific ones like `SELECT`, `INSERT`, `UPDATE`, `DELETE`, `CREATE`, `ALTER`, `INDEX`, `DROP`) to this user for the database.
4.  **Note Credentials:** Keep the following information safe:
    *   Database Host (often `localhost`, but check with your provider)
    *   Database Name
    *   Database Username
    *   Database User Password

## 4. Update Configuration File

The main configuration for the website is in `config/config.php`. You need to update this file with your live server details:

1.  **Open `config/config.php` in a text editor.**
2.  **Update Database Credentials:**
    ```php
    define('DB_HOST', 'your_database_host'); // e.g., 'localhost'
    define('DB_NAME', 'your_database_name');
    define('DB_USER', 'your_database_username');
    define('DB_PASS', 'your_database_password');
    ```
3.  **Update Application URL:**
    Set `APP_URL` to your live website's full URL, including `https://` if you have SSL.
    ```php
    define('APP_URL', 'https://www.yourcollegedomain.lk'); // Adjust if using a subdirectory
    // If your site is in a subdirectory like www.yourdomain.lk/college, then:
    // define('APP_URL', 'https://www.yourdomain.lk/college/public');
    // Ensure this points to the 'public' directory where index.php is.
    ```
4.  **Admin Access Key:**
    Change the default `ADMIN_ACCESS_KEY` to a strong, unique value:
    ```php
    define('ADMIN_ACCESS_KEY', 'YourNewStrongAdminAccessKey!@#$');
    ```
5.  **Error Reporting (Production):**
    For a live site, it's recommended to turn off display of PHP errors to the browser for security reasons. Errors should be logged to a server file instead.
    ```php
    ini_set('display_errors', 0); // Set to 0 for production
    ini_set('display_startup_errors', 0); // Set to 0 for production
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT); // Log all errors except deprecated/strict
    // Ensure your server is configured to log PHP errors to a file.
    ```
    The current `config.php` sets `display_errors` to `1`. Change this for production.

## 5. Upload Files

1.  Connect to your web server using an FTP client (e.g., FileZilla, Cyberduck) or your hosting provider's File Manager.
2.  Navigate to your website's document root directory. This is often named `public_html`, `htdocs`, `www`, or similar.
3.  Upload all files and folders from the `sandalanka_college_website` project directory to this document root.
    *   **Important:** The `APP_URL` you configured should point to the `public` directory within the uploaded project structure if you uploaded the entire `sandalanka_college_website` folder. If you uploaded the *contents* of `sandalanka_college_website` directly into `public_html`, then `APP_URL` should reflect that.
    *   The web server should be configured so that `public/` is the effective document root for your domain, or your `APP_URL` and internal paths must account for any subdirectories.
    *   You can typically exclude:
        *   `.git` directory and `.gitignore` file (if you used Git for development).
        *   `DEPLOYMENT_GUIDE.md` (this file).
        *   Any local development configuration files.
4.  Ensure the directory structure from the project is maintained on the server.

## 6. Database Schema Import / Initial Setup

The application is designed to create the necessary database tables automatically if they don't exist when the site is first accessed. This is handled by the `initializeDatabase()` function in `src/includes/db.php`, which reads `config/schema.sql`.

1.  **Automatic Setup:** Simply try accessing your website (e.g., `https://www.yourcollegedomain.lk`). The tables should be created in your configured database.

2.  **Manual Import (Alternative):** If the automatic setup fails or you prefer manual control:
    *   Log in to phpMyAdmin (usually available in your hosting control panel).
    *   Select your newly created database.
    *   Click on the "Import" tab.
    *   Choose the `config/schema.sql` file from your local project files.
    *   Click "Go" or "Import".
    *   **Alternatively, via command line (if you have SSH access):**
        ```bash
        mysql -u YOUR_DB_USER -pYOUR_DB_PASSWORD YOUR_DB_NAME < /path/to/your/local/config/schema.sql
        ```
        (Replace placeholders with your actual credentials and path to the SQL file).

## 7. Set File and Folder Permissions

Proper file permissions are crucial for security and functionality.
*   **Directories:** Typically `755` (drwxr-xr-x).
*   **Files:** Typically `644` (-rw-r--r--).
*   **Writable Directories:** The following directories need to be writable by the web server process for file uploads:
    *   `public/uploads/papers/`
    *   `public/uploads/timetables/`
    You might need to set these to `775` (drwxrwxr-x) if your web server runs as a different user than your file owner but is in the same group. Using `777` (drwxrwxrwx) should be a last resort and avoided if possible due to security implications. Consult your hosting provider's documentation for recommended permissions.

You can usually change permissions using your FTP client (right-click -> File Permissions) or via SSH with the `chmod` command.

## 8. Final Testing

Once deployed, thoroughly test all aspects of the website:

*   **User Registration:** Student and Admin (with access key).
*   **Login/Logout:** For all roles (Student, Admin, Owner).
*   **Role Protection:** Ensure users can only access areas appropriate for their role.
*   **Student Details:** Viewing and Admin editing.
*   **File Management:**
    *   Folder creation by Admin.
    *   Exam paper uploads by Admin.
    *   Timetable uploads by Admin.
    *   Student access and download of papers and timetables.
    *   Search functionality for exam papers.
    *   Deletion of timetables by Admin.
*   **Event Management:** Admin CRUD operations and public viewing.
*   **Owner Panel:** User listing, role changes (check `MAX_OWNERS` logic), site name update simulation.
*   **Forms:** Test all forms for submission and validation.
*   **Responsiveness:** Check the site on different devices (desktop, tablet, mobile).
*   **Links:** Ensure all navigation and internal links are working correctly.

## 9. Security Best Practices (Ongoing)

*   **Regular Backups:** Set up regular automated backups of your website files and database.
*   **Software Updates:** Keep your server's PHP version, web server software (Apache/Nginx), and MySQL/MariaDB updated with the latest security patches.
*   **HTTPS (SSL):** Install an SSL certificate (e.g., Let's Encrypt or a commercial one) to enable HTTPS, encrypting data between users and the server. `APP_URL` should use `https://`.
*   **Strong Credentials:** Use strong, unique passwords for database users, admin/owner accounts, and hosting control panel access.
*   **Monitor Logs:** Regularly check server error logs and PHP error logs for any suspicious activity or issues.
*   **Review `ADMIN_ACCESS_KEY`:** Ensure it's a strong, unique key not easily guessable.
*   **File Permissions:** Double-check that file permissions are not overly permissive.

This guide should help you deploy the Sandalanka Central College website. If you encounter issues, consult your hosting provider's support and documentation.
