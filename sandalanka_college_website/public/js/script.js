// Main JavaScript file for Sandalanka Central College website

document.addEventListener('DOMContentLoaded', function() {
    console.log("Sandalanka Central College website script loaded.");

    // Example: Smooth scrolling for anchor links (if any)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            try {
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            } catch (error) {
                console.warn('Smooth scroll target not found:', this.getAttribute('href'));
            }
        });
    });

    // Add more interactive features as needed
});
