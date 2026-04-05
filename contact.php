<?php
$pageTitle = 'Contact';
$pageScripts = ['validation.js'];
require_once 'includes/header.php';
initCSRFToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $name = sanitize($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        setFlashMessage('error', 'Please fill in all fields.');
    } elseif (!isValidEmail($email)) {
        setFlashMessage('error', 'Please enter a valid email address.');
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        setFlashMessage('success', 'Your message has been sent successfully!');
        header('Location: contact.php');
        exit;
    }
}
?>
<main class="main-content">
    <div class="container">
        <div class="section">
            <div class="section-header reveal">
                <h2>Get in <span class="text-gradient">Touch</span></h2>
                <p>Have questions, feedback, or suggestions? We'd love to hear from you.</p>
            </div>
            
            <div class="contact-grid">
                <!-- Contact Form -->
                <div class="card reveal-left">
                    <h3 style="margin-bottom:var(--space-lg);">Send us a message</h3>
                    <form method="POST" id="contactForm">
                        <?php echo csrf_field(); ?>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name" class="form-label">Name <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" placeholder="Your full name" required>
                                <div class="form-error" id="name-error"></div>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email <span class="required">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="you@email.com" required>
                                <div class="form-error" id="email-error"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" class="form-control" placeholder="What is this about?" required>
                            <div class="form-error" id="subject-error"></div>
                        </div>
                        <div class="form-group">
                            <label for="message" class="form-label">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" class="form-control" rows="6" placeholder="Write your message here..." required></textarea>
                            <div style="display:flex;justify-content:space-between;">
                                <div class="form-error" id="message-error"></div>
                                <span id="charCount" class="form-help">0/500</span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Info -->
                <div class="reveal-right">
                    <div class="contact-info-cards">
                        <div class="contact-info-card">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h4>Email Us</h4>
                                <p>support@campusnexus.com</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h4>Call Us</h4>
                                <p>+91 98765 43210</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h4>Visit Us</h4>
                                <p>University Campus, Main Building</p>
                            </div>
                        </div>
                        <div class="contact-info-card">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h4>Office Hours</h4>
                                <p>Mon-Fri: 9:00 AM - 5:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    new FormValidator('contactForm', {
        name: { required: true, minLength: 2, label: 'Name' },
        email: { required: true, email: true, label: 'Email' },
        subject: { required: true, minLength: 3, label: 'Subject' },
        message: { required: true, minLength: 10, maxLength: 500, label: 'Message' }
    });
    initCharCounter('message', 'charCount', 500);
});
</script>

<?php require_once 'includes/footer.php'; ?>
