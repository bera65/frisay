document.addEventListener('DOMContentLoaded', function() {
    // Sayfa yenilendiğinde formun tekrar gönderilmesini önler
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Mobil menü butonu tıklama olayı
    const mobileMenuBtn = document.getElementById('mobileMenuBtn'); // veya document.querySelector('#mobileMenuBtn')
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function() {
            // .sidebar ve .header sınıflarına sahip elementleri seçip 'active' sınıfını değiştirir
            document.querySelectorAll('.sidebar').forEach(el => el.classList.toggle('active'));
            document.querySelectorAll('.header').forEach(el => el.classList.toggle('active'));
            
            // Tıklanan butonun kendisine 'open' sınıfını ekler/çıkarır
            this.classList.toggle('open');
        });
    }
});
