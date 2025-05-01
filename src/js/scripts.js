document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    
    // Function to toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
        sidebarToggle.classList.toggle('collapsed');
        
        // Change icon direction
        if (sidebar.classList.contains('collapsed')) {
            toggleIcon.classList.remove('bi-chevron-left');
            toggleIcon.classList.add('bi-chevron-right');
        } else {
            toggleIcon.classList.remove('bi-chevron-right');
            toggleIcon.classList.add('bi-chevron-left');
        }
    }
    
    // Add click event to sidebar toggle button
    sidebarToggle.addEventListener('click', toggleSidebar);
    
    // Check screen size on load
    function checkScreenSize() {
        if (window.innerWidth < 992) {
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                content.classList.add('expanded');
                sidebarToggle.classList.add('collapsed');
                toggleIcon.classList.remove('bi-chevron-left');
                toggleIcon.classList.add('bi-chevron-right');
            }
        }
    }
    
    // Initial check
    checkScreenSize();
    
    // Handle window resize
    window.addEventListener('resize', checkScreenSize);
    
    // Mobile sidebar handling
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    
    // On mobile, collapse sidebar when a nav link is clicked
    if (window.innerWidth < 992) {
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (!sidebar.classList.contains('collapsed')) {
                    toggleSidebar();
                }
            });
        });
    }
});