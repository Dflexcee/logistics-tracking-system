// Page Loader
document.addEventListener('DOMContentLoaded', () => {
    const loader = document.createElement('div');
    loader.className = 'fixed inset-0 bg-white dark:bg-gray-900 z-50 flex items-center justify-center';
    loader.innerHTML = `
        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary"></div>
    `;
    document.body.appendChild(loader);

    window.addEventListener('load', () => {
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.remove();
        }, 500);
    });
});

// Slider Functionality
function initSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    const navButtons = document.querySelectorAll('.slider-nav');
    let currentSlide = 0;

    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.style.opacity = '0';
            slide.style.transform = 'scale(1.1)';
        });
        navButtons.forEach(btn => btn.classList.remove('active'));

        // Show current slide
        slides[index].style.opacity = '1';
        slides[index].style.transform = 'scale(1)';
        navButtons[index].classList.add('active');
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Add click events to navigation buttons
    navButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });

    // Auto slide every 5 seconds
    setInterval(nextSlide, 5000);

    // Initial slide
    showSlide(0);
}

// Counter Animation
function animateCounter(element) {
    const target = parseInt(element.getAttribute('data-target'));
    const duration = 2000; // 2 seconds
    const step = target / (duration / 16); // 60fps
    let current = 0;

    const updateCounter = () => {
        current += step;
        if (current < target) {
            element.textContent = Math.floor(current);
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = target;
        }
    };

    updateCounter();
}

// Initialize counters when they come into view
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounter(entry.target);
            counterObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

document.querySelectorAll('.counter').forEach(counter => {
    counterObserver.observe(counter);
});

// Page Transitions
document.addEventListener('click', (e) => {
    const link = e.target.closest('a');
    if (link && link.href && link.href.startsWith(window.location.origin)) {
        e.preventDefault();
        const loader = document.createElement('div');
        loader.className = 'fixed inset-0 bg-white dark:bg-gray-900 z-50 flex items-center justify-center';
        loader.innerHTML = `
            <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-primary"></div>
        `;
        document.body.appendChild(loader);

        setTimeout(() => {
            window.location.href = link.href;
        }, 500);
    }
});

// Initialize slider if it exists
if (document.querySelector('.hero-slider')) {
    initSlider();
}

// Random Counter Values
function updateCounterValues() {
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
        const currentValue = parseInt(counter.getAttribute('data-target'));
        const newValue = Math.floor(Math.random() * (currentValue * 1.5 - currentValue * 0.5) + currentValue * 0.5);
        counter.setAttribute('data-target', newValue);
        counter.textContent = '0';
        counterObserver.observe(counter);
    });
}

// Update counter values every 5 minutes
setInterval(updateCounterValues, 300000); 