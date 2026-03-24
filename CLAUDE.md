# CLAUDE.md — Project Guide for AI Assistants

This file provides context and instructions for working with this codebase using Claude or other AI coding assistants.

## Project Overview

**QPay** is a CodeIgniter 3 PHP web application platform. It auto-generates CRUD interfaces, REST APIs, and admin dashboards from a MySQL database schema. It also includes a user wallet/payment system, blog CMS, equipment tracking, form builder, and a visual page builder.

- **Framework:** CodeIgniter 3
- **Language:** PHP >= 5.4
- **Database:** MySQL/MariaDB
- **Version:** 2.6.8

---

## Repository Structure

```
application/
  controllers/
    api/           - REST API controllers (JWT-secured)
    administrator/ - Admin panel controllers
  models/          - Database models (30+ classes)
  views/
    frontend/      - Public-facing templates
    backend/       - Admin dashboard views
  libraries/       - Auth (Aauth), JWT, SMS, PDF, QR code
  config/          - All configuration files
  migrations/      - Database migration files
asset/             - Frontend assets (AdminLTE, jQuery, Bootstrap)
form_builder/      - Visual form builder module
cc-content/        - Page builder themes and elements
vendor/            - Composer dependencies
qpay.sql           - Full database schema
```

---

## Key Configuration Files

| File | Purpose |
|---|---|
| `application/config/config.php` | Base URL, encryption key, session settings |
| `application/config/database.php` | MySQL connection (host, user, db name) |
| `application/config/rest.php` | REST API format, auth, rate limits |
| `application/config/aauth.php` | User auth settings, password rules, groups |
| `application/config/routes.php` | URL routing rules |
| `application/config/autoload.php` | Auto-loaded libraries, helpers, models |

---

## Architecture Patterns

### Controllers
- All admin controllers extend `CI_Controller` and live in `application/controllers/administrator/`
- API controllers extend `REST_Controller` (custom library) and live in `application/controllers/api/`
- Use `$this->aauth->is_loggedin()` / `$this->aauth->is_admin()` for access control

### Models
- Follow CodeIgniter's Active Record pattern
- Models are in `application/models/` with `_model.php` suffix convention
- Database queries use `$this->db->get()`, `$this->db->insert()`, etc.

### Authentication
- **Aauth library** handles user auth, groups, and permissions
- **JWT** (`firebase/php-jwt`) is used for API token auth
- Google OAuth via `google/apiclient`
- Mobile OTP via Infobip SMS gateway

### REST API
- All API responses are JSON
- JWT token required in `Authorization: Bearer <token>` header
- Token issued at `/api/user/login`
- Rate limiting and key-based access configured in `rest.php`

---

## Common Development Tasks

### Adding a New API Endpoint

1. Create a controller in `application/controllers/api/YourName.php`
2. Extend `REST_Controller`
3. Name methods with HTTP verb suffix: `index_get()`, `index_post()`
4. Use `$this->response(['data' => ...], REST_Controller::HTTP_OK)`

```php
class YourName extends REST_Controller {
    public function index_get() {
        $data = $this->your_model->get_all();
        $this->response(['status' => true, 'data' => $data], REST_Controller::HTTP_OK);
    }
}
```

### Adding a New Admin Page

1. Create controller in `application/controllers/administrator/`
2. Load the view: `$this->load->view('backend/your_view', $data)`
3. Add menu item in the sidebar view template

### Database Migrations

Migration files live in `application/migrations/`. Run via CodeIgniter's migration library or the admin wizard.

---

## Dependencies

Install with Composer:
```bash
composer install
```

Key packages:
- `firebase/php-jwt` — JWT token generation/validation
- `google/apiclient` — Google OAuth
- `guzzlehttp/guzzle` — HTTP requests
- `chillerlan/php-qrcode` — QR code generation
- `infobip/infobip-api-php-client` — SMS OTP
- `dompdf/dompdf` — PDF export

---

## Database

Import the full schema:
```bash
mysql -u root -p qpay < qpay.sql
```

Key tables:
- `aauth_users` — user accounts
- `aauth_groups` — roles (admin, superadmin, customer, public)
- `aauth_group_to_group` — group hierarchy
- `user_wallet` — wallet balances
- `blog`, `blog_category` — CMS content
- `equipment` — equipment inventory
- `events` — event scheduling
- `form_*` — form builder definitions

---

## Environment Notes

- Set `ENVIRONMENT` to `development` or `production` in `index.php`
- Error reporting and debug bar activate in `development` mode
- Base URL must be set in `application/config/config.php`
- `.htaccess` must be enabled (Apache `mod_rewrite` required)

---

## Testing & Debugging

- Debug bar: enabled in development via `maximebf/debugbar`
- Error pages: `filp/whoops` in development mode
- API testing: use Postman or curl with JWT Bearer token
- Logs: `application/logs/` (set `$config['log_threshold']` in config.php)
