<?php
// Public Homepage
// HealthyLife Hospital Management System
require_once __DIR__ . '/config/constants.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo HOSPITAL_NAME; ?> - Premium healthcare services in Colombo. World-class medical expertise with luxury patient experiences.">
    <title><?php echo HOSPITAL_NAME; ?> - World-Class Premium Healthcare</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/index.css">
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container nav-container">
            <a href="<?php echo APP_URL; ?>" class="nav-brand">
                <i class="fas fa-hospital-symbol"></i>
                <div class="nav-brand-text">
                    <span class="brand-name">HealthyLife</span>
                    <span>PREMIUM CARE</span>
                </div>
            </a>
            
            <div class="mobile-toggle" id="mobile-toggle">
                <i class="fas fa-bars"></i>
            </div>
            
            <ul class="nav-menu" id="nav-menu">
                <li><a href="#home" class="nav-link">Home</a></li>
                <li><a href="#specialists" class="nav-link">Specialists</a></li>
                <li><a href="#services" class="nav-link">Luxury Services</a></li>
                <li><a href="#experience" class="nav-link">Experience</a></li>
                <li><a href="#testimonials" class="nav-link"><i class="fas fa-quote-left"></i> Testimonials</a></li>
                <li><a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-navy">
                    <i class="fas fa-user-circle"></i> Login
                </a></li>
                <li><a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary">
                    Book Consultation
                </a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <div class="hero-badges">
                    <span class="trust-badge"><i class="fas fa-check-circle"></i> JCI Accredited</span>
                    <span class="trust-badge"><i class="fas fa-award"></i> No.1 in Patient Care</span>
                </div>
                
                <h2 class="hero-subtitle">Welcome to the future of healing</h2>
                <h1 class="hero-title">World-Class Care, Personalized for You.</h1>
                <p class="hero-description">Experience exceptional medical expertise combined with luxury patient care. From consultation to recovery, your journey is our priority.</p>
                
                <div class="hero-actions">
                    <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Book Your Consultation
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Booking Bar -->
        <div class="booking-bar">
            <div class="booking-field">
                <label for="dept-select">Department</label>
                <select class="booking-select" id="dept-select" name="department">
                    <option value="">Select Specialty</option>
                    <option value="cardiology">Cardiology</option>
                    <option value="neurology">Neurology</option>
                    <option value="orthopedics">Orthopedics</option>
                    <option value="pediatrics">Pediatrics</option>
                </select>
            </div>
            <div class="booking-field">
                <label for="doctor-select">Doctor</label>
                <select class="booking-select" id="doctor-select" name="doctor">
                    <option value="">Choose Specialist</option>
                    <option value="dr-johnson">Dr. Sarah Johnson (Cardio)</option>
                    <option value="dr-ali">Dr. Ahmed Ali (Neuro)</option>
                </select>
            </div>
            <div class="booking-field">
                <label for="booking-date">Date</label>
                <input type="date" class="booking-select" id="booking-date" name="date">
            </div>
            <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary" style="width: auto;">
                Check Availability
            </a>
        </div>
    </header>

    <!-- Specialists Section -->
    <section class="section-padding doctors-section" id="specialists">
        <div class="container">
            <div class="text-center">
                <span class="section-subtitle">World-Class Experts</span>
                <h2 class="section-title">Meet Our Specialists</h2>
                <p style="max-width: 600px; margin: 0 auto;">Our team of board-certified consultants brings global expertise to provide you with the best medical care.</p>
            </div>
            
            <div class="doctors-grid">
                <!-- Doctor 1 -->
                <div class="doctor-card">
                    <img src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Dr. Sarah Johnson" class="doctor-image">
                    <div class="doctor-info">
                        <h3 class="doctor-name">Dr. Sarah Johnson</h3>
                        <p class="doctor-specialty">Interventional Cardiologist</p>
                        <p class="doctor-credentials">MD, FACC (Harvard Medical School)</p>
                        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-navy" style="width: 100%">Book Appointment</a>
                    </div>
                </div>

                <!-- Doctor 2 -->
                <div class="doctor-card">
                    <img src="https://images.unsplash.com/photo-1537368910025-700350fe46c7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Dr. Ahmed Ali" class="doctor-image">
                    <div class="doctor-info">
                        <h3 class="doctor-name">Dr. Ahmed Ali</h3>
                        <p class="doctor-specialty">Senior Neurologist</p>
                        <p class="doctor-credentials">MBBS, FRCP (London)</p>
                        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-navy" style="width: 100%">Book Appointment</a>
                    </div>
                </div>

                <!-- Doctor 3 -->
                <div class="doctor-card">
                    <img src="https://images.unsplash.com/photo-1594824476967-48c8b964273f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Dr. Emily Chen" class="doctor-image">
                    <div class="doctor-info">
                        <h3 class="doctor-name">Dr. Emily Chen</h3>
                        <p class="doctor-specialty">Orthopedic Surgeon</p>
                        <p class="doctor-credentials">MS, FACS (Johns Hopkins)</p>
                        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-outline-navy" style="width: 100%">Book Appointment</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Workflow Section -->
    <section class="section-padding workflow-section" id="experience">
        <div class="container">
            <div class="text-center">
                <span class="section-subtitle">Seamless Journey</span>
                <h2 class="section-title">Your Path to Wellness</h2>
            </div>
            
            <div class="steps-container">
                <div class="step-item">
                    <div class="step-icon"><i class="fas fa-user-check"></i></div>
                    <h3 class="step-title">1. Choose Specialist</h3>
                    <p>Select from our roster of world-class experts.</p>
                </div>
                <div class="step-item">
                    <div class="step-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3 class="step-title">2. Book Slot</h3>
                    <p>Pick a time that works for your schedule.</p>
                </div>
                <div class="step-item">
                    <div class="step-icon"><i class="fas fa-clipboard-check"></i></div>
                    <h3 class="step-title">3. Confirmation</h3>
                    <p>Receive instant SMS & email confirmation.</p>
                </div>
                <div class="step-item">
                    <div class="step-icon"><i class="fas fa-heartbeat"></i></div>
                    <h3 class="step-title">4. Premium Care</h3>
                    <p>Experience personalized medical attention.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Services -->
    <section class="section-padding luxury-section" id="services">
        <div class="container">
            <div class="luxury-grid">
                <div class="luxury-content">
                    <span class="section-subtitle" style="color: var(--color-gold);">Premium Experience</span>
                    <h2 class="display-2">Healthcare Redefined</h2>
                    <p>We believe healing happens best in an environment of comfort and tranquility. Our facility combines state-of-the-art medical technology with the amenities of a 5-star hotel.</p>
                    
                    <div class="luxury-features">
                        <div class="luxury-feature">
                            <i class="fas fa-concierge-bell"></i>
                            <div>
                                <h4>Concierge Services</h4>
                                <p>Personalized care coordinators for your entire stay.</p>
                            </div>
                        </div>
                        <div class="luxury-feature">
                            <i class="fas fa-bed"></i>
                            <div>
                                <h4>Private Suites</h4>
                                <p>Spacious recovery rooms with premium amenities.</p>
                            </div>
                        </div>
                        <div class="luxury-feature">
                            <i class="fas fa-globe"></i>
                            <div>
                                <h4>Intl. Support</h4>
                                <p>Multilingual staff and medical tourism assistance.</p>
                            </div>
                        </div>
                        <div class="luxury-feature">
                            <i class="fas fa-utensils"></i>
                            <div>
                                <h4>Gourmet Nutrition</h4>
                                <p>Personalized meal plans by expert nutritionists.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="luxury-image">
                    <img src="https://images.unsplash.com/photo-1519494080410-f9aa76cb4283?ixlib=rb-4.0.3&auto=format&fit=crop&w=1740&q=80" alt="Luxury Hospital Ward" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-hover);">
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="section-padding testimonials-section" id="testimonials">
        <div class="container">
            <div class="text-center" style="margin-bottom: 3rem;">
                <span class="section-subtitle">Testimonials</span>
                <h2 class="section-title">Patient Stories</h2>
            </div>
            
            <div class="doctors-grid">
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"The level of care and attention I received was unparalleled. It felt more like a luxury hotel than a hospital. Dr. Johnson is amazing!"</p>
                    <p class="testimonial-author">- Sarah M., Dubai</p>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Outstanding facilities and world-class doctors. From the moment I walked in, I felt confident I was in good hands."</p>
                    <p class="testimonial-author">- James R., London</p>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-stars">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="testimonial-text">"Efficient, professional, and compassionate. The booking process was seamless, and the staff went above and beyond."</p>
                    <p class="testimonial-author">- Aisha K., Qatar</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Mobile Sticky CTA -->
    <div class="mobile-cta">
        <a href="<?php echo APP_URL; ?>/auth/login.php" class="btn btn-primary">Book Appointment</a>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="nav-brand">
                        <i class="fas fa-hospital-symbol"></i>
                        <div class="nav-brand-text">
                            <span class="brand-name" style="color: var(--color-white); font-size: 1.5rem; font-weight: 700; display: block; line-height: 1.2; font-family: var(--font-heading);">HealthyLife</span>
                            <span style="color: rgba(255,255,255,0.7);">PREMIUM CARE</span>
                        </div>
                    </div>
                    <p>Leading the way in medical excellence and patient-focused care since 1990. Accredited by JCI for global standards in healthcare.</p>
                </div>
                
                <div>
                    <h3 class="footer-heading">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#home">Home</a></li>
                        <li><a href="#specialists">Our Specialists</a></li>
                        <li><a href="#services">Services</a></li>
                        <li><a href="<?php echo APP_URL; ?>/auth/login.php">Patient Portal</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-heading">Services</h3>
                    <ul class="footer-links">
                        <li><a href="#specialists">Cardiology</a></li>
                        <li><a href="#specialists">Neurology</a></li>
                        <li><a href="#specialists">Orthopedics</a></li>
                        <li><a href="#home">Emergency Care</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="footer-heading">Contact</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-map-marker-alt" style="width: 20px;"></i> 123 Galle Road, Colombo 03</li>
                        <li><i class="fas fa-phone" style="width: 20px;"></i> +94 11 234 5678</li>
                        <li><i class="fas fa-envelope" style="width: 20px;"></i> info@healthylife.lk</li>
                        <li><i class="fas fa-clock" style="width: 20px;"></i> 24/7 Emergency Service</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo HOSPITAL_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // DOM Elements
        const navbar = document.getElementById('navbar');
        const mobileToggle = document.getElementById('mobile-toggle');
        const navMenu = document.getElementById('nav-menu');
        const bookingBar = document.querySelector('.booking-bar');
        
        // Mobile Menu Toggle
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                navMenu.classList.toggle('active');
            });
        }
        
        // Close menu when clicking a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
            });
        });
        
        // Navbar & Booking Bar Scroll Effects
        window.addEventListener('scroll', () => {
            // Navbar effect
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
            
            // Booking bar subtle fade
            if (bookingBar) {
                if (window.scrollY > 200) {
                    bookingBar.style.opacity = '0.7';
                    bookingBar.style.transform = 'translateX(-50%) translateY(10px)';
                    bookingBar.style.pointerEvents = 'none';
                } else {
                    bookingBar.style.opacity = '1';
                    bookingBar.style.transform = 'translateX(-50%) translateY(0)';
                    bookingBar.style.pointerEvents = 'all';
                }
            }
        });
    </script>
</body>
</html>
