<?php
$page_title = 'Contact Us - Taste Bud Hub';
include 'includes/header.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'config/database.php';
    
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);
    
    if (!empty($name) && !empty($email) && !empty($message)) {
        $conn = getConnection();
        $stmt = $conn->prepare("INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);
        
        if ($stmt->execute()) {
            $success_message = "Thank you for your message! We'll get back to you soon.";
        } else {
            $error_message = "Sorry, there was an error sending your message. Please try again.";
        }
        
        $conn->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<main class="contact-page">
    <div class="container">
        <!-- Contact Header -->
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>Have a question, suggestion, or just want to say hello? We'd love to hear from you!</p>
        </div>
        
        <div class="contact-layout">
            <!-- Contact Form -->
            <div class="contact-form-section">
                <h2>Send us a Message</h2>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form class="contact-form" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Tell us how we can help you..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="fas fa-paper-plane"></i>
                        Send Message
                    </button>
                </form>
            </div>
            
            <!-- Contact Information -->
            <div class="contact-info-section">
                <h2>Get in Touch</h2>
                
                <div class="contact-info">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h4>Our Location</h4>
                            <p>123 Food Street<br>Culinary District<br>Food City, FC 12345</p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h4>Phone</h4>
                            <p>(555) 123-4567</p>
                            <small>Mon-Sun: 8:00 AM - 10:00 PM</small>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h4>Email</h4>
                            <p>info@tastebudhub.com</p>
                            <small>We'll respond within 24 hours</small>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="info-content">
                            <h4>Operating Hours</h4>
                            <p>Monday - Sunday<br>8:00 AM - 10:00 PM</p>
                        </div>
                    </div>
                </div>
                
                <div class="social-links">
                    <h4>Follow Us</h4>
                    <div class="social-icons">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h4>What are your delivery areas?</h4>
                    <p>We currently deliver to Downtown, Uptown, Suburbs, and Business District areas. Check our coverage during checkout.</p>
                </div>
                
                <div class="faq-item">
                    <h4>How long does delivery take?</h4>
                    <p>Most orders are delivered within 25-35 minutes. You'll receive real-time updates on your order status.</p>
                </div>
                
                <div class="faq-item">
                    <h4>What payment methods do you accept?</h4>
                    <p>We accept all major credit cards, debit cards, and digital payment methods for your convenience.</p>
                </div>
                
                <div class="faq-item">
                    <h4>Can I schedule orders in advance?</h4>
                    <p>Yes! You can schedule orders up to 24 hours in advance. Perfect for parties or special occasions.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>