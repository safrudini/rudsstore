// Basic JavaScript for interactive elements

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Phone number validation
    const phoneInputs = document.querySelectorAll('input[type="text"][id="phone_number"], input[type="text"][id="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const phone = this.value;
            if (phone && !/^08[0-9]{9,12}$/.test(phone)) {
                alert('Format nomor HP tidak valid! Harus diawali dengan 08 dan panjang 10-13 digit.');
                this.focus();
            }
        });
    });
    
    // Price formatting
    const priceInputs = document.querySelectorAll('input[type="number"][id="price"], input[type="number"][id="amount"]');
    priceInputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(0);
            }
        });
    });
});

// Tab functionality
function openTab(tabName) {
    var i, tabContent, tabButtons;
    
    // Hide all tab content
    tabContent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].classList.remove("active");
    }
    
    // Remove active class from all buttons
    tabButtons = document.getElementsByClassName("tab-button");
    for (i = 0; i < tabButtons.length; i++) {
        tabButtons[i].classList.remove("active");
    }
    
    // Show the specific tab content and add active class to the button
    document.getElementById(tabName).classList.add("active");
    event.currentTarget.classList.add("active");
}