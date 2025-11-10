<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - MediVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1e293b;
            line-height: 1.6;
        }

        .container-fluid {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 50%, #60a5fa 100%);
            color: white;
            padding: 80px 0 60px;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,0 1000,0 1000,60 0,100"/></svg>') no-repeat bottom;
            background-size: cover;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 4px 8px rgba(0,0,0,0.2);
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            opacity: 0.95;
            margin-bottom: 2rem;
        }

        .hero-description {
            font-size: 1.1rem;
            max-width: 800px;
            margin: 0 auto;
            opacity: 0.9;
            font-weight: 300;
        }

        /* Mission Section */
        .mission-section {
            padding: 80px 0;
            background: white;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
            border-radius: 2px;
        }

        .mission-content {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .mission-text {
            font-size: 1.2rem;
            color: #475569;
            font-weight: 400;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        /* Features Section */
        .features-section {
            padding: 80px 0;
            background: #f8fafc;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(59, 130, 246, 0.1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #60a5fa);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 40px rgba(59, 130, 246, 0.15);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(96, 165, 250, 0.1));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
        }

        .feature-icon i {
            font-size: 2rem;
            color: #3b82f6;
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 15px;
        }

        .feature-description {
            color: #64748b;
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Team Section */
        .team-section {
            padding: 80px 0;
            background: white;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            margin-top: 50px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .team-member {
            text-align: center;
            padding: 30px;
            background: #f8fafc;
            border-radius: 20px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .team-member:hover {
            transform: translateY(-5px);
            border-color: rgba(59, 130, 246, 0.2);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .member-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .member-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .member-photo i {
            font-size: 3rem;
            color: #64748b;
        }

        .member-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 8px;
        }

        .member-role {
            font-size: 1rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .member-description {
            font-size: 0.9rem;
            color: #475569;
            line-height: 1.5;
        }

        /* Vision Section */
        .vision-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            text-align: center;
        }

        .vision-quote {
            font-size: 1.8rem;
            font-weight: 300;
            font-style: italic;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
            position: relative;
        }

        .vision-quote::before,
        .vision-quote::after {
            content: '"';
            font-size: 4rem;
            position: absolute;
            top: -20px;
            opacity: 0.3;
            font-family: serif;
        }

        .vision-quote::before {
            left: -30px;
        }

        .vision-quote::after {
            right: -30px;
        }

        /* Stats Section */
        .stats-section {
            padding: 60px 0;
            background: white;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }

        .stat-item {
            padding: 20px;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #3b82f6;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 1rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.2rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .team-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
            
            .vision-quote {
                font-size: 1.4rem;
            }
            
            .vision-quote::before,
            .vision-quote::after {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-pills me-3"></i>MediVault
                </h1>
                <p class="hero-subtitle">Smart Pharmacy Management System</p>
                <p class="hero-description">
                    Revolutionizing pharmacy operations with intelligent technology, streamlined workflows, and enhanced patient care through our comprehensive digital solution.
                </p>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number">in development</div>
                    <div class="stat-label">Pharmacies Served</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">99.9%</div>
                    <div class="stat-label">Uptime</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50M+</div>
                    <div class="stat-label">Prescriptions Processed</div>
                </div>

                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="container">
            <h2 class="section-title">Our Mission</h2>
            <div class="mission-content">
                <p class="mission-text">
                    We empower pharmacies with cutting-edge technology to enhance efficiency, accuracy, and patient care. Our platform simplifies complex pharmacy operations while ensuring compliance with industry standards.
                </p>
                <p class="mission-text">
                    From intelligent inventory management to automated billing and real-time analytics, MediVault transforms traditional pharmacy workflows into smart, data-driven processes.
                </p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose MediVault</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <h3 class="feature-title">Smart Inventory</h3>
                    <p class="feature-description">
                        Automated stock management with predictive analytics to prevent shortages and optimize inventory levels.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3 class="feature-title">Automated Billing</h3>
                    <p class="feature-description">
                        Streamlined prescription processing and billing system with integrated insurance claim management.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="feature-title">Real-time Analytics</h3>
                    <p class="feature-description">
                        Comprehensive reporting and insights to drive informed business decisions and track performance.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="feature-title">Secure & Compliant</h3>
                    <p class="feature-description">
                        Bank-grade security with full regulatory compliance and encrypted patient data protection.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="feature-title">User-Friendly Interface</h3>
                    <p class="feature-description">
                        Intuitive design that requires minimal training with responsive mobile and desktop access.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="feature-title">24/7 Support</h3>
                    <p class="feature-description">
                        Round-the-clock technical support with dedicated account management and training resources.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <h2 class="section-title">Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <!-- Replace with actual image: <img src="team-member-1.jpg" alt="Team Member"> -->
                        <img src="team-1.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Swaroop Lenka</h3>
                    <p class="member-role">Making of Frontend of the Website</p>
                    <p class="member-description">
                        Leading the vision and strategy for MediVault's growth and innovation in pharmacy technology.
                    </p>
                </div>
                
                <div class="team-member">
                    <div class="member-photo">
                        <!-- Replace with actual image: <img src="team-member-2.jpg" alt="Team Member"> -->
                        <img src="team-2.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Mihir Kulkarni</h3>
                    <p class="member-role">Making of Backend along with enhancing the website UI</p>
                    <p class="member-description">
                        Overseeing technical architecture and ensuring our platform meets the highest standards.
                    </p>
                </div>
                
                <div class="team-member">
                    <div class="member-photo">
                        <!-- Replace with actual image: <img src="team-member-3.jpg" alt="Team Member"> -->
                        <img src="team-3.jpg" alt="Team Member">
                    </div>
                    <h3 class="member-name">Aryan Kulkarni</h3>
                    <p class="member-role">Working on both Backend and Frontend of website</p>
                    <p class="member-description">
                        Building robust and scalable solutions that power thousands of pharmacy operations.
                    </p>
                </div>
                
                <div class="team-member">
                    <div class="member-photo">
                        <!-- Replace with actual image: <img src="team-member-4.jpg" alt="Team Member"> -->
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="member-name">Harsh Makde</h3>
                    <p class="member-role">UI/UX Designer</p>
                    <p class="member-description">
                        Creating intuitive and beautiful interfaces that enhance user experience and productivity.
                    </p>
                </div>
                
                <div class="team-member">
                    <div class="member-photo">
                        <!-- Replace with actual image: <img src="team-member-5.jpg" alt="Team Member"> -->
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="member-name">Madhur Nichal</h3>
                    <p class="member-role">Helping the team with Mysql Queries</p>
                    <p class="member-description">
                        Transforming complex data into actionable insights for better pharmacy management decisions.
                    </p>
                </div>
                
                
            </div>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="vision-section">
        <div class="container">
            <p class="vision-quote">
                We envision a future where every pharmacy operates with precision, efficiency, and excellence, powered by intelligent technology that puts patient care first.
            </p>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on scroll
            const stats = document.querySelectorAll('.stat-number');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const target = parseInt(entry.target.textContent);
                        animateCount(entry.target, target);
                    }
                });
            });
            
            stats.forEach(stat => observer.observe(stat));
        });
        
        function animateCount(element, target) {
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = formatNumber(target);
                    clearInterval(timer);
                } else {
                    element.textContent = formatNumber(Math.floor(current));
                }
            }, 30);
        }
        
        function formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(0) + 'M+';
            } else if (num >= 1000) {
                return (num / 1000).toFixed(0) + 'K+';
            } else if (num === 999) {
                return '99.9%';
            } else if (num === 247) {
                return '24/7';
            }
            return num.toString();
        }
    </script>
</body>
</html>