# Changelog

All notable changes to FShop are documented here.  
Version numbers follow [Semantic Versioning](https://semver.org/).

---

## [2.4.1] — 2026-07-18

### Added

#### ftheme-edit — Canlı tema düzenleyici
- **WordPress-style customizer** — admin entry shows only “Canlı Düzenle”; sidebar left, storefront preview (iframe) right
- **Click-to-edit** — editable text regions on home/footer/cookie banner (`data-ftheme` hooks in fyazilim theme)
- **Homepage blocks** — reorder up/down, add/remove block types: slider, featured, promo, categories, home text, custom HTML, **banner** (image, link, width %; consecutive banners render in one row)
- **Banner media picker** — “Medyadan seç” via admin media library modal
- **Renkler panel** — only theme variables that actually affect the storefront; `colors.css` aliases for legacy `style.css` / cart variables
- **CSS / JS panel** — edit `custom.css` and `custom.js` without touching theme core files
- **Live preview** — `postMessage` sync for text, colors, blocks, and custom CSS (no full reload for every change)
- **JSON publish** — “Yayınla” saves via fetch; fixes invalid payload errors with large block JSON

#### fyazilim header — arama
- **header0** — full search bar between logo and cart (desktop); dedicated mobile search row below header
- **header1** — search icon next to cart; on desktop search replaces the nav row; on mobile search opens full-width below the header row
- **Autocomplete** — product suggestions with image, name, category, and price (`/api/search-suggest.php`, min. 2 characters)
- Shared partial: `templates/fyazilim/plugin/header-search.tpl`

### Changed

- **Site version** — `FShop::VERSION` 2.4.1 (admin footer)
- **ftheme-edit colors** — removed non-working editor entries (`fy-primary` as “button color”, unused `fy-footer-mid`); button color tied to `fy-gradient`; cart/catalog aliases follow editable gradient
- **Reviews invite** (see 2.4.0) — e-mail copy clarifies: after **delivered** status, wait **X days**, then invite to review with optional **%Y personal coupon**

### Fixed

- `api/search-suggest.php` — missing bootstrap (`IN_SCRIPT` / `settings.php`); autocomplete now returns product JSON
- ftheme-edit Smarty path for product slider partial inside block loop
- ftheme-edit customizer save and live block preview (`syncBlocks` postMessage)
- Media library modal z-index above customizer overlay
- `--surface` wired to page background; `--font-family` alias in `custom.css`

---

## [2.4.0] — 2026-07-17

### Added

#### Reviews invite (yorum daveti)
- After an order is **delivered**, wait **X days** then e-mail the customer to review purchased products
- Admin-editable subject/body with placeholders (`{customer_name}`, `{products_list}`, `{coupon_code}`, …)
- Optional **personal coupon** (bound to customer) created when the invite is sent
- Cron: `/api/module.php?m=reviews&action=cron&token=SHOP_TOKEN`
- Queue UI under **Modules → Ürün Yorumları → Gönderim kuyruğu**

#### Customer-bound coupons (core)
- `coupons.id_user` — optional customer binding; validation requires login and matching account
- Admin coupon form: customer selector; list shows assigned customer
- `Coupon::createPersonal()` for one-time personal codes (used by reviews invite)

#### Product Set (bundle) module
- **Pack product type** — sell multiple physical products as one set (e.g. newborn kit)
- Stock = minimum of component stocks; price = sum of components (optional fixed override)
- Add to cart expands into separate component lines; order stock deducts from children
- Admin product editor: type “Set (paket)” + component picker

#### Point of Sale (POS) module
- **Full-screen kasa terminal** — PIN or admin session; product grid, barcode scan, cart, cash / card / transfer checkout
- **Admin menu** — “Point of Sale” link under General (`/admin/pos`)
- **Kasa durumu** — today’s sales by payment type (cash, card, transfer, pending transfer) with grand total
- **Sales receipt** — thermal-style receipt modal after checkout (customer, items, payment, order ref + CODE39 barcode, print)
- **Customer on POS** — search, visitor default, create customer from pay flow
- **Admin customers** — “Add customer” modal on customer list (`Customer::createByAdmin`)
- **Stock rules** — hide out-of-stock products; optionally allow selling when stock is zero
- **Payment adjustments** — per-method discount or commission for credit card and bank transfer (e.g. 3% transfer discount, 5% card commission)
- **Module settings** — store label, order status, PIN lock, external card terminal URL (with current-domain presets), auto fullscreen on open

#### Smart Campaign module
- **Order status triggers** — campaigns can fire on placed / updated order status (e.g. shipped, delivered)
- Hooks: `order.placed`, `order.updated`

#### System e-mail layout
- **Three-part template** for all `Mail::send()` messages: editable header + dynamic body + editable footer
- Admin **Settings → E-posta** tab with header/footer editors and live preview iframe
- Keys: `MAIL_HEADER`, `MAIL_FOOTER`

#### Store & settings
- **Tabbed admin settings** — General, Contact, E-mail, Orders, Returns
- **Store maintenance mode** — toggle shop active/inactive, custom message, IP whitelist (`StoreStatus`, `SHOP_ACTIVE`, `SHOP_MAINTENANCE_*`)
- **Centralized contact info** — address, hours, social links in Settings → Contact; storefront contact page reads from settings

#### Admin & customers
- **Customer contact modal** on customer detail — message + WhatsApp (Wapio if configured, else `wa.me`) or e-mail (`CustomerContact`)

#### Themes
- **Theme schema options** — `theme.schema.json` driven colors/options for **fyazilim** and **blue** themes
- Social link blocks and option-driven CSS/JS wired in storefront templates

### Changed

- **POS UX** — viewport-fixed layout (no page scroll); 5-column product grid; compact stats; barcode field auto-focus; add-to-cart beep
- **POS modals** — customer picker stacks above pay modal (no need to close payment first)
- **POS receipt print** — dedicated print area so full receipt (not only barcode) goes to printer
- **Site version** — `FShop::VERSION` 2.4.0 (admin footer)

### Fixed

- POS stacked modals (customer behind payment overlay)
- POS external card URL defaulting to localhost — admin presets use current site domain
- POS receipt print showing only barcode in print preview

---

## [2.3.0] — 2026-07-16

### Added

- **Admin i18n** — English default admin language; all admin UI strings via `adminT` / `{'Key'|adminT}` with `lang/admin/en.php` and `lang/admin/tr.php`
- **Admin language switcher** — session-based TR/EN toggle in admin header

### Changed

- **Admin notifications** — page UI and notification titles/messages translated; legacy Turkish records mapped at display time
- **Site version** — `FShop::VERSION` 2.3.0 (admin footer)

---

## [2.2.0] — 2026-07-15

### Added

- **Login with email or phone** — customers can sign in using either identifier (`Customer::login`, auth API / login form)
- **`main_menu` display hook** — editable top navigation; **main-menu** module (custom links, category, CMS, blog)
- **Blog module** — installable posts list (`/blog`), categories (`/blog/kategori/{slug}-{id}`), detail (`/blog/{slug}-{id}`) with admin CRUD, TinyMCE + media library
- **Admin media picker** — reusable `media-picker.js` for TinyMCE / form fields (besides product attach)
- **Admin product media library** — “Dosya yöneticisi” modal; browse `img/`, upload to `img/media/`, multi-select, attach to product (`MediaLibrary`, `/api/admin-media.php`)
- **Admin product editor redesign** — sticky header, card sections, sticky price/media sidebar (`product-editor.css`)
- **AJAX product images** — cover / delete without full page reload; multi-file upload endpoint
- **Admin confirm modal** — shared “Vazgeç / Evet, Onayla” dialog for product, category, and brand delete
- **Bankwire payment discount** — `BANKWIRE_DISCOUNT_PERCENT` (havale indirimi) applied at checkout when bank transfer is selected
- **Checkout cargo selection** — customer picks carrier; fee from cargo rules (session + AJAX `set_cargo` / `set_payment`)
- **Order contact attachments** — optional file upload on “Bu sipariş hakkında soru sor” (`img/contact/`, `/api/contact-file.php`)
- **Admin customer password reset** — set customer password from customer detail page
- **Admin customer profile edit** — name, phone, and email editable by admin

### Changed

- Checkout shipping: global `FREE_SHIPPING_MIN` / `SHIPPING_FEE` settings removed from flow; rates come only from **Kargolar**
- Bankwire order confirmation UI — larger amount display, copy buttons for IBAN / holder / reference
- Customer account order detail — clearer breakdown (subtotal, promotions, coupon, payment discount, shipping, cargo, total)
- Admin product image workflow — open media library first instead of immediate file-input upload
- Admin set-password rules — minimum 8 characters (complexity optional for admin resets; e.g. `12345678` allowed)

### Fixed

- New product form warning: undefined `cost` key
- Media library “Bağlantı hatası” — clean JSON responses from `/api/admin-media.php` (output buffer / bootstrap clash)
- Returns admin list fatal: `ReturnRequest::adminUrl()` → `adminLink()` / `Admin::url()`
- Admin customer password change appearing to save while rejecting numeric-only passwords

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

[2.4.1]: https://github.com/bera65/fshop/compare/v2.4.0...v2.4.1
[2.4.0]: https://github.com/bera65/fshop/compare/v2.3.0...v2.4.0
[2.3.0]: https://github.com/bera65/fshop/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/bera65/fshop/compare/v2.1.0...v2.2.0
[2.1.0]: https://github.com/bera65/fshop/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/bera65/fshop/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/bera65/fshop/releases/tag/v1.0.0
