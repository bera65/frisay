# FShop — Open-Source PHP E-Commerce, Built for Real Stores

**FShop** is a self-hosted e-commerce platform written in plain PHP. No Laravel, no Symfony, no npm build step — just a fast, readable codebase you can deploy on any shared host, VPS, or local stack like WAMP/XAMPP.

It is designed for merchants who want full control: your data stays on your server, themes and modules extend the shop without forking core files, and a modern admin panel keeps day-to-day operations simple.

🔗 **Repository:** https://github.com/bera65/fshop

---

## Why FShop?

Most e-commerce platforms are either heavyweight frameworks or closed SaaS products. FShop takes a different path:

- **Lightweight architecture** — `container/` for page logic, `core/` for domain classes, Smarty for views. No `controllers/` / `models/` maze.
- **Theme-safe modules** — plugins live in `modules/` and hook into the storefront without editing theme files.
- **Production-ready defaults** — 4-step install wizard, demo data, Blue storefront theme, bilingual UI out of the box.
- **Integration-friendly** — REST Web API, module API endpoints, and cron hooks for automation.

If you can run PHP + MySQL on Apache, you can run FShop.

---

## Storefront Highlights

### Modern shopping experience
- SEO-friendly URLs (`/category/product-slug-123`)
- Product gallery, brand pages, category browsing, search
- Customer accounts: registration, login, password reset, order history
- Favorites, coupons, guest & registered checkout
- Invoice fields (company name, tax office, tax ID) for B2B-style orders
- Contact form and CMS pages for policies, about us, etc.

### Internationalization
- **Storefront:** Turkish & English UI with per-language product, category, brand, and CMS translations
- Language switcher and configurable default language
- Translation files in `lang/tr.php` and `lang/en.php`

### Multi-currency
- Products can be priced in TRY, USD, EUR, or gold-linked (XAU) units
- Automatic price refresh via cron (`/api/cron.php?action=currency`)
- Shop currency selector for customers

### Virtual products
- Sell digital goods: file downloads, license keys, or custom delivery text
- Secure download tokens after order fulfillment
- Cash-on-delivery automatically blocked for virtual carts

### SEO & structured data
- Per-product and per-page meta titles & descriptions
- Schema.org markup on product pages
- Dedicated SEO settings in admin

---

## Admin Panel

A clean, SaaS-inspired admin UI built with Bootstrap — not a legacy back office.

### Dashboard
- Revenue KPIs (today, last 30 days, trends)
- Order pipeline: pending, processing, shipped
- Charts for daily sales and operations
- Top sellers, recent orders, unread messages
- **Module hooks** — extend the dashboard without editing core templates

### Catalog management
- Products with images, variants, stock, barcodes, short & long descriptions
- Categories, brands, multi-language tabs per entity
- **Excel import** for bulk product uploads
- Virtual product configuration

### Orders & customers
- Filterable order list with status pills
- Order detail view with status updates, shipping info
- **One-click print** layout for packing slips / invoices
- Customer profiles and message inbox

### Marketing & content
- Coupon codes (percentage or fixed discount)
- CMS page editor
- SEO tools
- Theme customizer: colors, fonts, header layout, container width (Blue theme)

### Bilingual admin
- Turkish & English admin interface
- Language switcher in the header (and on the login page)
- Default admin language set at install or under **Languages**

### Modules & themes
- Install, enable, and configure modules from the panel
- Switch storefront themes (`default`, `blue`, `prime`, and more)
- Per-theme appearance options without touching code

---

## Module Ecosystem

Modules are first-class citizens. Each module is a self-contained folder with its own templates, assets, admin page, and optional API.

**Included modules (examples):**

| Module | Purpose |
|--------|---------|
| `paytr` | PayTR payment gateway |
| `bankwire` | Bank transfer / EFT |
| `cashondelivery` | Pay on delivery |
| `sanalpos` | Virtual POS integration |
| `reviews` | Product reviews tab |
| `slider` | Homepage slider |
| `whatsapp` | WhatsApp button in footer |
| `newsletter` | Email signup |
| `google-analytics` | Analytics snippet |
| `google-shopping` | Product feed export |
| `alert-price` | Price-drop alerts |
| `basitkargo` | Shipping integration |
| `socials` | Social media links |
| `question` | Product Q&A |
| `same-category-products` | Related products block |

**Extension points:**
- Display hooks: `footer`, `home_slider`, `product_tab`, `order_payment`, and more
- Internal hooks: `smarty.assign`, `head.assets`, `order.placed`, `admin_dashboard_*`
- Module API: `POST /api/module.php?m={module}&action={action}`
- Payment modules plug into checkout seamlessly

Full module developer guide: `modules/MODULE_DEVELOPER_GUIDE.md`

---

## Web API

Connect external systems — ERP, mobile apps, marketplaces — via a REST API.

- **Orders:** list, filter by date/status, update shipping & tracking
- **Products:** CRUD, quick price/stock updates, image upload (file, URL, or base64)
- **Categories & brands:** list; auto-create on product import

Authenticate with `X-API-Key` or `Authorization: Bearer`.  
Documentation: `docs/WEBAPI.md`

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL 5.7+ / MariaDB 10.3+ |
| Data access | PDO (`DB::` wrapper) |
| Templates | Smarty 5 |
| Admin UI | Bootstrap 5 |
| Routing | Apache `mod_rewrite` + `?container=` |

**Requirements:** `pdo_mysql`, `mbstring`, `gd`, writable `config/`, `cache/`, `img/products/`

---

## Installation

1. Upload files to your web root (or subfolder).
2. Create an empty MySQL database.
3. Open `/install/` in your browser.
4. Complete the wizard:
   - System requirements check
   - Database credentials
   - Site URL, **theme**, **store language**, **admin language**, admin account
   - Optional demo products & sample data
5. Log in at `/admin/`

Subfolder installs (e.g. `http://localhost/fshop/`) are supported — the wizard sets `RewriteBase` automatically.

Detailed guide: `install/README.md`

---

## Security & Deployment Checklist

- `config/env.php` is generated at install (never commit it)
- Set `APP_ENV=production` and `APP_DEBUG=false` on live servers
- Use HTTPS, a strong admin password, and rotate `SHOP_TOKEN`
- Block web access to `/install/` after setup
- Schedule the currency cron job if you use foreign-currency pricing

---

## Who Is It For?

- **Developers** building client stores who want readable PHP and a module API
- **Small & mid-size merchants** in Turkey and abroad who need TR/EN, local payments, and invoicing fields
- **Agencies** shipping multiple shops on shared hosting without Docker overhead
- **Contributors** looking for a clear, hook-based plugin model

---

## Get Started

```bash
git clone https://github.com/bera65/fshop.git
# Point your vhost to the project folder, create a database, then visit /install/
```

Star the repo if you find it useful — issues and pull requests are welcome.

---

*FShop — open-source e-commerce you can actually read, extend, and deploy.*
