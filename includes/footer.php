    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo mb-md">
                        <div class="logo-icon"></div>
                        <span>GRAB & GO</span>
                    </div>
                    <p class="text-secondary">
                        Your trusted supermarket for fresh produce and quality groceries. 
                        Order online, pickup at your convenience.
                    </p>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="#">Help & Support</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Insights</a></li>
                        <li><a href="#">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Help & Support</h4>
                    <ul class="footer-links">
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">FAQs</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Newsletter</h4>
                    <p class="text-secondary mb-md">Subscribe to get special offers and updates.</p>
                    <form class="flex gap-sm">
                        <input type="email" class="form-input" placeholder="Your email" style="flex: 1;">
                        <button type="submit" class="btn btn-primary">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Grab & Go. All rights reserved. Designed by Savio Joze</p>
            </div>
        </div>
    </footer>
    
    <script src="<?php echo BASE_URL; ?>js/main.js?v=<?php echo time(); ?>"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo BASE_URL . $extra_js; ?>"></script>
    <?php endif; ?>
</body>
</html>
