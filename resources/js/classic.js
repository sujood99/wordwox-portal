/**
 * Classic Template JavaScript
 * Handles classic template-specific interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize classic template features
    initClassicTemplate();
});

function initClassicTemplate() {
    // Add elegant scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add fade-in effect for classic elements
    const classicElements = document.querySelectorAll('.classic-card, .classic-button');
    classicElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transition = 'opacity 0.8s ease';
        setTimeout(() => {
            element.style.opacity = '1';
        }, index * 100);
    });
}

