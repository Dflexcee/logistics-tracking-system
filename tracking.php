<?php
require_once 'include/db.php';

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Get company settings
$company_settings = null;
$sql = "SELECT * FROM company_settings LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $company_settings = $result->fetch_assoc();
}

$tracking_number = sanitize_input($_GET['tracking_number'] ?? '');
$consignment = null;
$updates = [];
$error = null;

if (!empty($tracking_number)) {
    try {
        // Get consignment details
        $sql = "SELECT c.*, u.name as agent_name,
                s.name as sender_name, s.phone as sender_phone, s.address as sender_address,
                r.name as receiver_name, r.phone as receiver_phone, r.address as receiver_address,
                c.package_type, c.amount_paid, c.payment_method, c.payment_status, c.paid_by
                FROM consignments c 
                LEFT JOIN users u ON c.agent_id = u.id 
                LEFT JOIN senders s ON c.sender_id = s.id
                LEFT JOIN receivers r ON c.receiver_id = r.id
                WHERE c.tracking_number = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Database error");
        }
        
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Tracking number not found';
        } else {
            $consignment = $result->fetch_assoc();
            
            // Get tracking updates
            $sql = "SELECT th.* 
                    FROM tracking_history th 
                    WHERE th.consignment_id = ? 
                    ORDER BY th.created_at DESC";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Database error");
            }
            
            $stmt->bind_param("i", $consignment['id']);
            $stmt->execute();
            $updates_result = $stmt->get_result();
            
            while ($row = $updates_result->fetch_assoc()) {
                $updates[] = $row;
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from html.kodesolution.com/2024/PickExpress-php/index-2-dark.php by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 10 Dec 2024 09:45:58 GMT -->
<!-- Added by HTTrack -->
<!-- Mirrored from webtechs.com.ng/PickExpress/tracking.php by HTTrack Website Copier/3.x [XR&CO'2014], Thu, 24 Apr 2025 23:11:48 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=UTF-8" /><!-- /Added by HTTrack -->
<head>
    <meta charset="utf-8">
    <title>PickExpress | Logistic & Transport </title>

    <!-- Stylesheets -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="css/slick.css" rel="stylesheet">
    <link href="css/slick-theme.css" rel="stylesheet">
    <link href="css/responsive.css" rel="stylesheet">
    <link href="css/style-dark.css" rel="stylesheet">

    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="icon" href="images/favicon.png" type="image/x-icon">

    <!-- Responsive -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <!--[if lt IE 9]><script src="js/html5shiv.js"></script><![endif]-->
    <!--[if lt IE 9]><script src="js/respond.js"></script><![endif]-->

    <style>
        ::-webkit-scrollbar-track {
            -webkit-box-shadow: none;
            background-color: transparent;
        }
        ::-webkit-scrollbar {
            width: 5px;
            background-color: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background-color: #EFD777;
            border: 0px solid #EFD777;
        }
    </style>
</head>

<body>
<div class="page-wrapper">

    <!-- Preloader -->
    <div class="preloader"></div>

    <!-- Main Header-->
    <header class="main-header header-style-one">
        <!-- Header Top -->
        <div class="header-top">
            <div class="inner-container">
                <div class="top-left">
                    <!-- Info List -->
                    <ul class="list-style-one">
                        <li><i class="fa fa-envelope"></i> <a href="mailto:support@pickexpress.vemochemicals.world">support@pickexpress.vemochemicals.world</a></li>
                        <li><i class="fa fa-map-marker"></i> Konstitucijos Av. 20, Vilnius, LT-09308, Lithuania</li>
                    </ul>
                </div>
                <div class="top-right">
                    <ul class="useful-links">
                        <li><a href="#">Help</a></li>
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                    <ul class="social-icon-one">
                        <li><a href="#"><span class="fab fa-twitter"></span></a></li>
                        <li><a href="#"><span class="fab fa-facebook-square"></span></a></li>
                        <li><a href="#"><span class="fab fa-pinterest-p"></span></a></li>
                        <li><a href="#"><span class="fab fa-instagram"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- Header Top -->
        
        <div class="header-lower">
            <!-- Main box -->
            <div class="main-box">
                <div class="logo-box">
                    <div class="logo"><a href="index.php"><img src="images/logo-2.png" width="200" alt=""></a></div>
                </div>
                <!--Nav Box-->
                <div class="nav-outer">    
                    <nav class="nav main-menu">
                        <ul class="navigation">
                            <li><a href="index.php">Home</a></li>
                            <li><a href="about.php">About</a></li>
                            <li><a href="services.php">Services</a></li>
                            <li><a href="contact.php">Contact Us</a></li>
                            <li><a href="track.php">Track&Trace</a></li>
                        </ul>          
                    </nav>
                </div>

                <div class="outer-box">
                    <a href="tel:+92(8800)9806" class="info-btn">
                        <i class="icon fa-light fa-phone-arrow-up-right"></i>
                        + 92 (8800) - 9850
                    </a>
                    <!-- Mobile Nav toggler -->
                    <div class="mobile-nav-toggler"><span class="icon lnr-icon-bars"></span></div>
                </div>
            </div>
        </div>

        <!-- Mobile Menu  -->
        <div class="mobile-menu">
            <div class="menu-backdrop"></div>
        
            <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
            <nav class="menu-box">
                <div class="upper-box">
                    <div class="nav-logo"><a href="index.php"><img src="images/logo-2.png" alt=""></a></div>
                    <div class="close-btn"><i class="icon fa fa-times"></i></div>
                </div>
        
                <ul class="navigation clearfix">
                    <!--Keep This Empty / Menu will come through Javascript-->
                </ul>
                <ul class="contact-list-one">
                    <li>
                        <!-- Contact Info Box -->
                        <div class="contact-info-box">
                            <i class="icon lnr-icon-phone-handset"></i>
                            <span class="title">Call Now</span>
                            <a href="tel:+92880098670">+92 (8800) - 98670</a>
                        </div>
                    </li>
                    <li>
                        <!-- Contact Info Box -->
                        <div class="contact-info-box">
                            <span class="icon lnr-icon-envelope1"></span>
                            <span class="title">Send Email</span>
                            <a href="mailto:support@pickexpress.vemochemicals.world">support@pickexpress.vemochemicals.world</a>
                        </div>
                    </li>
                    <li>
                        <!-- Contact Info Box -->
                        <div class="contact-info-box">
                            <span class="icon lnr-icon-clock"></span>
                            <span class="title">Send Email</span>
                            Mon - Sat 8:00 - 6:30, Sunday - CLOSED
                        </div>
                    </li>
                </ul>
        
                <ul class="social-links">
                    <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#"><i class="fab fa-pinterest"></i></a></li>
                    <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </nav>
        </div><!-- End Mobile Menu -->

        <!-- Header Search -->
        <div class="search-popup">
            <span class="search-back-drop"></span>
            <button class="close-search"><span class="fa fa-times"></span></button>
        </div>

        <!-- Sticky Header  -->
        <div class="sticky-header">
            <div class="auto-container">
                <div class="inner-container">
                    <!--Logo-->
                    <div class="logo">
                        <a href="index.php" title=""><img src="images/logo-2.png"></a>
                    </div>
            
                    <!--Right Col-->
                    <div class="nav-outer">
                        <!-- Main Menu -->
                        <nav class="main-menu">
                            <div class="navbar-collapse show collapse clearfix">
                                <ul class="navigation clearfix">
                                    <!--Keep This Empty / Menu will come through Javascript-->
                                </ul>
                            </div>
                        </nav><!-- Main Menu End-->
            
                        <!--Mobile Navigation Toggler-->
                        <div class="mobile-nav-toggler"><span class="icon lnr-icon-bars"></span></div>
                    </div>
                </div>
            </div>
        </div><!-- End Sticky Menu -->
    </header>
    <!-- Main Header End -->

    <!-- Start main-content -->
    <section class="page-title" style="background-image: url(images/background/page-title.jpg);">
        <div class="auto-container">
            <div class="title-outer text-center">
                <h1 class="title">Track&Trace</h1>
                <ul class="page-breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li>Track&Trace</li>
                </ul>
            </div>
        </div>
    </section>
    <!-- end main-content -->

    <br>
    <br>
    <br>
    <br>
    <br>

    <!-- Contact Section -->
    <section id="contact" class="contact-section-two pull-up pb-40">
        <div class="auto-container">
            <div class="row">
                <!-- Info Column -->
                <!-- <div class="info-column col-xl-7 col-lg-6 order-2">
                    <div class="inner-column wow fadeInRight">
                        <div class="sec-title">
                            <div class="sub-title">Get to know</div>
                            <h3>Keep your dream transport with us</h3>
                        </div>
                        <figure class="image overlay-anim d-none d-xl-block"><img src="images/resource/contact.jpg" alt=""></figure>
                    </div>
                </div> -->
                <div class="max-w-4xl mx-auto">
                    <div class="bg-gray-100 dark:bg-gray-700 p-8 rounded-lg">
                        <form action="" method="GET" class="space-y-6">
                            <div>
                                <label for="tracking_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tracking Number</label>
                                <input type="text" id="tracking_number" name="tracking_number" value="<?php echo htmlspecialchars($tracking_number); ?>"
                                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary" 
                                    placeholder="Enter your tracking number" required>
                            </div>
                            <button type="submit" class="w-full bg-primary hover:bg-blue-800 text-white px-8 py-3 rounded-lg">Track Now</button>
                        </form>
                    </div>

                    <?php if ($error): ?>
                    <!-- Error Message -->
                    <div class="mt-8">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($consignment): ?>
                    <!-- Tracking Result -->
                    <div class="mt-8">
                        <div class="tracking-container rounded-lg shadow-lg overflow-hidden">
                            <!-- Company Branding -->
                            <div class="bg-gradient-to-r from-primary to-blue-800 p-6 text-white">
                                <div class="flex flex-col items-center text-center">
                                    <?php if (!empty($company_settings['logo_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($company_settings['logo_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($company_settings['company_name']); ?>" 
                                         class="h-20 w-20 rounded-full mb-4">
                                    <?php endif; ?>
                                    <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?></h2>
                                    <div class="flex space-x-4 text-sm">
                                        <span><i class="fas fa-phone mr-1"></i> <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?></span>
                                        <span><i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Print Button -->
                            <div class="p-4 bg-gray-100 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600 no-print">
                                <button onclick="window.print()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-800 transition">
                                    <i class="fas fa-print mr-2"></i> Print Tracking Info
                                </button>
                            </div>

                            <!-- Status Banner -->
                            <div class="bg-primary text-white p-6">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h2 class="text-2xl font-bold">Tracking Number: <?php echo htmlspecialchars($consignment['tracking_number']); ?></h2>
                                        <p class="text-sm mt-1">Created on <?php echo date('M d, Y', strtotime($consignment['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm">Current Status</p>
                                        <p class="text-xl font-semibold mt-1"><?php echo htmlspecialchars($consignment['status']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Details Grid -->
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <!-- Sender Information -->
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Sender Information</h3>
                                        <div class="space-y-2">
                                            <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['sender_name']); ?></p>
                                            <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['sender_phone']); ?></p>
                                            <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['sender_address']); ?></p>
                                        </div>
                                    </div>

                                    <!-- Receiver Information -->
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Receiver Information</h3>
                                        <div class="space-y-2">
                                            <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($consignment['receiver_name']); ?></p>
                                            <p><span class="font-medium">Phone:</span> <?php echo htmlspecialchars($consignment['receiver_phone']); ?></p>
                                            <p><span class="font-medium">Address:</span> <?php echo htmlspecialchars($consignment['receiver_address']); ?></p>
                                        </div>
                                    </div>

                                    <!-- Payment Information -->
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Payment Information</h3>
                                        <div class="space-y-2">
                                            <p><span class="font-medium">Amount:</span> $<?php echo number_format($consignment['amount_paid'], 2); ?></p>
                                            <p><span class="font-medium">Payment Method:</span> <?php echo htmlspecialchars($consignment['payment_method'] ?? 'Not specified'); ?></p>
                                            <p><span class="font-medium">Payment Status:</span> 
                                                <span class="px-2 py-1 rounded-full text-sm <?php 
                                                    echo $consignment['payment_status'] === 'paid' ? 'bg-green-100 text-green-800' : 
                                                        ($consignment['payment_status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                                ?>">
                                                    <?php echo htmlspecialchars($consignment['payment_status'] ?? 'Not specified'); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Package Information -->
                                    <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Package Information</h3>
                                        <div class="space-y-2">
                                            <p><span class="font-medium">Description:</span> <?php echo htmlspecialchars($consignment['description']); ?></p>
                                            <p><span class="font-medium">Weight:</span> <?php echo htmlspecialchars($consignment['weight']); ?> kg</p>
                                            <p><span class="font-medium">Dimensions:</span> <?php echo htmlspecialchars($consignment['dimensions']); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking Timeline -->
                            <div class="mt-8">
                                <h2 class="text-2xl font-bold text-primary mb-6">Tracking History</h2>
                                <div class="bg-white rounded-lg shadow-lg p-6">
                                    <?php if (empty($updates)): ?>
                                        <div class="text-center py-8">
                                            <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                                            <p class="text-gray-500">No tracking updates available yet.</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="relative">
                                            <!-- Timeline line -->
                                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                            
                                            <?php foreach ($updates as $index => $update): ?>
                                                <div class="relative pl-12 pb-8">
                                                    <!-- Timeline dot -->
                                                    <div class="absolute left-0 w-8 h-8 rounded-full bg-white border-2 border-primary flex items-center justify-center">
                                                        <i class="fas fa-circle text-primary text-xs"></i>
                                                    </div>
                                                    
                                                    <!-- Status badge -->
                                                    <div class="mb-2">
                                                        <span class="px-3 py-1 rounded-full text-sm font-medium
                                                            <?php
                                                            switch($update['status']) {
                                                                case 'Delivered':
                                                                    echo 'bg-green-100 text-green-800';
                                                                    break;
                                                                case 'On Transit':
                                                                    echo 'bg-blue-100 text-blue-800';
                                                                    break;
                                                                case 'Out for Delivery':
                                                                    echo 'bg-yellow-100 text-yellow-800';
                                                                    break;
                                                                case 'On Hold':
                                                                    echo 'bg-red-100 text-red-800';
                                                                    break;
                                                                default:
                                                                    echo 'bg-gray-100 text-gray-800';
                                                            }
                                                            ?>">
                                                            <?php echo htmlspecialchars($update['status']); ?>
                                                        </span>
                                                    </div>
                                                    
                                                    <!-- Update content -->
                                                    <div class="bg-white rounded-lg shadow p-4">
                                                        <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($update['comment']); ?></p>
                                                        <div class="flex items-center text-sm text-gray-500">
                                                            <i class="fas fa-clock mr-2"></i>
                                                            <span><?php echo date('M d, Y H:i', strtotime($update['created_at'])); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12 no-print">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">About Us</h3>
                    <p class="text-gray-400">Your trusted partner in global logistics and shipping solutions.</p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                    <div class="footer-links">
                        <ul>
                            <li><a href="index.php" class="text-gray-400 hover:text-white">Home</a></li>
                            <li><a href="about.php" class="text-gray-400 hover:text-white">About</a></li>
                            <li><a href="services.php" class="text-gray-400 hover:text-white">Services</a></li>
                            <li><a href="contact.php" class="text-gray-400 hover:text-white">Contact</a></li>
                        </ul>
                    </div>
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
                            <?php echo htmlspecialchars($company_settings['address'] ?? '88 Broklyn Golden Street. New York'); ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-2"></i>
                            <?php echo htmlspecialchars($company_settings['phone'] ?? '+234 806 541 6156'); ?>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-2"></i>
                            <?php echo htmlspecialchars($company_settings['email'] ?? 'support@flexcee.com'); ?>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company_settings['company_name'] ?? 'FLEXCEE Logistics'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dark Mode Toggle
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