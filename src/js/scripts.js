document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Function to check if we're on mobile
    function isMobile() {
        return window.innerWidth <= 768;
    }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        if (isMobile()) {
            sidebar.classList.toggle('active');
            sidebarOverlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        } else {
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');
            sidebarToggle.classList.toggle('collapsed');
            toggleIcon.classList.toggle('bi-chevron-left');
            toggleIcon.classList.toggle('bi-chevron-right');
        }
    });
    
    // Close sidebar when clicking on overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        sidebarOverlay.style.display = 'none';
    });
    
    // Close sidebar when clicking on a menu item (on mobile)
    const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (isMobile() && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                sidebarOverlay.style.display = 'none';
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (!isMobile()) {
            sidebarOverlay.style.display = 'none';
            if (sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        } else {
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                content.classList.remove('expanded');
                sidebarToggle.classList.remove('collapsed');
                toggleIcon.classList.add('bi-chevron-left');
                toggleIcon.classList.remove('bi-chevron-right');
            }
        }
    });
    
    // Delete account checkbox functionality
    const confirmDeleteCheck = document.getElementById('confirmDeleteCheck');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const deleteConfirmPin = document.getElementById('deleteConfirmPin');
    
    if (confirmDeleteCheck && confirmDeleteBtn && deleteConfirmPin) {
        function checkDeleteForm() {
            confirmDeleteBtn.disabled = !(confirmDeleteCheck.checked && deleteConfirmPin.value.length === 4);
        }
        
        confirmDeleteCheck.addEventListener('change', checkDeleteForm);
        deleteConfirmPin.addEventListener('input', checkDeleteForm);
    }
    
    // Transaction history filter functionality
    const transactionTypeFilter = document.getElementById('transactionTypeFilter');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    
    if (transactionTypeFilter && startDate && endDate) {
        function filterTransactions() {
            // This would filter the transactions in a real implementation
            console.log('Filtering transactions:', {
                type: transactionTypeFilter.value,
                startDate: startDate.value,
                endDate: endDate.value
            });
        }
        
        transactionTypeFilter.addEventListener('change', filterTransactions);
        startDate.addEventListener('change', filterTransactions);
        endDate.addEventListener('change', filterTransactions);
    }
    
    // Show alert function
    window.showAlert = function(type, message, redirect = null) {
        // Check if SweetAlert2 is available
        if (typeof Swal === 'undefined') {
            // Dynamically load SweetAlert2 if not available
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
            script.onload = function() {
                showSweetAlert(type, message, redirect);
            };
            document.head.appendChild(script);
        } else {
            showSweetAlert(type, message, redirect);
        }
    };
    
    function showSweetAlert(type, message, redirect) {
        // Ensure Swal is defined before using it
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Success' : 'Error',
                text: message,
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                if (redirect) {
                    window.location.href = redirect;
                }
            });
        } else {
            console.error('SweetAlert2 is not loaded.');
        }
    }

    
});

document.addEventListener('DOMContentLoaded', function() {
    // Get filter elements
    const typeFilter = document.getElementById('transactionTypeFilter');
    const startDateFilter = document.getElementById('startDate');
    const endDateFilter = document.getElementById('endDate');
    
    // Add event listeners to filters
    if (typeFilter && startDateFilter && endDateFilter) {
        typeFilter.addEventListener('change', filterTransactions);
        startDateFilter.addEventListener('change', filterTransactions);
        endDateFilter.addEventListener('change', filterTransactions);
        
        // Clear filters button
        const clearFiltersBtn = document.getElementById('clearFilters');
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', clearFilters);
        }
    }
    
    // Function to filter transactions
    function filterTransactions() {
        const type = typeFilter.value;
        const startDate = startDateFilter.value ? new Date(startDateFilter.value) : null;
        const endDate = endDateFilter.value ? new Date(endDateFilter.value) : null;
        
        // Adjust end date to include the entire day
        if (endDate) {
            endDate.setHours(23, 59, 59, 999);
        }
        
        // Get all transaction rows
        const rows = document.querySelectorAll('.transaction-table tbody tr:not(#noTransactionsInitial)');
        
        // Counter for visible rows
        let visibleRows = 0;
        
        // Loop through each row and check if it matches the filters
        rows.forEach(row => {
            let showRow = true;
            
            // Get transaction type from the row
            const transactionTypeCell = row.querySelector('td:nth-child(2)');
            const transactionType = transactionTypeCell ? transactionTypeCell.textContent.trim().toLowerCase() : '';
            
            // Check transaction type filter
            if (type !== 'all') {
                if (type === 'deposit' && !transactionType.includes('deposit')) {
                    showRow = false;
                } else if (type === 'withdrawal' && !transactionType.includes('withdrawal')) {
                    showRow = false;
                } else if (type === 'transfer') {
                    if (!transactionType.includes('transfer')) {
                        showRow = false;
                    }
                }
            }
            
            // Get transaction date from the row
            const dateCell = row.querySelector('td:nth-child(1)');
            if (dateCell && (startDate || endDate)) {
                const dateText = dateCell.textContent.trim();
                const transactionDate = new Date(dateText);
                
                // Check date range filter
                if (startDate && transactionDate < startDate) {
                    showRow = false;
                }
                
                if (endDate && transactionDate > endDate) {
                    showRow = false;
                }
            }
            
            // Show or hide the row based on filters
            if (showRow) {
                row.style.display = '';
                visibleRows++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "No transactions found" message if no rows match the filters
        const noTransactionsMessage = document.getElementById('noTransactionsMessage');
        
        if (noTransactionsMessage) {
            if (visibleRows === 0 && rows.length > 0) {
                noTransactionsMessage.style.display = 'block';
            } else {
                noTransactionsMessage.style.display = 'none';
            }
        }
        
        // Update filter status text
        updateFilterStatus(type, startDate, endDate);
    }
    
    // Function to clear all filters
    function clearFilters() {
        typeFilter.value = 'all';
        startDateFilter.value = '';
        endDateFilter.value = '';
        filterTransactions();
    }
    
    // Function to update filter status text
    function updateFilterStatus(type, startDate, endDate) {
        const filterStatus = document.getElementById('filterStatus');
        if (!filterStatus) return;
        
        let statusText = 'Showing: ';
        
        // Add transaction type to status
        if (type === 'all') {
            statusText += 'All transactions';
        } else if (type === 'deposit') {
            statusText += 'Deposits only';
        } else if (type === 'withdrawal') {
            statusText += 'Withdrawals only';
        } else if (type === 'transfer') {
            statusText += 'Transfers only';
        }
        
        // Add date range to status if applicable
        if (startDate && endDate) {
            statusText += ` from ${startDate.toLocaleDateString()} to ${endDate.toLocaleDateString()}`;
        } else if (startDate) {
            statusText += ` from ${startDate.toLocaleDateString()}`;
        } else if (endDate) {
            statusText += ` until ${endDate.toLocaleDateString()}`;
        }
        
        filterStatus.textContent = statusText;
    }
    
    // Initialize the transaction history modal
    const transactionHistoryModal = document.getElementById('transactionHistoryModal');
    if (transactionHistoryModal) {
        transactionHistoryModal.addEventListener('shown.bs.modal', function() {
            // Reset filters when modal is shown
            clearFilters();
        });
    }});