# Changelog

All notable changes to FShop are documented here.  
Version numbers follow [Semantic Versioning](https://semver.org/).

---

## [2.1.0] — 2026-07-10

### Added

- **Theme gallery** — card-based theme list with activate, preview, edit, ZIP upload, and theme copy
- **`theme.schema.json`** — per-theme customization (options + colors) for Nova and Blue
- **Performance panel** (`/admin/performance`) — template cache toggle, cache clear, gzip, HTML minify, debug mode, optional guest page cache
- **Dashboard news** — RSS feed from [frisay.com/rss.xml](https://frisay.com/rss.xml) with local fallback (`data/frisay-news.json`, `/rss.xml`)
- **Site version** — `FShop::VERSION` (2.1.0) shown in admin footer
- **Cart promotions** — admin “Sepet Kampanyaları” (`nth_item`, `buy_x_pay_y`), auto-applied at cart/checkout
- **Category sidebar filters** — subcategories, brands, price range (Nova + Blue)
- **Blue cart page** — full cart layout with summary, recommended products, favorites
- **Related products** — sidebar block on product pages (category → brand → store fallback)
- **Shopier module** — REST API product sync (create / update / delete)
- **Discount timer module** — scheduled product discounts with countdown
- **Product extra text module** — global text on all product pages via module settings
- **CHANGELOG.md** and streamlined English **README.md**

### Changed

- Admin **Temalar** split into gallery + dedicated customize page (`/admin/theme-customize`)
- Module list search/filter fixed (Bootstrap `d-flex` conflict)
- Debug and error display can be controlled from admin (overrides `env.php` when set)

### Fixed

- Admin coupons page 500 when `CartPromotion` class was not bootstrapped
- Theme color saving now respects per-theme schema (Blue / Nova variables)

---

## [2.0.0] — 2026-06

### Added

- 4-step **install wizard** (`/install/`) with requirements check, DB setup, admin account, optional demo data
- **Blue** and **Nova** storefront themes; theme customizer (colors, fonts, header variants)
- **Module system** — install / enable / configure from admin; display hooks and internal hooks
- **REST Web API** — orders, products, categories, brands ([docs/WEBAPI.md](docs/WEBAPI.md))
- **Bilingual** storefront and admin (Turkish / English)
- **Multi-currency** products with cron price refresh (`/api/cron.php?action=currency`)
- **Virtual / digital products** with secure download tokens
- **Customer accounts** — registration, login, orders, favorites, addresses
- **Coupons**, CMS pages, contact form, SEO fields, Schema.org on products
- **Excel product import**, order print view, dashboard KPIs and charts
- Bundled modules: PayTR, bank wire, cash on delivery, reviews, slider, WhatsApp, newsletter, Google Analytics / Shopping, and more

### Changed

- Architecture: `container/` + `core/` + Smarty (no Laravel/Symfony)
- Admin UI refreshed (Bootstrap, sidebar layout)

---

## [1.0.0] — 2025

### Added

- Initial open-source release of the FShop e-commerce codebase
- Basic catalog, cart, checkout, and admin product/order management
- `default` storefront theme and `templates/admin` back office

---

[2.1.0]: https://github.com/bera65/fshop/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/bera65/fshop/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/bera65/fshop/releases/tag/v1.0.0
