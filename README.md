# FriSay

**Version 2.1.0** — Open-source PHP e-commerce for merchants and developers who want a readable, self-hosted store without a heavy framework.

FriSay runs on plain PHP, MySQL, Apache, and Smarty. You own your data, extend the shop with modules, and customize the storefront with themes — no Node build step, no vendor lock-in.

🔗 **Repository:** [github.com/bera65/frisay](https://github.com/bera65/frisay)

---

## Live demo

| | URL |
|---|-----|
| **Storefront** | [fyazilim.com/fshop](https://fyazilim.com/fshop/) |
| **Admin panel** | [fyazilim.com/fshop/admin](https://fyazilim.com/fshop/admin/) |

> Install the project locally with the wizard at `/install/` — see [install/README.md](install/README.md).

---

## What you get

- SEO-friendly catalog, cart, checkout, customer accounts, coupons, multi-language (TR/EN), multi-currency
- Modern admin: dashboard, orders, products, CMS, themes, performance tools, module manager
- **Module system** — payment, shipping, marketing, and UI extensions in `modules/`
- **Theme system** — switchable storefront themes with gallery upload, copy, and schema-based customization
- **REST Web API** for orders, products, categories, and brands — see [docs/WEBAPI.md](docs/WEBAPI.md)

---

## Quick start

**Requirements:** PHP 7.4+, MySQL 5.7+, Apache `mod_rewrite`, extensions `pdo_mysql`, `mbstring`, `gd`

1. Clone or upload the project to your web root.
2. Create an empty MySQL database.
3. Open `/install/` in your browser and complete the wizard.
4. Sign in at `/admin/`.

Full instructions: **[install/README.md](install/README.md)**

---

## Documentation

| Topic | Guide |
|-------|--------|
| Installation | [install/README.md](install/README.md) |
| Module development | [modules/MODULE_DEVELOPER_GUIDE.md](modules/MODULE_DEVELOPER_GUIDE.md) |
| Theme development | [docs/THEME_AI_PROMPT.md](docs/THEME_AI_PROMPT.md) |
| Web API | [docs/WEBAPI.md](docs/WEBAPI.md) |
| Architecture (AI / agents) | [AGENTS.md](AGENTS.md) · [AI_CONTEXT.md](AI_CONTEXT.md) |
| Release history | [CHANGELOG.md](CHANGELOG.md) |

---

## Tech stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 7.4+ |
| Database | MySQL / MariaDB |
| Templates | Smarty 5 |
| Admin UI | Bootstrap 5 |
| Routing | Apache `mod_rewrite` |

---

## License

Open source — see the repository for license terms.

**FriSay** · [frisay.com](https://frisay.com/)
