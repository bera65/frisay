    </main>

    <!-- Minimal Footer -->
    <footer class="site-footer">
        <div class="container-premium">
            <div class="row g-5">
                <div class="col-12 col-lg-4">
                    <a href="#" class="footer-logo">FriSay<span class="text-muted">.</span></a>
                    <p class="text-muted mb-4" style="font-size: 0.95rem; max-width: 300px;">
                        Tasarım ve kalitenin buluştuğu premium alışveriş deneyimi. İlham veren stiller için bizi takip edin.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-muted"><svg class="icon"><use href="#icon-user"></use></svg></a> <!-- Sosyal Medya İkonu Placeholder -->
                        <a href="#" class="text-muted"><svg class="icon"><use href="#icon-heart"></use></svg></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Mağaza</h5>
                    <ul class="footer-nav">
                        <li><a href="#">Kadın Giyim</a></li>
                        <li><a href="#">Erkek Giyim</a></li>
                        <li><a href="#">Ayakkabı</a></li>
                        <li><a href="#">Aksesuarlar</a></li>
                        <li><a href="#">Kozmetik</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h5 class="footer-title">Kurumsal</h5>
                    <ul class="footer-nav">
                        <li><a href="#">Hakkımızda</a></li>
                        <li><a href="#">Kariyer</a></li>
                        <li><a href="#">Sürdürülebilirlik</a></li>
                        <li><a href="#">İletişim</a></li>
                        <li><a href="#">Mağazalarımız</a></li>
                    </ul>
                </div>
                <div class="col-12 col-lg-4">
                    <h5 class="footer-title">E-Bülten</h5>
                    <p class="text-muted" style="font-size: 0.9rem;">Özel indirimler ve yeni koleksiyonlardan ilk siz haberdar olun.</p>
                    <form onsubmit="event.preventDefault();">
                        <input type="email" class="newsletter-input" placeholder="E-posta adresiniz" required>
                        <button class="btn-premium-solid w-100 justify-content-center" style="padding: 12px">Abone Ol</button>
                    </form>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; 2026 FriSay Nova Premium. Tüm hakları saklıdır.
                </div>
                <div class="d-flex gap-4">
                    <a href="#" class="copyright text-decoration-underline">Gizlilik Politikası</a>
                    <a href="#" class="copyright text-decoration-underline">Kullanım Şartları</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobil Bottom Nav (Gelişmiş Tasarım) -->
    <nav class="mobile-bottom-bar">
        <a href="#" class="m-nav-item active">
            <svg class="icon"><use href="#icon-home"></use></svg>
            <span>Ana Sayfa</span>
        </a>
        <a href="#" class="m-nav-item" data-bs-toggle="offcanvas" data-bs-target="#searchMenu">
            <svg class="icon"><use href="#icon-search"></use></svg>
            <span>Keşfet</span>
        </a>
        <a href="#" class="m-nav-item position-relative">
            <svg class="icon"><use href="#icon-cart"></use></svg>
            <span>Sepet</span>
            <span class="cart-badge" style="top: -4px; right: -2px;">0</span>
        </a>
        <a href="#" class="m-nav-item">
            <svg class="icon"><use href="#icon-heart"></use></svg>
            <span>Favoriler</span>
        </a>
        <a href="#" class="m-nav-item">
            <svg class="icon"><use href="#icon-user"></use></svg>
            <span>Profil</span>
        </a>
    </nav>

    <!-- Offcanvas Mobil Menü -->
    <div class="offcanvas offcanvas-start offcanvas-premium" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header p-4">
            <h5 class="brand-logo mb-0">FriSay<span>.</span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body p-4">
            <ul class="footer-nav" style="font-size: 1.2rem; font-weight: 500;">
                <li class="mb-4"><a href="#" class="text-danger">Yeni Sezon</a></li>
                <li class="mb-4"><a href="#">Kadın</a></li>
                <li class="mb-4"><a href="#">Erkek</a></li>
                <li class="mb-4"><a href="#">Ayakkabı & Çanta</a></li>
                <li class="mb-4"><a href="#">Kozmetik</a></li>
                <li class="mb-4 mt-5 pt-4 border-top" style="border-color: var(--p-border-light) !important"><a href="#">Hesabım</a></li>
                <li class="mb-4"><a href="#">Siparişlerim</a></li>
                <li><button class="btn p-0 text-muted" id="themeToggleMobile">Temayı Değiştir</button></li>
            </ul>
        </div>
    </div>

    <!-- Offcanvas Arama -->
    <div class="offcanvas offcanvas-top offcanvas-premium" tabindex="-1" id="searchMenu" style="height: auto; max-height: 50vh;">
        <div class="offcanvas-header container-premium pb-0">
            <h5 class="d-none">Arama</h5>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
        </div>
        <div class="offcanvas-body container-premium pt-2 pb-5">
            <form action="#" class="position-relative">
                <svg class="icon position-absolute text-muted" style="left: 0; top: 50%; transform: translateY(-50%); width: 28px; height: 28px;"><use href="#icon-search"></use></svg>
                <input type="text" class="w-100 border-0 border-bottom bg-transparent" placeholder="Marka, ürün veya kategori ara..." 
                       style="font-size: 1.5rem; padding: 15px 15px 15px 45px; color: var(--p-text); outline: none; border-color: var(--p-border) !important;">
            </form>
            <div class="mt-4 text-muted" style="font-size: 0.9rem;">
                Popüler aramalar: <a href="#" class="text-decoration-underline ms-2">Sneaker</a>, <a href="#" class="text-decoration-underline ms-2">Kışlık Mont</a>, <a href="#" class="text-decoration-underline ms-2">Parfüm</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap & Interactions Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            // 1. Sticky Header Scroll Effect
            const header = document.getElementById('header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 20) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // 2. Premium Dark Mode Logic
            const themeBtns = [document.getElementById('themeToggleBtn'), document.getElementById('themeToggleMobile')];
            const themeIcon = document.getElementById('themeIcon');
            const htmlEl = document.documentElement;
            
            // Check LocalStorage
            const savedTheme = localStorage.getItem('premium-theme');
            if(savedTheme) {
                htmlEl.setAttribute('data-theme', savedTheme);
                updateIcon(savedTheme);
            }

            themeBtns.forEach(btn => {
                if(!btn) return;
                btn.addEventListener('click', () => {
                    const currentTheme = htmlEl.getAttribute('data-theme');
                    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
                    htmlEl.setAttribute('data-theme', newTheme);
                    localStorage.setItem('premium-theme', newTheme);
                    updateIcon(newTheme);
                });
            });

            function updateIcon(theme) {
                if(themeIcon) {
                    themeIcon.innerHTML = theme === 'dark' ? '<use href="#icon-sun"></use>' : '<use href="#icon-moon"></use>';
                }
            }
        });

        // 3. Elegant "Add to Cart" Feedback
        let cartTotal = 0;
        function animateCart(btn) {
            const origText = btn.innerText;
            
            // Micro-interaction
            btn.innerText = 'Eklendi ✓';
            btn.style.backgroundColor = 'var(--p-success)';
            btn.style.color = '#fff';
            
            cartTotal++;
            
            // Update Badges
            document.querySelectorAll('.cart-badge').forEach(badge => {
                badge.innerText = cartTotal;
                // Pop animation
                badge.style.transform = 'scale(1.5)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            });

            // Revert Button
            setTimeout(() => {
                btn.innerText = origText;
                btn.style.backgroundColor = '';
                btn.style.color = '';
            }, 2000);
        }
    </script>
</body>
</html>