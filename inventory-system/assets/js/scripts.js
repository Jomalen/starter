document.addEventListener('DOMContentLoaded', function() {
    // Sidebar functionality
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const toggleIcon = toggleBtn?.querySelector('i');

    function updateToggleState(isCollapsed) {
        if (!sidebar || !mainContent || !toggleIcon) return;
        
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            toggleIcon.classList.remove('fa-bars');
            toggleIcon.classList.add('fa-chevron-right');
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-bars');
        }
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    }

    // Handle window resize
    function handleResize() {
        if (!sidebar || !mainContent || !toggleIcon) return;

        if (window.innerWidth <= 768) {
            sidebar.classList.add('mobile-show');
            mainContent.classList.add('expanded');
            toggleIcon.classList.remove('fa-chevron-right');
            toggleIcon.classList.add('fa-bars');
        } else {
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            updateToggleState(isCollapsed);
        }
    }

    // Initialize dashboard cards animation and other features
    function initDashboardFeatures() {
        // Dashboard cards hover effect
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Quick actions hover effect
        const actionButtons = document.querySelectorAll('.quick-actions .btn');
        actionButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });

        // Table row hover effect
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseover', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseout', function() {
                this.style.backgroundColor = '';
            });
        });
    }

    // Toggle click handler
    if (toggleBtn && sidebar && mainContent) {
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('mobile-show');
                const isMobileShown = sidebar.classList.contains('mobile-show');
                toggleIcon.classList.toggle('fa-chevron-right', !isMobileShown);
                toggleIcon.classList.toggle('fa-bars', isMobileShown);
            } else {
                const isCollapsed = !sidebar.classList.contains('collapsed');
                updateToggleState(isCollapsed);
            }
        });

        // Initialize on page load
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        handleResize();
        
        // Handle window resize
        window.addEventListener('resize', handleResize);
    }

    // Search functionality
    const searchInput = document.querySelector('#searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const model = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                const serialNumber = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                const propertyNumber = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const brand = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                const category = row.querySelector('td:nth-child(5)')?.textContent.toLowerCase() || '';
                const location = row.querySelector('td:nth-child(6)')?.textContent.toLowerCase() || '';
                const endUser = row.querySelector('td:nth-child(7)')?.textContent.toLowerCase() || '';
                
                if (model.includes(searchTerm) || 
                    serialNumber.includes(searchTerm) || 
                    propertyNumber.includes(searchTerm) ||
                    brand.includes(searchTerm) ||
                    category.includes(searchTerm) ||
                    location.includes(searchTerm) ||
                    endUser.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Initialize all features
    initDashboardFeatures();

    // Initialize tooltips if Bootstrap is loaded
    if (typeof bootstrap !== 'undefined') {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
