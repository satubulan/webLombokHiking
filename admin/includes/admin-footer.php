
</main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle sidebar toggle
            const layout = document.querySelector('.admin-layout');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            const toggleMenu = document.querySelector('.toggle-menu');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    layout.classList.toggle('collapsed');
                });
            }
            
            if (toggleMenu) {
                toggleMenu.addEventListener('click', function() {
                    layout.classList.toggle('mobile-open');
                });
            }
            
            // Close sidebar on mobile when clicking outside
            document.addEventListener('click', function(event) {
                if (layout.classList.contains('mobile-open') && 
                    !event.target.closest('.admin-sidebar') && 
                    !event.target.closest('.toggle-menu')) {
                    layout.classList.remove('mobile-open');
                }
            });
            
            // Handle active link based on current page
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-link');
            
            navLinks.forEach(link => {
                if (currentPath.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
            
            // Setup notification dropdown
            const notificationBtn = document.querySelector('.notification-button');
            
            if (notificationBtn) {
                notificationBtn.addEventListener('click', function() {
                    // Handle notification dropdown
                });
            }
            
            // Setup user menu dropdown
            const userMenu = document.querySelector('.user-menu');
            
            if (userMenu) {
                userMenu.addEventListener('click', function() {
                    // Handle user menu dropdown
                });
            }
        });
    </script>
</body>
</html>
