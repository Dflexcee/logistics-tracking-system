<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track & Trace - Cargorover</title>
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
                <h1 class="text-4xl font-bold mb-4">Track Your Shipment</h1>
                <p class="text-xl">Enter your tracking number to get real-time updates</p>
            </div>
        </div>
    </div>

    <!-- Tracking Section -->
    <section class="py-20 bg-white dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto">
                <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                    <form class="space-y-6">
                        <div>
                            <label for="tracking_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tracking Number</label>
                            <input type="text" id="tracking_number" name="tracking_number" class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary" placeholder="Enter your tracking number">
                        </div>
                        <button type="submit" class="w-full bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Track Now</button>
                    </form>
                </div>

                <!-- Tracking Result (Hidden by default) -->
                <div id="tracking_result" class="mt-8 hidden">
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Shipment Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-800 dark:text-white font-medium">Package Picked Up</p>
                                    <p class="text-gray-600 dark:text-gray-300 text-sm">March 15, 2024 - 10:30 AM</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fas fa-check text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-800 dark:text-white font-medium">In Transit</p>
                                    <p class="text-gray-600 dark:text-gray-300 text-sm">March 15, 2024 - 2:45 PM</p>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                                    <i class="fas fa-truck text-white"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-gray-800 dark:text-white font-medium">Out for Delivery</p>
                                    <p class="text-gray-600 dark:text-gray-300 text-sm">March 16, 2024 - 9:15 AM</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-20 bg-gray-100 dark:bg-gray-900">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800 dark:text-white">How to Track Your Shipment</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">01</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Enter Tracking Number</h3>
                    <p class="text-gray-600 dark:text-gray-300">Enter your unique tracking number in the field above.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">02</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Click Track Now</h3>
                    <p class="text-gray-600 dark:text-gray-300">Click the Track Now button to get real-time updates.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg">
                    <div class="text-4xl font-bold text-primary mb-4">03</div>
                    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">View Status</h3>
                    <p class="text-gray-600 dark:text-gray-300">View detailed information about your shipment's current status.</p>
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

        // Tracking form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const trackingNumber = document.getElementById('tracking_number').value;
            if (trackingNumber) {
                document.getElementById('tracking_result').classList.remove('hidden');
            }
        });
    </script>
</body>
</html> 