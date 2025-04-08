document.addEventListener('DOMContentLoaded', function() {
    const cookieConsent = document.getElementById('cookieConsent');
    const acceptCookies = document.getElementById('acceptCookies');
    
    // Check if consent was already given
    if (!localStorage.getItem('cookieConsent')) {
        // Show after 2 seconds
        setTimeout(() => {
            cookieConsent.style.display = 'block';
        }, 2000);
    }
    
    // Handle accept button
    acceptCookies.addEventListener('click', function() {
        localStorage.setItem('cookieConsent', 'accepted');
        cookieConsent.style.display = 'none';
        
        // In a real implementation, you would load analytics/tracking scripts here
    });
});