<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Cargorover</title>
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
                <h1 class="text-4xl font-bold mb-4">Our Services</h1>
                <p class="text-xl">Comprehensive logistics solutions for your business needs</p>
            </div>
        </div>
    </div>

    <!-- Main Services -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">Offering Sustainable Logistics Services</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Air Freight -->
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <img src="assets/img/service-1.jpg" alt="Air Freight" class="w-full h-48 object-cover rounded-lg mb-4">
                    <i class="fas fa-plane text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Air freight</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Fast and secure shipping solutions to meet your urgent delivery needs across the globe.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Express delivery options</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Global coverage</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Real-time tracking</span>
                        </li>
                    </ul>
                </div>

                <!-- Sea Freight -->
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <img src="assets/img/service-2.jpg" alt="Sea Freight" class="w-full h-48 object-cover rounded-lg mb-4">
                    <i class="fas fa-ship text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Sea freight</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Reliable and affordable shipping for large-scale goods to international destinations.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>FCL and LCL options</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Port-to-port delivery</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Customs clearance</span>
                        </li>
                    </ul>
                </div>

                <!-- Road Service -->
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <img src="assets/img/service-3.jpg" alt="Road Service" class="w-full h-48 object-cover rounded-lg mb-4">
                    <i class="fas fa-truck text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Road service</h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        Efficient transport solutions ensuring timely deliveries across local and regional routes.
                    </p>
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Local and long-distance haulage</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Temperature-controlled transport</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-primary mt-1 mr-3"></i>
                            <span>Door-to-door delivery</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Additional Services -->
    <section class="py-20 bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">Additional Services</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Warehousing -->
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <i class="fas fa-warehouse text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Warehousing</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Secure storage solutions with advanced inventory management systems.
                    </p>
                </div>

                <!-- Customs Clearance -->
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <i class="fas fa-file-contract text-4xl text-primary mb-4"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Customs Clearance</h3>
                    <p class="text-gray-600 dark:text-gray-300">
                        Expert assistance with customs documentation and clearance procedures.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <div class="text-4xl font-bold text-primary mb-4">01</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Enter your product details</h3>
                    <p class="text-gray-600 dark:text-gray-300">Provide accurate information about your package, including dimensions, weight, and destination.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <div class="text-4xl font-bold text-primary mb-4">02</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Documents & Payments</h3>
                    <p class="text-gray-600 dark:text-gray-300">Easily upload required shipping documents and complete payments securely.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <div class="text-4xl font-bold text-primary mb-4">03</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Ready to send your goods</h3>
                    <p class="text-gray-600 dark:text-gray-300">Our efficient process ensures that your goods are delivered safely and on time, wherever they need to go.</p>
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