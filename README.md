# CodeIgniter CRUD & REST API Generator — PHP Admin Panel Builder

> Rapidly build web applications with auto-generated CRUD interfaces, REST APIs, user authentication, wallet/payment management, and a drag-and-drop page builder — all powered by CodeIgniter 3.

[![PHP](https://img.shields.io/badge/PHP-%3E%3D5.4-blue)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3-orange)](https://codeigniter.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Version](https://img.shields.io/badge/version-2.6.8-brightgreen)](https://github.com/vipul26singh/qpay)

---

## What Is This?

**QPay** is a full-featured PHP web application platform that eliminates boilerplate. Point it at your MySQL database and it auto-generates:

- Fully functional **CRUD admin interfaces**
- **RESTful JSON APIs** with JWT authentication
- **Role-based access control** (Admin, Superadmin, Customer, Public)
- A polished **AdminLTE dashboard** — ready to ship

Built on CodeIgniter 3, it's fast to deploy, easy to extend, and production-ready.

---

## Key Features

### Core Platform
- **CRUD Generator** — auto-build create/read/update/delete views and controllers for any database table
- **REST API Generator** — expose any data as a JWT-secured RESTful API instantly
- **Setup Wizard** — guided installation with database auto-configuration
- **Form Builder** — drag-and-drop form designer with validation
- **Page Builder** — visual HTML page editor with Bootstrap components

### Authentication & Users
- Full user authentication with email/password
- **Google OAuth** login integration
- **Mobile OTP** verification (Infobip SMS gateway)
- Role-based permissions with fine-grained group management
- API key management per user

### Business Features
- **User Wallet / Payment System** — balance tracking, transactions, QR code payments
- **Blog & CMS** — posts, categories, static pages
- **Equipment Management** — check-in / check-out tracking with QR codes
- **Event Management** — scheduling and attendance
- **Report Generation** — exportable reports with PDF support

### Developer Experience
- Multi-language support (50+ locales)
- PDF generation (FPDF, DOMPDF, HtmlPDF)
- QR code generation built-in
- DataTables for sortable/searchable grids
- APIDoc-format API documentation
- Debug bar for development

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend Framework | CodeIgniter 3 |
| Language | PHP >= 5.4 |
| Database | MySQL / MariaDB |
| Authentication | Aauth + JWT (`firebase/php-jwt`) |
| HTTP Client | Guzzle 7 |
| OAuth | Google API Client |
| SMS Gateway | Infobip |
| QR Codes | `chillerlan/php-qrcode` |
| Admin Theme | AdminLTE |
| Frontend | jQuery, Bootstrap, DataTables |
| Rich Text | CKEditor, TinyMCE, Medium Editor |
| File Uploads | Fine Uploader |

---

## Quick Start

### Requirements
- PHP >= 5.4 (7.x recommended)
- MySQL >= 5.7
- Composer
- Apache / Nginx with mod_rewrite

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/vipul26singh/qpay.git
cd qpay

# 2. Install PHP dependencies
composer install

# 3. Import the database schema
mysql -u root -p your_database < qpay.sql

# 4. Configure your environment
cp application/config/database.php.example application/config/database.php
# Edit database.php with your credentials

# 5. Set base URL
# Edit application/config/config.php → $config['base_url']

# 6. Run the setup wizard
# Open your browser: http://localhost/qpay/wizzard
```

### Web Server (Apache)

```apache
<VirtualHost *:80>
    ServerName qpay.local
    DocumentRoot /var/www/qpay
    <Directory /var/www/qpay>
        AllowOverride All
    </Directory>
</VirtualHost>
```

---

## REST API

APIs are JWT-secured and return JSON. Base path: `/api/`

| Endpoint | Description |
|---|---|
| `POST /api/user/login` | Authenticate and get JWT token |
| `GET /api/user/profile` | Get user profile |
| `GET /api/user_wallet` | Wallet balance and history |
| `POST /api/mobile_otp` | Send/verify OTP |
| `GET /api/blog` | Blog posts |
| `GET /api/static_pages` | Static page content |

**Example request:**
```bash
curl -H "Authorization: Bearer <jwt_token>" \
     https://your-domain.com/api/user/profile
```

Full API documentation is available in the `/apidoc` directory.

---

## Project Structure

```
qpay/
├── application/
│   ├── controllers/
│   │   ├── api/            # REST API endpoints
│   │   └── administrator/  # Admin panel controllers
│   ├── models/             # Database models
│   ├── views/
│   │   ├── frontend/       # Public pages
│   │   └── backend/        # Admin dashboard
│   ├── libraries/          # Auth, JWT, SMS, PDF, QR
│   └── config/             # App, DB, routes config
├── asset/                  # CSS, JS, admin theme
├── form_builder/           # Visual form builder
├── cc-content/             # Page builder content
├── vendor/                 # Composer packages
├── qpay.sql               # Database schema
└── index.php              # Front controller
```

---

## Configuration

Key config files under `application/config/`:

| File | Purpose |
|---|---|
| `config.php` | Base URL, encryption key, language |
| `database.php` | MySQL connection settings |
| `rest.php` | API output format, rate limits |
| `aauth.php` | Auth settings, password rules |
| `routes.php` | URL routing |

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m 'Add: your feature description'`
4. Push and open a Pull Request

---

## License

This project is licensed under the MIT License.

---

## Keywords

`codeigniter crud generator` · `php rest api generator` · `php admin panel` · `codeigniter admin dashboard` · `php web application builder` · `codeigniter jwt api` · `php payment wallet system` · `codeigniter 3 boilerplate` · `php rapid application development` · `codeigniter aauth`
