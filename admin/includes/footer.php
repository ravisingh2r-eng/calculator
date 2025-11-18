        </main>
        <!-- End Main Content -->

    </div>
    <!-- End Layout Container -->

</div>
<!-- End Admin Wrapper -->

<!-- Admin JavaScript -->
<script>
(function() {
    'use strict';

    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar && overlay) {
        // Toggle sidebar
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Close sidebar on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        });
    }

    // Auto-hide flash messages
    const flashMessages = document.querySelectorAll('.admin-flash');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s ease';
            msg.style.opacity = '0';
            setTimeout(function() {
                msg.style.display = 'none';
            }, 500);
        }, 5000);
    });

})();
</script>

<?php if (isset($extraScripts)): ?>
<script><?php echo $extraScripts; ?></script>
<?php endif; ?>

</body>
</html>
