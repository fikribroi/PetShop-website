<?php
/**
 * Footer Partials - Public Website (PawHouse)
 */
?>
</main>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="brand-logo" style="color: #FFFFFF; margin-bottom: 1rem;">
                    <span class="paw-icon" style="background: rgba(255,255,255,0.1); color: #FFF;">🐾</span>
                    <span>PawHouse</span>
                </div>
                <p>Pusat perawatan, perlengkapan kebutuhan, dan penitipan hewan peliharaan terbaik dengan standar higienis dan kasih sayang penuh.</p>
            </div>

            <div class="footer-col">
                <h4>Menu Navigasi</h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('public/index.php') ?>">Beranda</a></li>
                    <li><a href="<?= base_url('public/products.php') ?>">Katalog Produk</a></li>
                    <li><a href="<?= base_url('public/services.php') ?>">Layanan Penitipan</a></li>
                    <li><a href="<?= base_url('public/booking.php') ?>">Formulir Booking</a></li>
                    <li><a href="<?= base_url('public/booking-status.php') ?>">Cek Status Booking</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Layanan Unggulan</h4>
                <ul class="footer-links">
                    <li><a href="<?= base_url('public/services.php') ?>">Hotel & Penitipan Kucing</a></li>
                    <li><a href="<?= base_url('public/services.php') ?>">Hotel & Penitipan Anjing</a></li>
                    <li><a href="<?= base_url('public/services.php') ?>">Grooming Lengkap & Terapi</a></li>
                    <li><a href="<?= base_url('public/services.php') ?>">Perawatan Kutu & Jamur</a></li>
                    <li><a href="<?= base_url('public/services.php') ?>">Antar Jemput Hewan</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Kontak & Lokasi</h4>
                <p style="font-size: 0.9rem; color: #9CA3AF; margin-bottom: 0.5rem;">📍 Jl. Tongkol Malabero, Bengkulu</p>
                <p style="font-size: 0.9rem; color: #9CA3AF; margin-bottom: 0.5rem;">📞 WhatsApp: 0831-5303-2700</p>
                <p style="font-size: 0.9rem; color: #9CA3AF; margin-bottom: 1rem;">⏰ Buka: Setiap Hari (08.00 - 20.00 WIB)</p>
                <a href="<?= base_url('public/booking.php') ?>" class="btn btn-accent btn-sm">Reservasi Sekarang</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> PawHouse - Sistem Pet Shop & Penitipan Hewan. Dibuat untuk Proyek Tugas Basis Data.</p>
            <p>Dibangun dengan PHP Native & MySQL (PDO Prepared Statements)</p>
        </div>
    </div>
</footer>

<script src="<?= asset_url('js/main.js') ?>"></script>
</body>
</html>

