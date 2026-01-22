        </div> <!-- End content-container -->
    </main>
    
    <?php if (MENU_LAYOUT === 'sidebar'): ?>
    </div> <!-- End main-wrapper for sidebar layout -->
    <?php endif; ?>
    
    <!-- ================================================================ -->
    <!-- FOOTER -->
    <!-- ================================================================ -->
    <footer class="main-footer" style="background:#fff; border-top:1px solid #e2e8f0; padding:20px 0; margin-top:auto;">
        <div class="footer-container" style="max-width:1400px; margin:0 auto; padding:0 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap;">
            <div class="footer-left">
                <p style="color:#64748b; font-size:14px; margin:0;">
                    &copy; <?php echo date('Y'); ?> <strong><?php echo APP_NAME; ?></strong> 
                    <span style="color:#cbd5e1; margin:0 10px;">|</span>
                    <span style="font-size:13px;">Advanced Inventory & Billing System</span>
                </p>
            </div>
            
            <div class="footer-right">
                <a href="<?php echo BASE_URL; ?>help.php" style="color:#64748b; text-decoration:none; font-size:14px; margin-left:20px; transition:color 0.2s;">
                    📚 Documentation
                </a>
                <a href="mailto:<?php echo defined('DEV_EMAIL') ? DEV_EMAIL : 'support@example.com'; ?>" style="color:#64748b; text-decoration:none; font-size:14px; margin-left:20px; transition:color 0.2s;">
                    📨 Support
                </a>
                <span style="color:#cbd5e1; margin:0 10px 0 20px;">|</span>
                <span style="color:#94a3b8; font-size:13px;">v1.0.0</span>
            </div>
        </div>
    </footer>
    
    <!-- ================================================================ -->
    <!-- JAVASCRIPT -->
    <!-- ================================================================ -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <!-- DataTables & Plugins -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($.fn.DataTable) {
            $('.data-table').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    'pageLength',
                    { extend: 'colvis', text: 'Columns' },
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
                stateSave: true, 
                autoWidth: false,
                responsive: true,
                processing: true, // Show 'Processing...'
                scrollX: true,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search records...",
                    emptyTable: "No data available",
                    zeroRecords: "No matching records found",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });
        }
    });
    </script>
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
         * Mobile sidebar toggle
         */
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        }
    </script>
</body>
</html>
