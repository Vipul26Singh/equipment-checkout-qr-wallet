# CLAUDE.md — Developer Guide

QPay is a **CodeIgniter 3 PHP web application** for equipment checkout/check-in management, event tracking, and QR-code-based user wallets. It was scaffolded using [api_generator](https://github.com/vipul26singh/api_generator).

## What This App Does

- Tracks physical equipment (inventory, checkout to events, check-in returns)
- Issues QR-code digital wallets to users
- Manages events and links equipment to them
- Provides a REST API (JWT-secured) for mobile/frontend clients
- Admin dashboard with user/role/content management

---

## Project Structure

```
application/
  controllers/
    api/             - REST API (JWT auth), one file per resource
    administrator/   - Admin panel, one file per section
  models/            - DB models; naming: <table>_model.php
  views/
    frontend/        - Public-facing templates
    backend/         - Admin dashboard (AdminLTE)
  libraries/         - Aauth (auth), JWT, Infobip (SMS), QR, PDF
  config/            - All config files (see below)
  migrations/        - DB migrations
asset/               - CSS/JS (AdminLTE, Bootstrap, jQuery, DataTables)
form_builder/        - Visual drag-and-drop form builder module
cc-content/          - Page builder themes and elements
vendor/              - Composer packages
qpay.sql             - Full DB schema
```

---

## Key Config Files

| File | What to change |
|---|---|
| `application/config/config.php` | Base URL, encryption key |
| `application/config/database.php` | MySQL host/user/db |
| `application/config/rest.php` | API format, rate limits, auth |
| `application/config/aauth.php` | Auth rules, password policy, groups |
| `application/config/routes.php` | URL routing |
| `application/config/autoload.php` | Auto-loaded libs/helpers/models |

---

## Architecture

### Controllers
- Admin controllers → `application/controllers/administrator/`, extend `CI_Controller`
- API controllers → `application/controllers/api/`, extend `REST_Controller`
- Access control: `$this->aauth->is_loggedin()` / `$this->aauth->is_admin()`

### Models
- CodeIgniter Active Record pattern
- `$this->db->get()`, `$this->db->insert()`, `$this->db->update()`

### Auth
- **Aauth** — user accounts, groups, permissions
- **JWT** (`firebase/php-jwt`) — API token auth
- **Google OAuth** — via `google/apiclient`
- **OTP** — Infobip SMS gateway

### REST API
- JSON responses
- `Authorization: Bearer <token>` header required
- Token issued at `POST /api/user/login`

---

## Core Business Logic

### Equipment Checkout Flow
1. Equipment created in `equipments` table with category, barcode, size
2. Admin creates an event in `events`
3. Equipment assigned to event via `event_equipment_checkout`
4. Checkout logged in `equipment_checkout` (timestamp, user, condition)
5. Return logged in `equipment_checkin`

### Wallet / QR Flow
1. User account created → wallet record in `user_wallet`
2. QR code generated via `chillerlan/php-qrcode`
3. Wallet code retrievable via `/api/user_wallet`

---

## Common Tasks

### Add a new REST API endpoint

```php
// application/controllers/api/YourResource.php
class YourResource extends REST_Controller {
    public function index_get() {
        $data = $this->your_model->get_all();
        $this->response(['status' => true, 'data' => $data], REST_Controller::HTTP_OK);
    }
    public function index_post() {
        // handle POST
    }
}
```

### Add a new admin page

1. Create `application/controllers/administrator/YourPage.php`
2. Load view: `$this->load->view('backend/your_view', $data)`
3. Add link in sidebar template

### Run database migration

Use CodeIgniter's migration library or run SQL directly against the `qpay` database.

---

## Dependencies

```bash
composer install
```

Key packages:
- `firebase/php-jwt` — JWT tokens
- `google/apiclient` — Google OAuth
- `guzzlehttp/guzzle` — HTTP client
- `chillerlan/php-qrcode` — QR code generation
- `infobip/infobip-api-php-client` — SMS OTP
- `dompdf/dompdf` — PDF export

---

## Environment

- Set `ENVIRONMENT` in `index.php` (`development` / `production`)
- Development mode enables debug bar and whoops error pages
- Logs: `application/logs/` — set level via `$config['log_threshold']`
- Requires Apache `mod_rewrite` (`.htaccess` included)
