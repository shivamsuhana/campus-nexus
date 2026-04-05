<?php
$pageTitle = 'About';
require_once 'includes/header.php';
?>
<main class="main-content">
    <div class="container">
        <!-- Hero -->
        <div class="about-hero reveal">
            <h1>About <span class="text-gradient">CampusNexus</span></h1>
            <p style="font-size:var(--text-xl);color:var(--text-secondary);max-width:700px;margin:var(--space-md) auto 0;">
                The unified smart campus ecosystem connecting students, faculty, and administration under one platform.
            </p>
        </div>

        <!-- Mission -->
        <section class="section">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-2xl);align-items:center;">
                <div class="reveal-left">
                    <h2>Our <span class="text-gradient">Mission</span></h2>
                    <p style="margin:var(--space-md) 0;">
                        Every campus has the same problems: broken infrastructure with no accountability, 
                        resources scattered across WhatsApp groups, events missed because nobody saw the notice, 
                        and a general disconnect between students and administration.
                    </p>
                    <p>
                        CampusNexus bridges this gap with 8 powerful modules that digitize every aspect 
                        of campus life — from reporting a broken fan to finding lost items, from sharing 
                        study notes to rating mess food.
                    </p>
                </div>
                <div class="reveal-right">
                    <div class="card" style="text-align:center;padding:var(--space-2xl);">
                        <div style="font-size:64px;margin-bottom:var(--space-md);">🏗️</div>
                        <h3>Built for Real Problems</h3>
                        <p style="margin-top:var(--space-sm);">Not a toy project — CampusNexus addresses genuine daily pain points faced by every college community.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tech Stack -->
        <section class="section">
            <div class="section-header reveal">
                <h2>Technology <span class="text-gradient">Stack</span></h2>
                <p>Built with modern, industry-standard technologies</p>
            </div>
            <div class="tech-stack reveal">
                <div class="tech-item"><i class="fab fa-html5" style="color:#E44D26;"></i><span>HTML5</span></div>
                <div class="tech-item"><i class="fab fa-css3-alt" style="color:#2965F1;"></i><span>CSS3</span></div>
                <div class="tech-item"><i class="fab fa-js-square" style="color:#F7DF1E;"></i><span>JavaScript</span></div>
                <div class="tech-item"><i class="fab fa-php" style="color:#777BB3;"></i><span>PHP 8</span></div>
                <div class="tech-item"><i class="fas fa-database" style="color:#4479A1;"></i><span>MySQL</span></div>
                <div class="tech-item"><i class="fas fa-chart-pie" style="color:#FF6384;"></i><span>Chart.js</span></div>
                <div class="tech-item"><i class="fab fa-font-awesome" style="color:#228AE6;"></i><span>Font Awesome</span></div>
                <div class="tech-item"><i class="fab fa-github" style="color:var(--text-primary);"></i><span>GitHub</span></div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="section">
            <div class="section-header reveal">
                <h2>Frequently Asked <span class="text-gradient">Questions</span></h2>
            </div>
            <div style="max-width:800px;margin:0 auto;" class="reveal">
                <div class="faq-item">
                    <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                        <span>What is CampusNexus?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>CampusNexus is a comprehensive campus management platform with 8 modules covering attendance, resources, grievances, marketplace, events, lost & found, mess feedback, and announcements.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                        <span>Who can use CampusNexus?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Three types of users: Students, Faculty, and Administrators. Each role has a customized dashboard and specific capabilities within each module.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                        <span>How do I report a campus issue?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Login to your account, navigate to the Grievances module, click "Report Issue", fill in the details with optional photo evidence, and submit. You can track the resolution status in real-time.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                        <span>Is my data secure?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yes. Passwords are encrypted using bcrypt hashing. All database queries use prepared statements to prevent SQL injection. Sessions are managed securely with proper cookie handling.</p>
                    </div>
                </div>
                <div class="faq-item">
                    <div class="faq-question" onclick="this.parentElement.classList.toggle('active')">
                        <span>What technologies power CampusNexus?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Frontend: HTML5, CSS3 (with Flexbox, Grid, Box Model, Floats, Positioning), Vanilla JavaScript. Backend: PHP 8 with PDO. Database: MySQL. Charts: Chart.js. Icons: Font Awesome.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Developer -->
        <section class="section">
            <div class="section-header reveal">
                <h2>Meet the <span class="text-gradient">Developer</span></h2>
            </div>
            <div class="card reveal" style="max-width:500px;margin:0 auto;text-align:center;padding:var(--space-2xl);">
                <div style="width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--primary-start),var(--primary-end));display:flex;align-items:center;justify-content:center;font-size:36px;color:#fff;margin:0 auto var(--space-lg);font-weight:800;">K</div>
                <h3>Krishna</h3>
                <p style="color:var(--primary);font-weight:600;margin:var(--space-xs) 0;">CSE Student</p>
                <p style="margin-top:var(--space-md);">
                    Web Technologies (23CSE404) Capstone Project. Built with passion for 
                    creating technology solutions that solve real campus problems.
                </p>
                <div style="display:flex;gap:var(--space-md);justify-content:center;margin-top:var(--space-lg);">
                    <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-github"></i> GitHub</a>
                    <a href="#" class="btn btn-outline btn-sm"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                </div>
            </div>
        </section>
    </div>
</main>
<?php require_once 'includes/footer.php'; ?>
