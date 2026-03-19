    </main>
    
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="logo mb-md">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-green">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span style="color: #FFFFFF;">GRAB & GO</span>
                    </div>
                    <p class="footer-description mb-lg">
                        Your trusted supermarket for fresh produce and quality groceries. 
                        Experience the best online shopping with fast delivery and premium service.
                    </p>
                    <div class="footer-social-links">
                        <a href="#" class="social-icon" title="Facebook">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                        </a>
                        <a href="#" class="social-icon" title="Twitter">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>
                        </a>
                        <a href="#" class="social-icon" title="Instagram">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                        </a>
                        <a href="#" class="social-icon" title="LinkedIn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                        </a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo BASE_URL; ?>products/listing.php">Our Shop</a></li>
                        <li><a href="#" onclick="showFooterToast('About Us page coming soon!'); return false;">About Us</a></li>
                        <li><a href="#" onclick="showFooterToast('Latest Insights blog coming soon!'); return false;">Latest Insights</a></li>
                        <li><a href="#" onclick="showFooterToast('Terms & Conditions page coming soon!'); return false;">Terms & Conditions</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Customer Care</h4>
                    <ul class="footer-links">
                        <li><a href="#" onclick="showFooterToast('Support Center coming soon!'); return false;">Contact Support</a></li>
                        <li><a href="<?php echo BASE_URL; ?>orders/my-orders.php">Track Orders</a></li>
                        <li><a href="#" onclick="showFooterToast('Shipping FAQ coming soon!'); return false;">Shipping FAQ</a></li>
                        <li><a href="#" onclick="showFooterToast('Privacy Policy coming soon!'); return false;">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="footer-section footer-newsletter">
                    <h4>Newsletter</h4>
                    <p class="footer-description mb-lg">Stay updated with our latest offers and grocery tips.</p>
                    <form class="flex gap-sm" id="newsletterForm" onsubmit="handleNewsletterSubmit(event)">
                        <input type="email" id="nlEmail" class="form-input" placeholder="Your email address" style="flex: 1;" required>
                        <button type="submit" class="btn btn-primary" id="nlSubmitBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            
            <script>
            function showFooterToast(msg, type = 'success') {
                let t = document.getElementById('footer-toast');
                if (!t) {
                    t = document.createElement('div');
                    t.id = 'footer-toast';
                    t.style.cssText = `
                        position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(60px);
                        background:#1a4d1e; color:#fff; padding:12px 24px; border-radius:30px;
                        font-size:0.85rem; font-weight:600; z-index:9999; opacity:0;
                        transition:all .3s cubic-bezier(.4,0,.2,1); white-space:nowrap;
                        box-shadow:0 6px 20px rgba(0,0,0,.25);
                    `;
                    document.body.appendChild(t);
                }
                if (type === 'error') t.style.background = '#dc2626';
                else if (type === 'success') t.style.background = '#1a4d1e';
                else if (type === 'info') t.style.background = '#18181b';
                
                t.textContent = msg;
                t.style.opacity = '1';
                t.style.transform = 'translateX(-50%) translateY(0)';
                clearTimeout(t._timer);
                t._timer = setTimeout(() => {
                    t.style.opacity = '0';
                    t.style.transform = 'translateX(-50%) translateY(60px)';
                }, 3000);
            }

            function handleNewsletterSubmit(e) {
                e.preventDefault();
                const btn = document.getElementById('nlSubmitBtn');
                const emailInput = document.getElementById('nlEmail');
                if(!emailInput.value) return;
                
                // Visual feedback
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>`;
                
                // Simulate an API call
                setTimeout(() => {
                    showFooterToast('🎉 Success! You are now subscribed to our newsletter.', 'success');
                    btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
                    emailInput.value = '';
                    
                    setTimeout(() => {
                        btn.innerHTML = origHtml;
                        btn.disabled = false;
                    }, 2000);
                }, 800);
            }
            </script>
            
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
