# FShop AI Context

## Project Type

Open source ecommerce platform written in PHP.

## Stack

- PHP 7.4+
- MySQL
- PDO
- Smarty
- Bootstrap

## Directory Structure

/config
/controllers
/models
/modules
/templates
/install
/api

## Templates

Storefront:
templates/default

Admin:
templates/admin

## Database

Core tables:

- products
- categories
- orders
- customers
- settings

## Routing

URL rewriting is handled by Apache mod_rewrite.

## Module System

Modules are stored inside:

modules/

Each module can contain:

- install scripts
- admin pages
- frontend hooks

## Currency System

Currency update cron endpoint:

/api/cron.php?action=currency&token=SHOP_TOKEN
