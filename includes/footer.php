    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="<?= SITE_URL ?>/" class="footer-brand">
                        <div class="navbar-brand-icon" style="width:32px;height:32px;font-size:14px;">CN</div>
                        CampusNexus
                    </a>
                    <p class="footer-desc">
                        The unified smart campus ecosystem connecting students, faculty, and administration 
                        under one powerful platform. Report issues, share resources, discover events, and more.
                    </p>
                </div>
                <div>
                    <h4 class="footer-title">Platform</h4>
                    <div class="footer-links">
                        <a href="<?= SITE_URL ?>/modules/grievances.php">Grievances</a>
                        <a href="<?= SITE_URL ?>/modules/marketplace.php">Marketplace</a>
                        <a href="<?= SITE_URL ?>/modules/events.php">Events</a>
                        <a href="<?= SITE_URL ?>/modules/resources.php">Resources</a>
                        <a href="<?= SITE_URL ?>/modules/lost_found.php">Lost & Found</a>
                    </div>
                </div>
                <div>
                    <h4 class="footer-title">Modules</h4>
                    <div class="footer-links">
                        <a href="<?= SITE_URL ?>/modules/attendance.php">Attendance</a>
                        <a href="<?= SITE_URL ?>/modules/mess.php">Mess Feedback</a>
                        <a href="<?= SITE_URL ?>/modules/announcements.php">Announcements</a>
                        <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a>
                    </div>
                </div>
                <div>
                    <h4 class="footer-title">Quick Links</h4>
                    <div class="footer-links">
                        <a href="<?= SITE_URL ?>/about.php">About</a>
                        <a href="<?= SITE_URL ?>/contact.php">Contact</a>
                        <a href="<?= SITE_URL ?>/login.php">Login</a>
                        <a href="<?= SITE_URL ?>/register.php">Register</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> CampusNexus. Built with ❤️ for Web Technologies (23CSE404)</p>
                <div class="footer-social">
                    <a href="#" data-tooltip="GitHub"><i class="fab fa-github"></i></a>
                    <a href="#" data-tooltip="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" data-tooltip="Twitter"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Core JavaScript -->
    <script src="<?= SITE_URL ?>/js/app.js"></script>
    
    <!-- Page-specific JS -->
    <?php if (isset($pageScripts) && is_array($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
        <script src="<?= SITE_URL ?>/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
