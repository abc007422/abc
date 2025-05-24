# Sandalanka Central College Website

This project is the official website for Sandalanka Central College, designed to serve students, staff, and the public with relevant information and resources. It includes features for user authentication, student details management, file sharing (exam papers and timetables), event announcements, and site administration.

## Key Features Implemented

*   **User Roles:** Student, Administrator, and Owner roles with distinct privileges.
*   **Authentication:** Secure registration and login for all user roles.
*   **Dashboards:** Dedicated dashboards for Students, Admins, and Owners.
*   **Student Details Management:** Students can view their details (after index number confirmation), and Admins can manage these details.
*   **File Management (Exam Papers):**
    *   Admins/Owners can create folders and upload exam paper PDFs/images.
    *   Students can browse, search (by filename, subject, year, description), and download exam papers.
*   **Time Tables Management:**
    *   Admins/Owners can upload class timetables (PDFs/images) for different grades and classes.
    *   Students can view and download relevant class timetables.
*   **Event Management:**
    *   Admins/Owners can create, edit, and delete school events.
    *   A public page displays upcoming and past events.
*   **Owner Panel:**
    *   Site statistics overview.
    *   Management of all user accounts, including role changes (with limits on owner promotion/demotion).
    *   Simulated site name update and display of other configuration details.
*   **Responsive Design:** The website utilizes Bootstrap 5 for a responsive and mobile-friendly user interface across various devices.
*   **Search Functionality:** Students can search for exam papers.

## Project Structure

*   `public/`: Publicly accessible files (CSS, JS, images, entry point `index.php`).
    *   `uploads/`: Directory for user-uploaded content (papers, timetables).
*   `src/`: PHP source code.
    *   `controllers/`: Handles application logic and request processing.
    *   `includes/`: Core files like database connection (`db.php`).
    *   `models/`: (Currently not used, but available for future expansion with complex data logic).
    *   `views/`: Contains all PHP files responsible for generating HTML output (templates).
        *   `layouts/`: Main site layout.
        *   `auth/`, `admin/`, `owner/`, `student/`, `public/`: Views specific to these sections.
*   `config/`: Configuration files.
    *   `config.php`: Main application and database settings.
    *   `schema.sql`: Database table definitions.
*   `tests/`: (Currently contains a utility script for listing files).
*   `.gitignore`: Specifies intentionally untracked files that Git should ignore.
*   `DEPLOYMENT_GUIDE.md`: Instructions for deploying the website to a live server.
*   `GITHUB_GUIDE.md`: Instructions for setting up a GitHub repository for this project.
*   `README.md`: This file.

## Basic Setup (for Local Development)

1.  **Web Server & PHP:** Ensure you have a local web server environment (like XAMPP, WAMP, MAMP, or Docker with PHP/MySQL) with PHP 7.4+ and MySQL/MariaDB.
2.  **Database:**
    *   Create a MySQL/MariaDB database (e.g., `sandalanka_college_db`).
    *   Create a database user and grant it privileges to the database.
3.  **Configuration:**
    *   Copy the project files to your web server's document root (e.g., `htdocs/sandalanka_college_website`).
    *   Open `config/config.php` and update the following:
        *   `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` with your local database credentials.
        *   `APP_URL` to your local development URL (e.g., `http://localhost/sandalanka_college_website/public`).
        *   Optionally, review `ADMIN_ACCESS_KEY` and other settings.
4.  **Database Schema:**
    *   The application attempts to create tables automatically from `config/schema.sql` upon first access.
    *   Alternatively, you can manually import `config/schema.sql` into your database using a tool like phpMyAdmin.
5.  **Access:** Open `APP_URL` in your browser (e.g., `http://localhost/sandalanka_college_website/public`).

## Deployment

For deploying the website to a live server, please refer to the detailed instructions in [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md).

## Version Control with GitHub

To set up a GitHub repository for this project, please refer to the instructions in [GITHUB_GUIDE.md](GITHUB_GUIDE.md).

## License

(License information to be added if applicable - e.g., MIT, GPL. Currently, no license is specified.)

This project was developed based on a detailed specification. For further development or inquiries, please contact the project maintainers.
