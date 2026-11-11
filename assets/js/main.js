/**
 * PawHouse - Main JavaScript
 * Interaktivitas Navigasi, Konfirmasi Hapus, dan UI Mobile
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Mobile Navigation Toggle (Public)
    const mobileToggle = document.querySelector('.mobile-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileToggle && navLinks) {
        mobileToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // 2. Admin Sidebar Toggle (Mobile)
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', () => {
            adminSidebar.classList.toggle('open');
        });
    }

    // 3. Konfirmasi Tombol Hapus (Admin CRUD)
    const deleteForms = document.querySelectorAll('form.form-delete');
    deleteForms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const itemName = form.dataset.name || 'data ini';
            const confirmed = confirm(`Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`);
            if (!confirmed) {
                e.preventDefault();
            }
        });
    });

    // 4. Auto dismiss alert messages after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

