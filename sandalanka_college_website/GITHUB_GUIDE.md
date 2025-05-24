# Sandalanka Central College Website - GitHub Guide

This guide provides basic instructions for setting up a GitHub repository for the Sandalanka Central College website project.

## Prerequisites

*   You have a GitHub account.
*   You have Git installed on your local machine. You can download it from [git-scm.com](https://git-scm.com/).

## Steps to Create and Push Your Repository

1.  **Create a New Repository on GitHub:**
    *   Log in to your GitHub account.
    *   Click the "+" icon in the top-right corner and select "New repository".
    *   **Repository name:** Choose a name (e.g., `sandalanka-college-website` or `scc-website`).
    *   **Description:** (Optional) Add a brief description like "Official website for Sandalanka Central College."
    *   **Public or Private:** Choose whether you want the repository to be public or private.
    *   **Initialize this repository with:**
        *   **Do NOT** check "Add a README file" if you are pushing an existing project with its own README.md (which this project has).
        *   **Do NOT** check "Add .gitignore" if you are pushing an existing project with its own .gitignore (which this project has).
        *   **Do NOT** check "Choose a license" if you plan to add one later or already have one.
    *   Click "Create repository".

2.  **Initialize a Git Repository in Your Local Project Folder:**
    *   Open your terminal or command prompt.
    *   Navigate to the root directory of your project (the `sandalanka_college_website` folder).
        ```bash
        cd path/to/your/sandalanka_college_website
        ```
    *   Initialize a new Git repository (if you haven't already):
        ```bash
        git init
        ```
        If it's already a Git repository (e.g., from development), you can skip `git init`.

3.  **Add All Project Files to Staging:**
    *   Add all files in the current directory to the Git staging area:
        ```bash
        git add .
        ```

4.  **Commit the Files:**
    *   Commit the staged files with a meaningful message:
        ```bash
        git commit -m "Initial commit of Sandalanka Central College website project"
        ```

5.  **Set Your Default Branch Name (Optional but Recommended):**
    *   GitHub's default branch is now often `main`. If your local Git default is `master`, you might want to rename it for consistency:
        ```bash
        git branch -M main
        ```
    *   If you are fine with `master` or your default is already `main`, you can skip this.

6.  **Add the GitHub Remote Repository:**
    *   On the GitHub repository page you created in Step 1, find the "HTTPS" clone URL. It will look something like `https://github.com/YOUR_USERNAME/YOUR_REPOSITORY_NAME.git`.
    *   In your terminal, add this URL as a remote named `origin`:
        ```bash
        git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY_NAME.git
        ```
        (Replace `YOUR_USERNAME` and `YOUR_REPOSITORY_NAME` with your actual GitHub username and repository name.)
    *   If you made a mistake or need to change it, you can use `git remote set-url origin NEW_URL`.
    *   To verify: `git remote -v`

7.  **Push Your Code to GitHub:**
    *   Push your local `main` (or `master`) branch to the `origin` remote on GitHub:
        ```bash
        git push -u origin main
        ```
        (If your branch is `master`, use `git push -u origin master`).
    *   The `-u` flag sets the upstream remote branch for your local branch, so next time you can just use `git push`.

Your project code should now be on GitHub! You can refresh your GitHub repository page to see the files.

## Subsequent Pushes

After making further changes to your project:

1.  Add the modified files: `git add .` (or `git add specific_file`)
2.  Commit the changes: `git commit -m "Your descriptive commit message"`
3.  Push to GitHub: `git push` (if upstream was set with `-u`)

This guide provides the fundamental steps. For more advanced Git usage, refer to the official Git documentation.
