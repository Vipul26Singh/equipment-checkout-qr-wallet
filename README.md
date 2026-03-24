# QPay — Equipment Checkout & QR Wallet Management System

> A PHP web application for managing equipment inventory, tracking check-in/check-out across events, and issuing QR-code-based digital wallets to users.

[![PHP](https://img.shields.io/badge/PHP-%3E%3D5.4-blue)](https://php.net)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-3-orange)](https://codeigniter.com)
[![Version](https://img.shields.io/badge/version-2.6.8-brightgreen)](https://github.com/vipul26singh/equipment-checkout-qr-wallet)

---

## What Is QPay?

**QPay** is a web-based platform that helps organizations manage physical equipment across events and assign QR-code wallets to users. It's built on CodeIgniter 3 and was scaffolded using [api_generator](https://github.com/vipul26singh/api_generator).

**Core use cases:**
- Track which equipment is checked out, to which event, and when it returns
- Issue users unique QR-code wallet identifiers
- Manage users, roles, content, and forms from a single admin panel

---

## Features

### Equipment Management
- Equipment inventory with categories, barcodes, sizes, and condition tracking
- **Check-out** equipment to specific events with timestamps
- **Check-in** returns and condition logging
- Equipment checklist per event
- Full audit history per item

### Event Management
- Create and schedule events
- Assign equipment lists to events
- Track equipment usage across multiple events

### User Wallet & QR Codes
- Each user gets a unique digital wallet code
- Auto-generated QR codes for wallets
- REST API endpoints for wallet lookup and management
- Mobile OTP verification (Infobip SMS)

### Admin Panel
- AdminLTE dashboard
- Role-based access control (Admin, Superadmin, Customer, Public)
- User and group management with fine-grained permissions
- Blog / CMS for pages and content
- Drag-and-drop form builder
- Visual page builder

### API
- JWT-secured RESTful API
- Google OAuth login
- API key management per user

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP >= 5.4, CodeIgniter 3 |
| Database | MySQL / MariaDB |
| Auth | Aauth + JWT (`firebase/php-jwt`) |
| SMS / OTP | Infobip |
| QR Codes | `chillerlan/php-qrcode` |
| OAuth | Google API Client |
| Admin UI | AdminLTE, Bootstrap, jQuery |
| PDF Export | FPDF, DOMPDF |
| Scaffolded with | [api_generator](https://github.com/vipul26singh/api_generator) |

---

## Quick Start

### Requirements
- PHP >= 5.4 (7.x recommended)
- MySQL >= 5.7
- Composer
- Apache with `mod_rewrite`

### Installation

```bash
# 1. Clone
git clone https://github.com/vipul26singh/equipment-checkout-qr-wallet.git
cd equipment-checkout-qr-wallet

# 2. Install dependencies
composer install

# 3. Import database
mysql -u root -p your_db_name < qpay.sql

# 4. Set database credentials
# Edit application/config/database.php

# 5. Set base URL
# Edit application/config/config.php → $config['base_url']

# 6. Run setup wizard
# Open: http://localhost/equipment-checkout-qr-wallet/wizzard
```

---

## REST API

All endpoints require a JWT Bearer token obtained from `/api/user/login`.

| Endpoint | Method | Description |
|---|---|---|
| `/api/user/login` | POST | Authenticate, returns JWT |
| `/api/user/profile` | GET | User profile |
| `/api/user_wallet` | GET | Wallet code and QR |
| `/api/mobile_otp` | POST | Send/verify OTP |
| `/api/blog` | GET | Blog posts |

Full API docs in `/apidoc/`.

---

## Project Structure

```
application/
  controllers/
    api/             # JWT-secured REST endpoints
    administrator/   # Admin panel controllers
  models/            # DB models (equipment, wallet, events, users...)
  views/
    frontend/        # Public pages
    backend/         # Admin dashboard
  libraries/         # Auth, JWT, SMS, QR, PDF
  config/            # App, DB, REST, auth config
asset/               # AdminLTE, jQuery, Bootstrap
form_builder/        # Visual form builder
qpay.sql             # Full database schema
```

---

## Key Database Tables

| Table | Purpose |
|---|---|
| `equipments` | Equipment inventory |
| `equipment_category` | Equipment categories |
| `equipment_checkout` | Checkout transactions |
| `equipment_checkin` | Return transactions |
| `events` | Event records |
| `event_equipment_checkout` | Equipment per event |
| `user_wallet` | User wallet codes and QR data |
| `aauth_users` | User accounts |
| `aauth_groups` | Roles and permissions |

---

## Built With api_generator

The scaffolding, CRUD interfaces, and REST API boilerplate in this project were generated using **[api_generator](https://github.com/vipul26singh/api_generator)** — a CodeIgniter-based tool that auto-generates admin panels and REST APIs from database tables.

---

## Contributing

1. Fork the repo
2. Create a branch: `git checkout -b feature/your-feature`
3. Commit: `git commit -m 'Add: your feature'`
4. Open a Pull Request

---

## Keywords

`equipment checkout management` · `qr code wallet php` · `codeigniter equipment tracker` · `asset management web app` · `php event equipment tracking` · `qr wallet system` · `codeigniter admin panel` · `php equipment inventory`
