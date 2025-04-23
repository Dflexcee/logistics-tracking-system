<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics Solutions - Your Trusted Shipping Partner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1a365d',
                        secondary: '#2d3748',
                        accent: '#e53e3e'
                    }
                }
            }
        }
    </script>
    <style>
        .hero-slider {
            position: relative;
            height: 100vh;
            overflow: hidden;
        }
        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transform: scale(1.1);
            transition: opacity 1s ease-in-out, transform 1s ease-in-out;
        }
        .hero-slide.active {
            opacity: 1;
            transform: scale(1);
        }
        .hero-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .hero-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 2;
            width: 100%;
            max-width: 800px;
            padding: 0 20px;
        }
        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .slider-nav {
            transition: all 0.3s ease;
        }
        .slider-nav.active {
            background-color: white;
            transform: scale(1.2);
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <!-- Top Bar -->
    <div class="bg-primary text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex space-x-4">
                <a href="tel:+9288009850" class="flex items-center">
                    <i class="fas fa-phone mr-2"></i>
                    +92 (8800) - 9850
                </a>
                <a href="mailto:support@cargorover.com" class="flex items-center">
                    <i class="fas fa-envelope mr-2"></i>
                    support@cargorover.com
                </a>
            </div>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-facebook"></i>
                </a>
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="hover:text-gray-300">
                    <i class="fab fa-linkedin"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <a href="index.php" class="text-2xl font-bold text-primary dark:text-white">
                    CARGOROVER
                </a>
                <div class="hidden md:flex space-x-8">
                    <a href="index.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Home</a>
                    <a href="about.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">About</a>
                    <a href="services.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Services</a>
                    <a href="contact.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Contact Us</a>
                    <a href="track.php" class="text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-white">Track&Trace</a>
                </div>
                <button id="darkModeToggle" class="p-2 rounded-lg bg-gray-200 dark:bg-gray-700">
                    <i class="fas fa-moon dark:hidden"></i>
                    <i class="fas fa-sun hidden dark:block text-yellow-300"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Slider -->
    <div class="hero-slider relative h-screen">
        <div class="hero-slide active absolute inset-0">
            <img src="assets/img/hero-1.jpg" alt="Hero 1" class="w-full h-full object-cover">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="text-5xl font-bold mb-4">Solutions for your transport</h1>
                <p class="text-xl mb-8">Logistics Solutions for transport</p>
                <div class="flex space-x-4 justify-center">
                    <a href="services.php" class="bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Discover More</a>
                    <a href="#" class="bg-white hover:bg-gray-100 text-primary px-8 py-3 rounded-lg">Watch Video</a>
                </div>
            </div>
        </div>
        <div class="hero-slide absolute inset-0">
            <img src="assets/img/hero-2.jpg" alt="Hero 2" class="w-full h-full object-cover">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="text-5xl font-bold mb-4">Reliable & Safe Transport Solution</h1>
                <p class="text-xl mb-8">Your trusted partner in logistics</p>
                <div class="flex space-x-4 justify-center">
                    <a href="services.php" class="bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Discover More</a>
                    <a href="#" class="bg-white hover:bg-gray-100 text-primary px-8 py-3 rounded-lg">Watch Video</a>
                </div>
            </div>
        </div>
        <div class="hero-slide absolute inset-0">
            <img src="assets/img/hero-3.jpg" alt="Hero 3" class="w-full h-full object-cover">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1 class="text-5xl font-bold mb-4">Solutions for Global Shipping</h1>
                <p class="text-xl mb-8">Worldwide logistics solutions</p>
                <div class="flex space-x-4 justify-center">
                    <a href="services.php" class="bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Discover More</a>
                    <a href="#" class="bg-white hover:bg-gray-100 text-primary px-8 py-3 rounded-lg">Watch Video</a>
                </div>
            </div>
        </div>
        <!-- Slider Navigation -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 flex space-x-4">
            <button class="slider-nav active w-3 h-3 rounded-full bg-white"></button>
            <button class="slider-nav w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
            <button class="slider-nav w-3 h-3 rounded-full bg-white bg-opacity-50"></button>
        </div>
    </div>

    <!-- About Section -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold mb-6 text-gray-800 dark:text-white">Reliable Logistics Solutions Tailored for You</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-6">
                        We provide end-to-end logistics services designed to meet your needs with precision and efficiency. 
                        Our solutions are built on trust, speed, and reliability, ensuring your goods are delivered safely and on time.
                    </p>
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <p class="text-gray-600 dark:text-gray-300">Industry Leader in Smart Logistics</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <p class="text-gray-600 dark:text-gray-300">Professional Delivery Team</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <p class="text-gray-600 dark:text-gray-300">Global Network Coverage</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="assets/img/about-company.jpg" alt="Company Overview" class="rounded-lg shadow-xl">
                    <div class="absolute -bottom-6 -right-6 bg-primary text-white p-6 rounded-lg">
                        <h3 class="text-2xl font-bold">38+</h3>
                        <p>Years of Experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Our Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/service-1.jpg" alt="Air Freight" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Air Freight</h3>
                    <p class="text-gray-600">Fast and reliable air freight services worldwide</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/service-2.jpg" alt="Sea Freight" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Sea Freight</h3>
                    <p class="text-gray-600">Cost-effective sea freight solutions for large shipments</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/service-3.jpg" alt="Road Service" class="w-full h-48 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Road Service</h3>
                    <p class="text-gray-600">Efficient road transportation for local and regional deliveries</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">Expand your reach effortlessly.</h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-12">
                We offer cutting-edge logistics services designed to optimize supply chains globally.<br>
                We uphold industry-leading standards and best practices across all operations.
            </p>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Safety & Reliable Service</h3>
                    <p class="text-gray-600 dark:text-gray-300">Your cargo is protected with our advanced security measures and reliable service.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Real Time Tracking System</h3>
                    <p class="text-gray-600 dark:text-gray-300">Monitor your shipment's journey with our advanced tracking system.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">Get your shipment with our 3 easy simple steps</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">01</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Enter your product details</h3>
                    <p class="text-gray-600 dark:text-gray-300">Provide accurate information about your package, including dimensions, weight, and destination.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">02</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Documents & Payments</h3>
                    <p class="text-gray-600 dark:text-gray-300">Easily upload required shipping documents and complete payments securely.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">03</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ready to send your goods</h3>
                    <p class="text-gray-600 dark:text-gray-300">Our efficient process ensures that your goods are delivered safely and on time, wherever they need to go.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-20 bg-gray-900 text-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                <div>
                    <h3 class="text-4xl font-bold mb-2 counter" data-target="1500">0</h3>
                    <p class="text-gray-400">Projects Completed</p>
                </div>
                <div>
                    <h3 class="text-4xl font-bold mb-2 counter" data-target="98">0</h3>
                    <p class="text-gray-400">Customer Satisfaction</p>
                </div>
                <div>
                    <h3 class="text-4xl font-bold mb-2 counter" data-target="50">0</h3>
                    <p class="text-gray-400">Countries Served</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">What They're Saying About Transport</h2>
            <p class="text-center text-gray-600 dark:text-gray-300 mb-12">
                We take pride in delivering exceptional service to our customers. Hear from those who have experienced our reliable and efficient logistics solutions. Your satisfaction is our priority.
            </p>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">"Cargorover has consistently exceeded our expectations. Their timely deliveries and professional service make them a trusted partner for our logistics needs. Highly recommended!"</p>
                    <div class="flex items-center">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-800 dark:text-white">Sarah W.</h4>
                            <p class="text-gray-600 dark:text-gray-300">Supply Manager</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">"We've been using Cargorover for over a year, and their reliability is unmatched. Whether it's road, air, or sea freight, they handle everything with the utmost care and precision."</p>
                    <div class="flex items-center">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-800 dark:text-white">John M.</h4>
                            <p class="text-gray-600 dark:text-gray-300">Operations Director</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <p class="text-gray-600 dark:text-gray-300 mb-4">"From booking to delivery, Transport makes logistics simple. Their easy-to-use platform and responsive customer support make all the difference in ensuring our shipments are on track."</p>
                    <div class="flex items-center">
                        <div class="ml-4">
                            <h4 class="font-bold text-gray-800 dark:text-white">Lisa T.</h4>
                            <p class="text-gray-600 dark:text-gray-300">Business Owner</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">About Us</h3>
                    <p class="text-gray-400">Your trusted partner in global logistics and shipping solutions.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                        <li><a href="about.php" class="text-gray-400 hover:text-white">About</a></li>
                        <li><a href="services.php" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Services</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Air Freight</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Sea Freight</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Road Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Warehousing</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                    <ul class="space-y-2">
                        <li class="flex items-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            88 Broklyn Golden Street. New York
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            +92 (8800) - 9850
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            support@cargorover.com
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 Cargorover. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        darkModeToggle.addEventListener('click', () => {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        });

        // Hero Slider
        const slides = document.querySelectorAll('.hero-slide');
        let currentSlide = 0;

        function nextSlide() {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        setInterval(nextSlide, 5000);
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 