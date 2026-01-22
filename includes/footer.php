        </div> <!-- End content-container -->
    </main>
    
    <!-- ================================================================ -->
    <!-- FOOTER -->
    <!-- ================================================================ -->
    <footer class="main-footer">
        <div class="footer-container">
            <p class="footer-text">
                &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> 
                <span class="footer-separator">|</span> 
                Version <?php echo APP_VERSION; ?>
                <span class="footer-separator">|</span>
                Developed with ❤️ using PHP & MySQL
            </p>
        </div>
    </footer>
    
    <!-- ================================================================ -->
    <!-- JAVASCRIPT -->
    <!-- ================================================================ -->
    <script>
        /**
         * Confirmation dialog for delete actions
         * 
         * @param {string} message - Confirmation message
         * @return {boolean} True if confirmed, false otherwise
         */
        function confirmDelete(message) {
            return confirm(message || 'Are you sure you want to delete this item?');
        }
        
        /**
         * Auto-hide alert messages after 5 seconds
         */
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                }, 5000);
            });
        });
        
        /**
         * Simple dropdown menu functionality
         */
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(function(dropdown) {
                dropdown.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.toggle('active');
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(function(dropdown) {
                        dropdown.classList.remove('active');
                    });
                }
            });
        });
    </script>
</body>
</html>
