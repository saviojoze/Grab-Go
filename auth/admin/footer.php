    </div><!-- .admin-layout -->
    
    <!-- Admin Footer -->
    <footer class="admin-footer">
        <p>&copy; <?php echo date('Y'); ?> Grab & Go. All rights reserved.</p>
    </footer>
    
    <!-- JavaScript -->
    <script src="../js/admin.js"></script>
    
    <script>
        // User dropdown toggle
        const userProfileBtn = document.getElementById('userProfileBtn');
        const userDropdownMenu = document.getElementById('userDropdownMenu');
        
        if (userProfileBtn) {
            userProfileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            });
            
            document.addEventListener('click', () => {
                userDropdownMenu.classList.remove('show');
            });
        }
    </script>
</body>
</html>
