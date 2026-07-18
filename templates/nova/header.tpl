<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FriSay Nova Premium - High-End E-Commerce Experience">
    <title>FriSay Nova | Premium E-Ticaret Deneyimi</title>

    <!-- Bootstrap 5 CSS (Sadece Grid ve Core yapı için) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* --- Premium Tema Değişkenleri --- */
        :root {
            /* Renk Paleti - Minimal ve Sofistike */
            --p-accent: #000000; /* Siyah ana renk (modern marka) */
            --p-accent-hover: #333333;
            --p-bg: #FFFFFF;
            --p-bg-alt: #F8FAFC;
            --p-surface: #F1F5F9; /* Kart arka planları */
            --p-text: #0F172A;
            --p-text-muted: #64748B;
            --p-border: #E2E8F0;
            --p-border-light: #F1F5F9;
            --p-success: #10B981;
            --p-danger: #EF4444;

            /* Tasarım Jetonları */
            --p-radius-sm: 6px;
            --p-radius: 16px;
            --p-radius-lg: 24px;
            --p-radius-pill: 9999px;
            
            /* İnce Gölgeler (Apple/Stripe tarzı) */
            --p-shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
            --p-shadow: 0 10px 30px -10px rgba(0,0,0,0.08);
            --p-shadow-floating: 0 20px 40px -15px rgba(0,0,0,0.12);
            
            /* Tipografi */
            /* Modern, sans-serif, okunaklı */
            --p-font: "Inter", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            
            /* Konteyner & Layout */
            --p-container-width: 1400px; /* Daha geniş, ferah */
            --p-transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); /* Pürüzsüz ease-out */
        }

        /* --- Dark Mode --- */
        [data-theme="dark"] {
            --p-accent: #FFFFFF;
            --p-accent-hover: #E2E8F0;
            --p-bg: #0A0A0A; /* Gerçek derin siyah */
            --p-bg-alt: #121212;
            --p-surface: #1A1A1A;
            --p-text: #F8FAFC;
            --p-text-muted: #94A3B8;
            --p-border: #27272A;
            --p-border-light: #1A1A1A;
            --p-shadow: 0 10px 30px -10px rgba(0,0,0,0.5);
        }

        /* --- Global Reset & Base --- */
        * { box-sizing: border-box; }
        body {
            font-family: var(--p-font);
            background-color: var(--p-bg);
            color: var(--p-text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            transition: background-color 0.4s ease, color 0.4s ease;
            padding-bottom: 80px; /* Mobil nav için */
        }
        @media (min-width: 992px) { body { padding-bottom: 0; } }
        
        a { text-decoration: none; color: inherit; transition: var(--p-transition); }
        a:hover { color: var(--p-text); opacity: 0.7; }
        ul { list-style: none; padding: 0; margin: 0; }
        button { font-family: inherit; cursor: pointer; }

        .container-premium {
            max-width: var(--p-container-width);
            margin: 0 auto;
            padding: 0 24px;
        }

        /* İkonlar */
        .icon {
            width: 22px;
            height: 22px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: var(--p-transition);
        }

        /* --- Announcement Bar --- */
        .top-bar {
            background-color: var(--p-accent);
            color: var(--p-bg);
            text-align: center;
            padding: 8px 0;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        /* --- Minimalist Header --- */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            background-color: rgba(var(--p-bg-rgb), 0.8);
            background: var(--p-bg);
            border-bottom: 1px solid var(--p-border-light);
            transition: var(--p-transition);
            padding: 24px 0;
        }
        .site-header.scrolled {
            padding: 16px 0;
            box-shadow: var(--p-shadow-sm);
            background-color: rgba(255,255,255,0.95); /* Light mode glass */
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        [data-theme="dark"] .site-header.scrolled {
            background-color: rgba(10,10,10,0.85); /* Dark mode glass */
        }

        .header-inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
        }

        .brand-logo {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -1px;
            color: var(--p-text) !important;
            opacity: 1 !important;
        }
        .brand-logo span { color: var(--p-text-muted); font-weight: 400;}

        /* Desktop Nav (Center) */
        .desktop-nav {
            display: flex;
            gap: 32px;
            justify-content: center;
        }
        .nav-link-premium {
            font-weight: 500;
            color: var(--p-text);
            position: relative;
            padding-bottom: 4px;
        }
        .nav-link-premium::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--p-accent);
            transition: var(--p-transition);
        }
        .nav-link-premium:hover::after { width: 100%; }

        /* Header Actions (Right) */
        .header-actions {
            display: flex;
            justify-content: flex-end;
            gap: 20px;
            align-items: center;
        }
        .action-btn {
            background: none;
            border: none;
            color: var(--p-text);
            position: relative;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-btn:hover { color: var(--p-text-muted); }
        .cart-badge {
            position: absolute;
            top: -2px;
            right: -6px;
            background-color: var(--p-danger);
            color: white;
            font-size: 0.65rem;
            font-weight: 700;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--p-bg);
        }

        /* --- Mega Menu (Hover ile açılan modern yapı) --- */
        .has-mega-menu { position: static !important; }
        .mega-menu-wrapper {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: var(--p-bg);
            border-top: 1px solid var(--p-border);
            border-bottom: 1px solid var(--p-border);
            box-shadow: var(--p-shadow);
            padding: 40px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: var(--p-transition);
            pointer-events: none;
        }
        .has-mega-menu:hover .mega-menu-wrapper {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }
        .mega-menu-list h6 {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            color: var(--p-text-muted);
            margin-bottom: 16px;
        }
        .mega-menu-list a {
            display: block;
            padding: 8px 0;
            color: var(--p-text);
            font-size: 0.95rem;
            font-weight: 400;
        }
        .mega-menu-list a:hover { transform: translateX(5px); }

        /* --- Editorial Hero Section --- */
        .hero-editorial {
            padding: 40px 0 80px;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 40px;
            align-items: center;
            background-color: var(--p-surface);
            border-radius: var(--p-radius-lg);
            overflow: hidden;
        }
        @media (min-width: 992px) {
            .hero-grid { grid-template-columns: 1fr 1fr; gap: 0; height: 600px;}
        }
        
        .hero-content { padding: 40px; }
        @media (min-width: 992px) { .hero-content { padding: 80px; } }

        .eyebrow-text {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
            color: var(--p-text-muted);
            margin-bottom: 20px;
            display: block;
        }
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            line-height: 1.05;
            letter-spacing: -0.03em;
            margin-bottom: 24px;
            color: var(--p-text);
        }
        .hero-desc {
            font-size: 1.1rem;
            color: var(--p-text-muted);
            margin-bottom: 40px;
            max-width: 90%;
        }
        .btn-premium-solid {
            background-color: var(--p-accent);
            color: var(--p-bg);
            padding: 16px 36px;
            border-radius: var(--p-radius-pill);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: none;
            font-size: 1rem;
        }
        .btn-premium-solid:hover {
            background-color: var(--p-accent-hover);
            transform: translateY(-2px);
            color: var(--p-bg);
            opacity: 1;
        }
        
        .hero-image-wrap {
            height: 400px;
            width: 100%;
            position: relative;
        }
        @media (min-width: 992px) { .hero-image-wrap { height: 100%; } }
        .hero-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        /* --- Minimal Categories --- */
        .category-scroll {
            display: flex;
            gap: 30px;
            overflow-x: auto;
            padding-bottom: 20px;
            scrollbar-width: none; /* Firefox */
        }
        .category-scroll::-webkit-scrollbar { display: none; } /* Chrome */
        
        .cat-item-modern {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            min-width: 100px;
        }
        .cat-img-box {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: var(--p-surface);
            padding: 2px;
            border: 1px solid var(--p-border);
            transition: var(--p-transition);
            position: relative;
            overflow: hidden;
        }
        .cat-img-box img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .cat-item-modern:hover .cat-img-box { border-color: var(--p-text); }
        .cat-item-modern:hover .cat-img-box img { transform: scale(1.1); }
        .cat-name { font-size: 0.9rem; font-weight: 500; color: var(--p-text); }

        /* --- Section Titles --- */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 40px;
        }
        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            margin: 0;
        }
        .link-view-all {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }
        .link-view-all::after {
            content: ''; position: absolute; left: 0; bottom: -4px; width: 100%; height: 1px; background: currentColor;
        }

        /* --- High-End Product Card --- */
        .product-card-pro {
            position: relative;
            group: product; /* Tailwind tarzı grup hover mantığı için */
        }
        .product-card-pro:hover .pc-media img:first-child { opacity: 0; }
        .product-card-pro:hover .pc-media img:nth-child(2) { opacity: 1; transform: scale(1.05); }
        
        .pc-media {
            position: relative;
            aspect-ratio: 4/5; /* Modern, uzun tasarım */
            background-color: var(--p-surface);
            border-radius: var(--p-radius);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .pc-media img {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover;
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .pc-media img:nth-child(2) { opacity: 0; } /* İkinci resim gizli */

        /* Kart Üstü Badgeler & Favori */
        .pc-badges { position: absolute; top: 16px; left: 16px; display: flex; flex-direction: column; gap: 8px; z-index: 2; }
        .badge-premium { background: var(--p-accent); color: var(--p-bg); font-size: 0.7rem; font-weight: 600; padding: 4px 10px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px;}
        .badge-sale { background: var(--p-danger); }

        .btn-wishlist {
            position: absolute; top: 16px; right: 16px; z-index: 2;
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--p-bg); border: none;
            display: flex; align-items: center; justify-content: center;
            color: var(--p-text); opacity: 0; transform: translateY(-10px);
            box-shadow: var(--p-shadow-sm); transition: var(--p-transition);
        }
        .product-card-pro:hover .btn-wishlist { opacity: 1; transform: translateY(0); }
        .btn-wishlist:hover { color: var(--p-danger); transform: scale(1.1) !important;}

        /* Aşağıdan Kayan Sepete Ekle Butonu */
        .pc-quick-add {
            position: absolute; bottom: 16px; left: 16px; right: 16px; z-index: 2;
            transform: translateY(20px); opacity: 0; transition: var(--p-transition);
        }
        .product-card-pro:hover .pc-quick-add { transform: translateY(0); opacity: 1; }
        
        .btn-add-cart {
            width: 100%; padding: 12px; border-radius: var(--p-radius-sm);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(4px); border: none;
            font-size: 0.9rem; font-weight: 600; color: #000; /* Her zaman okunur */
            box-shadow: var(--p-shadow-floating); transition: 0.2s;
        }
        [data-theme="dark"] .btn-add-cart { background: rgba(0, 0, 0, 0.9); color: #fff;}
        .btn-add-cart:hover { background: var(--p-accent); color: var(--p-bg); }

        /* Kart Metin Bilgileri */
        .pc-info { text-align: left; }
        .pc-brand { font-size: 0.75rem; color: var(--p-text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .pc-title { font-size: 1.05rem; font-weight: 500; color: var(--p-text); margin-bottom: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pc-price { display: flex; align-items: center; gap: 10px; }
        .price-current { font-size: 1.1rem; font-weight: 700; color: var(--p-text); }
        .price-old { font-size: 0.9rem; text-decoration: line-through; color: var(--p-text-muted); }

        /* --- Asymmetric Promo Grid --- */
        .promo-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-top: 80px;
            margin-bottom: 80px;
        }
        @media (min-width: 768px) {
            .promo-grid { grid-template-columns: repeat(2, 1fr); }
            .promo-large { grid-column: 1 / 3; }
        }
        @media (min-width: 992px) {
            .promo-grid { grid-template-columns: 2fr 1fr; }
            .promo-large { grid-column: 1 / 2; }
        }

        .promo-box {
            position: relative;
            border-radius: var(--p-radius);
            overflow: hidden;
            background: #000; /* Resim yüklenene kadar koyu zemin */
            min-height: 350px;
        }
        .promo-box img {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            object-fit: cover; opacity: 0.8; transition: transform 0.8s ease;
        }
        .promo-box:hover img { transform: scale(1.03); }
        
        .promo-content {
            position: absolute;
            bottom: 0; left: 0; width: 100%;
            padding: 40px;
            color: #fff; /* Fotoğraf üzeri her zaman beyaz */
            background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
            z-index: 2;
        }
        .promo-content h3 { font-size: 2rem; font-weight: 700; letter-spacing: -1px; margin-bottom: 10px; }
        .promo-content p { font-size: 1rem; opacity: 0.9; margin-bottom: 20px; }
        .btn-outline-white {
            display: inline-block; padding: 10px 24px;
            border: 1px solid #fff; color: #fff; border-radius: var(--p-radius-pill);
            font-size: 0.9rem; font-weight: 500; transition: 0.3s;
        }
        .btn-outline-white:hover { background: #fff; color: #000; opacity: 1;}

        /* --- Minimal Footer --- */
        .site-footer {
            border-top: 1px solid var(--p-border-light);
            padding: 80px 0 40px;
            margin-top: 40px;
        }
        .footer-logo { font-size: 1.5rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 20px; display: inline-block;}
        .footer-title { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; margin-bottom: 24px; color: var(--p-text);}
        .footer-nav li { margin-bottom: 12px; }
        .footer-nav a { color: var(--p-text-muted); font-size: 0.95rem; }
        .footer-nav a:hover { color: var(--p-accent); }
        
        .newsletter-input {
            width: 100%; padding: 14px 20px;
            background: var(--p-surface); border: 1px solid transparent;
            border-radius: var(--p-radius-sm); color: var(--p-text);
            margin-bottom: 12px; transition: 0.3s; outline: none;
        }
        .newsletter-input:focus { border-color: var(--p-text-muted); background: var(--p-bg);}
        
        .footer-bottom {
            margin-top: 60px; padding-top: 24px;
            border-top: 1px solid var(--p-border-light);
            display: flex; flex-direction: column; gap: 16px;
            justify-content: space-between; align-items: center;
        }
        @media (min-width: 768px) { .footer-bottom { flex-direction: row; } }
        .copyright { color: var(--p-text-muted); font-size: 0.85rem; }

        /* --- Mobil Alt Navigasyon (Premium Yorum) --- */
        .mobile-bottom-bar {
            position: fixed; bottom: 0; left: 0; width: 100%;
            background-color: rgba(var(--p-bg-rgb), 0.9);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid var(--p-border-light);
            display: flex; justify-content: space-around; padding: 12px 0 20px; /* Safe area padding */
            z-index: 1040;
        }
        @media (min-width: 992px) { .mobile-bottom-bar { display: none; } }
        .m-nav-item { display: flex; flex-direction: column; align-items: center; gap: 4px; color: var(--p-text-muted); }
        .m-nav-item.active { color: var(--p-accent); }
        .m-nav-item span { font-size: 0.65rem; font-weight: 600; }

        /* Offcanvas Arama & Menü Custom */
        .offcanvas-premium { background-color: var(--p-bg); border: none; }
        .offcanvas-premium .btn-close { filter: var(--p-bg) == '#0A0A0A' ? invert(1) : none; }
        
    </style>
</head>
<body>

    <!-- SVG İkon Sprite (Feather/Minimalist Style) -->
    <svg width="0" height="0" class="d-none">
        <symbol id="icon-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></symbol>
        <symbol id="icon-user" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
        <symbol id="icon-heart" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></symbol>
        <symbol id="icon-cart" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></symbol>
        <symbol id="icon-menu" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></symbol>
        <symbol id="icon-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></symbol>
        <symbol id="icon-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></symbol>
        <symbol id="icon-home" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></symbol>
    </svg>

    <!-- Duyuru Çubuğu -->
    <div class="top-bar">
        Seçili ürünlerde sepette %15 indirim | Ücretsiz Kargo
    </div>

    <!-- Minimalist Header -->
    <header class="site-header" id="header">
        <div class="container-premium header-inner">
            <!-- Sol: Logo & Mobil Menü -->
            <div class="d-flex align-items-center gap-3">
                <button class="action-btn d-lg-none p-0" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <svg class="icon"><use href="#icon-menu"></use></svg>
                </button>
                <a href="#" class="brand-logo">FriSay<span></span></a>
            </div>

            <!-- Orta: Masaüstü Navigasyon -->
            <nav class="desktop-nav d-none d-lg-flex">
                <a href="#" class="nav-link-premium text-danger">Yeni Sezon</a>
                <div class="has-mega-menu">
                    <a href="#" class="nav-link-premium">Kadın</a>
                    <!-- Mega Menu İçeriği -->
                    <div class="mega-menu-wrapper">
                        <div class="container-premium">
                            <div class="row">
                                <div class="col-3">
                                    <ul class="mega-menu-list">
                                        <li><h6>Giyim</h6></li>
                                        <li><a href="#">Elbise</a></li>
                                        <li><a href="#">Tişört & Bluz</a></li>
                                        <li><a href="#">Ceket & Mont</a></li>
                                        <li><a href="#">Pantolon</a></li>
                                    </ul>
                                </div>
                                <div class="col-3">
                                    <ul class="mega-menu-list">
                                        <li><h6>Ayakkabı & Çanta</h6></li>
                                        <li><a href="#">Sneaker</a></li>
                                        <li><a href="#">Topuklu Ayakkabı</a></li>
                                        <li><a href="#">Omuz Çantası</a></li>
                                        <li><a href="#">Sırt Çantası</a></li>
                                    </ul>
                                </div>
                                <div class="col-6">
                                    <div class="promo-box" style="min-height: 200px; border-radius: 8px;">
                                        <img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=800&q=80" alt="Kadın Koleksiyon">
                                        <div class="promo-content p-3" style="background: none;">
                                            <h4 class="fw-bold mb-1">Sonbahar 26</h4>
                                            <a href="#" class="text-white text-decoration-underline" style="font-size: 0.9rem">Koleksiyonu İncele</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="#" class="nav-link-premium">Erkek</a>
                <a href="#" class="nav-link-premium">Aksesuar</a>
                <a href="#" class="nav-link-premium">Kozmetik</a>
            </nav>

            <!-- Sağ: İkonlar -->
            <div class="header-actions">
                <button class="action-btn d-none d-md-flex" data-bs-toggle="offcanvas" data-bs-target="#searchMenu">
                    <svg class="icon"><use href="#icon-search"></use></svg>
                </button>
                <button class="action-btn d-none d-md-flex" id="themeToggleBtn">
                    <svg class="icon" id="themeIcon"><use href="#icon-moon"></use></svg>
                </button>
                <a href="#" class="action-btn d-none d-md-flex">
                    <svg class="icon"><use href="#icon-user"></use></svg>
                </a>
                <a href="#" class="action-btn">
                    <svg class="icon"><use href="#icon-cart"></use></svg>
                    <span class="cart-badge" id="cartCounter">0</span>
                </a>
            </div>
        </div>
    </header>

    <main>