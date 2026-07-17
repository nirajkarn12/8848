// DOM Elements
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');
const closeSidebar = document.getElementById('close-sidebar');
const mobileMenuBtn = document.querySelector('.mobile-menu-btn'); // This is the button you want to use
const navLinks = document.querySelector('.nav-links');
const newsletterForm = document.getElementById('newsletter-form');
const favoriteButtons = document.querySelectorAll('.favorite-btn');
const cartButtons = document.querySelectorAll('.cart-btn');

// Initialize the application
function init() {
    console.log('App initialized. Setting up event listeners.');
    setupEventListeners();
}

// Setup event listeners
function setupEventListeners() {
    // Debugging: Check if elements are found
    console.log('sidebar element:', sidebar);
    console.log('mobileMenuBtn element:', mobileMenuBtn);

    // Sidebar toggle (for the "Filters" button, still present in your HTML)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.add('active'); // Simply adds 'active'
            console.log('sidebarToggle clicked. Sidebar should now have "active" class.');
        });
    }
    
    // Close sidebar button
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            sidebar.classList.remove('active');
            console.log('closeSidebar clicked. Sidebar "active" class removed.');
        });
    }
    
    // *** IMPORTANT CHANGE: This listener is now for the mobile-menu-btn ONLY to open the sidebar ***
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.add('active'); // Add 'active' to sidebar when mobileMenuBtn is clicked
            console.log('mobileMenuBtn clicked. Sidebar should now have "active" class.');
            // Removed the call to toggleMobileMenu here, as per your request to only activate the sidebar.
        });
    }

    // --- The 'close sidebar when clicking outside' logic has been removed as per your request for simplicity. ---
    // document.addEventListener('click', function(e) {
    //     if (sidebar && sidebar.classList.contains('active') && 
    //         !e.target.closest('.sidebar') && 
    //         !e.target.closest('#sidebar-toggle')) {
    //         sidebar.classList.remove('active');
    //     }
    // });
    
    // Newsletter form submission
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const emailInput = this.querySelector('input[type="email]');
            if (emailInput && emailInput.value) {
                showNotification('Thank you for subscribing to our newsletter!');
                emailInput.value = '';
            }
        });
    }
    
    // Favorite buttons
    favoriteButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            this.classList.toggle('active');
            toggleFavorite(this, productId);
        });
    });
    
    // Cart buttons
    cartButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            this.classList.toggle('active');
            addToCart(productId);
        });
    });
    
    // Price range slider
    const priceSlider = document.getElementById('price-range');
    const minPrice = document.getElementById('min-price');
    const maxPrice = document.getElementById('max-price');
    
    if (priceSlider && minPrice && maxPrice) {
        priceSlider.addEventListener('input', function() {
            const value = this.value;
            maxPrice.value = value;
        });
        
        minPrice.addEventListener('change', function() {
            if (parseInt(this.value) > parseInt(maxPrice.value)) {
                this.value = maxPrice.value;
            }
        });
        
        maxPrice.addEventListener('change', function() {
            if (parseInt(this.value) < parseInt(minPrice.value)) {
                this.value = minPrice.value;
            }
        });
    }
}

// Toggle mobile menu (This function is now only called if you explicitly add a listener for navLinks)
function toggleMobileMenu() {
    if (navLinks) { // Added a check for navLinks to prevent errors if it's not present
        if (navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            navLinks.style.display = 'none';
        } else {
            navLinks.classList.add('active');
            navLinks.style.display = 'flex';
            navLinks.style.flexDirection = 'column';
            navLinks.style.position = 'absolute';
            navLinks.style.top = '100%';
            navLinks.style.left = '0';
            navLinks.style.width = '100%';
            navLinks.style.backgroundColor = 'var(--white)';
            navLinks.style.padding = '20px';
            navLinks.style.boxShadow = 'var(--shadow)';
        }
    }
}

// Toggle favorite status
function toggleFavorite(button, productId) {
    const icon = button.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showNotification('Product added to favorites');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showNotification('Product removed from favorites');
    }
}

// Add product to cart
function addToCart(productId) {
    // In a real app, you would update the cart state here
    showNotification('Product added to cart');
}

// Show notification
function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-check-circle"></i>
            <p>${message}</p>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Add styles dynamically
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = 'var(--primary-color)';
    notification.style.color = 'white';
    notification.style.padding = '15px 20px';
    notification.style.borderRadius = '5px';
    notification.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
    notification.style.zIndex = '1000';
    notification.style.transform = 'translateY(100px)';
    notification.style.opacity = '0';
    notification.style.transition = 'all 0.3s ease';
    
    notification.querySelector('.notification-content').style.display = 'flex';
    notification.querySelector('.notification-content').style.alignItems = 'center';
    notification.querySelector('.notification-content').style.gap = '10px';
    
    // Show notification with animation
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateY(100px)';
        notification.style.opacity = '0';
        
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Removed the jQuery dependent code block as it was causing "ReferenceError: $ is not defined"
// $('.proceed-to-checkout').click(function() {
//     const qty = $('.quantity-input').val();
//     $('#checkout-quantity').val(qty);
//     $('#checkout-form').slideDown();
// });


// Initialize the application when DOM is loaded
document.addEventListener('DOMContentLoaded', init);

// Add CSS for animations
const style = document.createElement('style');
style.textContent = `
    .notification {
        animation: slideIn 0.3s ease forwards;
    }
    
    @keyframes slideIn {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Add animation for decorative elements */
    .decoration {
        animation: float 6s ease-in-out infinite;
    }
    
    .decoration-1 {
        animation-delay: 0s;
    }
    .decoration-2 {
        animation-delay: 1s;
    }
    .decoration-3 {
        animation-delay: 2s;
    }
    .decoration-4 {
        animation-delay: 1.5s;
    }
    
    @keyframes float {
        0% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-15px);
        }
        100% {
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);


$(document).on("submit", "#newsletter-form", function(e) {
    e.preventDefault();
    let formData = $(this).serialize();

    $.ajax({
        url: "subscribe.php",
        type: "POST",
        data: formData,
        dataType: "json",
        success: function(response) {
            if (response.status === "success") {
                Swal.fire({
                    icon: "success",
                    title: "Subscribed!",
                    text: response.message,
                    showConfirmButton: false,
                    timer: 2000
                });
                $("#newsletter-form")[0].reset();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Could not connect to server."
            });
        }
    });
});

