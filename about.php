<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Cargorover</title>
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

    <!-- Hero Section -->
    <div class="relative bg-gray-900 h-[400px]">
        <div class="absolute inset-0 bg-black opacity-50"></div>
        <div class="relative container mx-auto px-4 h-full flex items-center">
            <div class="text-white max-w-2xl">
                <h1 class="text-4xl font-bold mb-4">About Us</h1>
                <p class="text-xl">Your trusted partner in global logistics and shipping solutions</p>
            </div>
        </div>
    </div>

    <!-- About Section -->
    <section class="py-20">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <img src="assets/img/about-company.jpg" alt="About Company" class="w-full h-96 object-cover rounded-lg">
                </div>
                <div>
                    <h2 class="text-4xl font-bold mb-6">About Our Company</h2>
                    <p class="text-gray-600 mb-6">We are a leading logistics company providing comprehensive shipping and transportation solutions worldwide. With years of experience in the industry, we ensure reliable and efficient service for all our clients.</p>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-xl font-bold mb-2">Our Mission</h3>
                            <p class="text-gray-600">To provide innovative logistics solutions that exceed customer expectations.</p>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-2">Our Vision</h3>
                            <p class="text-gray-600">To be the global leader in logistics and transportation services.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Our Leadership Team</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/team-1.jpg" alt="Team Member 1" class="w-full h-64 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">John Doe</h3>
                    <p class="text-gray-600">CEO & Founder</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/team-2.jpg" alt="Team Member 2" class="w-full h-64 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Jane Smith</h3>
                    <p class="text-gray-600">Operations Director</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <img src="assets/img/team-3.jpg" alt="Team Member 3" class="w-full h-64 object-cover rounded-lg mb-4">
                    <h3 class="text-xl font-bold mb-2">Mike Johnson</h3>
                    <p class="text-gray-600">Technical Director</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">Why Choose Us</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <i class="fas fa-globe text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Global Network</h3>
                    <p class="text-gray-600 dark:text-gray-300">With partners in over 150 countries, we can deliver your shipments anywhere in the world.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <i class="fas fa-headset text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">24/7 Customer Support</h3>
                    <p class="text-gray-600 dark:text-gray-300">Our dedicated team is available round the clock to assist you with any queries.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <i class="fas fa-chart-line text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Advanced Technology</h3>
                    <p class="text-gray-600 dark:text-gray-300">We use cutting-edge technology to ensure efficient and reliable logistics solutions.</p>
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
    </script>
</body>
</html> 