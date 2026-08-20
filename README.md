# Kamadhenu Goushala — Full-Stack Web Application & Management System

A modern, responsive, and production-ready Web Application and Admin Panel built for **Kamadhenu Goushala**, dedicated to the conservation, protection, and ethical healthcare of sacred indigenous Indian cows (Desi Gau Vansh).

---

## 🌟 Key Features

### 🐮 Public Portal
- **Hero & Heritage Overview**: Immersive hero section, sanctuary video showcase, dynamic counter metrics, and Vedic heritage pillars.
- **Resident Cows Directory**: Filterable directory with live search, breed selector, gender filter, health status tags, and individual cow story profile pages.
- **Indigenous Cow Breeds Encyclopedia**: Detailed guides for indigenous breeds (Gir, Sahiwal, Red Sindhi, Tharparkar, Rathi, Kankrej, Ongole, Hallikar, Punganur, Malnad Gidda).
- **Gau Seva Catalog**: Seva contribution packages with suggested amounts and 80G tax benefit information.
- **Instant Donation Portal with 80G Receipts**:
  - Preset tiles (₹501, ₹1001, ₹2501, ₹5001, ₹10001) & custom amounts.
  - Razorpay payment gateway integration with HMAC-SHA256 signature verification.
  - Development simulation mode when live gateway keys are not provided.
  - Automatic generation and instant printing of official **Section 80G Tax Exemption Receipts**.
- **Cow Adoption & Sponsorship**: Flexible monthly sponsorship programs (1, 3, 6, 12 months) with dedicated cow selection.
- **Organic Goushala E-Store**:
  - Pure Desi Cow products (A2 Bilona Ghee, Gomutra Arka, Vermicompost, Agnihotra Cakes, Dhoop Batti).
  - Client-side Cart & instant checkout workflow.
- **Photo Gallery**: Filterable sanctuary photo gallery with high-resolution lightbox preview.
- **Vedic Knowledge Hub (Blog)**: Articles on Vedic cow wisdom, A2 milk science, and organic farming.
- **Events & Celebrations**: Gopashtami Mahotsav, Gau Puja schedules, and volunteer registration.
- **Contact & Map**: Inquiry submission form, interactive Google Map embed, and direct WhatsApp helpline integration.
- **SEO Ready**: Semantic HTML5 markup, Open Graph tags, canonical URLs, `robots.txt`, and XML sitemap.

### 🛡️ Admin Management Panel (`/admin`)
- **Executive Dashboard**:
  - Live KPI stats (Total Cows, Active Cows, Collections, Month Seva, Active Sponsors, Orders, Inquiries).
  - Monthly Donation Trends Chart via Chart.js.
  - Recent Donations and Inquiry Tables.
  - Audit Trail of administrator activities.
- **Resident Cows CRUD**: Add/Edit cow profiles with Multer file uploads or image URLs, tag numbers, health statuses, and life stories.
- **Indigenous Breeds CRUD**: Manage breed information, origins, milk yield data, and photos.
- **Seva Packages CRUD**: Create and update seva tiers, suggested pricing, and icon badges.
- **Donation Management**: Searchable ledger with date range filters, payment status filters, and one-click **CSV export**.
- **Sponsorships & Sponsors**: Real-time tracking of active cow adoptions, end dates, and sponsor PAN details.
- **Store & Inventory Management**: Product catalog with stock quantity tracking and category assignment.
- **Order Processing**: Customer orders status pipeline (`Pending` → `Confirmed` → `Processing` → `Shipped` → `Delivered` → `Cancelled`).
- **Vedic Blog CMS**: Full article drafting, categories, featured image uploads, and publishing controls.
- **Events & Celebrations CMS**: Schedule upcoming festivals and archive past celebrations.
- **Multi-Photo Gallery Uploader**: Bulk upload sanctuary photos with category tags.
- **Testimonials CMS**: Manage devotee ratings, testimonials, and avatars.
- **Journey / Timeline CMS**: Edit sanctuary milestones and founding history.
- **Contact Inquiries Inbox**: Review incoming messages, update status (`New`, `Read`, `Replied`), and write admin notes.
- **Newsletter Subscribers**: Subscriber registry with **CSV Export**.
- **Global Website Settings**: Update organization details, phone numbers, UPI ID, 80G tax text, and social links.
- **Admin Profile & Security**: Password hashing with `bcryptjs` and session-based security.

---

## 🛠️ Technology Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript (ES6+), Bootstrap 5.3.3, Bootstrap Icons 1.11.3, Chart.js.
- **Backend**: Node.js, Express.js, REST API architecture.
- **Database**: MySQL 8.0+ using `mysql2/promise` connection pooling with parameterized queries.
- **Authentication**: `express-session` + `bcryptjs` (salt rounds: 10).
- **File Uploads**: `multer` with disk storage, file validation, and automated storage under `/public/uploads/`.
- **Payment Processing**: Razorpay Node.js SDK + HMAC-SHA256 crypto verification with fallback development simulator.
- **Security**: `helmet`, `cors`, and `express-rate-limit`.

---

## 📋 Prerequisites

Before running the application, ensure you have:
1. **Node.js**: Version 18.x or higher installed ([Download Node.js](https://nodejs.org/)).
2. **MySQL Server**: MySQL 8.0+ running locally (via **XAMPP**, **WAMP**, **MySQL Community Server**, or Docker).

---

## 🚀 Quick Start Guide

### 1. Configure Environment Variables
Copy `.env.example` to `.env` (already present in the project):
```env
PORT=3000
NODE_ENV=development

# MySQL Database Settings (Default XAMPP credentials)
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=kamadhenu_goushala

# Session Configuration
SESSION_SECRET=kamadhenu_sacred_secret_key_2026_jwt_token_secure

# Optional Razorpay Gateway Credentials
RAZORPAY_KEY_ID=rzp_test_placeholder
RAZORPAY_KEY_SECRET=rzp_secret_placeholder
```

### 2. Initialize Database & Seed Demo Data
Ensure MySQL is running in XAMPP / MySQL service, then execute:
```bash
npm run seed
```
This automated command will:
- Connect to MySQL and create the `kamadhenu_goushala` database if it doesn't exist.
- Execute `database/schema.sql` (creates all 22 relational tables, foreign keys, and indexes).
- Execute `database/seed.sql` with realistic demo data for cows, breeds, seva, blogs, gallery, products, timeline, and settings.
- Hash and insert the default administrator account.

### 3. Create Additional Admin Users (Optional)
You can create new administrators via CLI:
```bash
npm run create-admin
# Or pass arguments directly:
node scripts/create-admin.js "Swami Ramanuj" "swami@kamadhenugoushala.org" "Secure@2026" "admin"
```

### 4. Start the Application Server
```bash
# Production mode
npm start

# Development mode (with auto-restart)
npm run dev
```

The application will be live at:
- **Public Portal**: [http://localhost:3000](http://localhost:3000)
- **Admin Portal**: [http://localhost:3000/admin](http://localhost:3000/admin)

---

## 🔑 Default Administrator Credentials

| Role | Email | Password |
| :--- | :--- | :--- |
| **Super Admin** | `admin@kamadhenugoushala.org` | `Admin@123` |

---

## 💳 Payment Gateway & Development Simulation

1. **Razorpay Live / Test Mode**:
   - Set valid `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET` in `.env`.
   - The frontend checkout will open the official Razorpay payment modal and verify the digital signature upon completion.
2. **Development Simulation Mode (Zero Configuration)**:
   - When placeholder or empty keys are configured in `.env`, the backend automatically generates a simulated development order (`order_dev_...`).
   - The frontend handles dev verification seamlessly, records the donation / sponsorship / order into MySQL, and immediately produces an official **80G receipt**.

---

## 🗄️ Database Architecture (22 Relational Tables)

1. `users` — Administrator and manager accounts with bcrypt password hashes.
2. `breeds` — Indigenous Indian cow breeds encyclopedia.
3. `cows` — Resident cow profiles, health conditions, rescue stories, and tags.
4. `cow_gallery` — Additional photo galleries linked to individual cows.
5. `seva_types` — Gau Seva packages and suggested contribution amounts.
6. `donations` — Complete ledger of donations, donor PAN, Razorpay transaction IDs, and 80G receipts.
7. `sponsors` — Registered cow guardians and sponsors directory.
8. `cow_sponsorships` — Active and historical cow sponsorships and durations.
9. `product_categories` — Store category classifications.
10. `products` — Organic Goushala products catalog and stock counts.
11. `orders` — Customer store orders and delivery details.
12. `order_items` — Line items purchased in each store order.
13. `blog_categories` — Vedic wisdom and organic farming categories.
14. `blogs` — Articles with full HTML content, author attribution, and slugs.
15. `events` — Sanctuary gatherings, Gopashtami Mahotsav, and workshops.
16. `gallery_categories` — Media categorizations.
17. `gallery` — Sanctuary photo gallery items with image URLs.
18. `testimonials` — Devotee and visitor feedback and star ratings.
19. `timeline` — Chronological history and milestones of the sanctuary.
20. `contact_messages` — Inbound visit and puja inquiries.
21. `newsletter_subscribers` — Devotee email subscriptions.
22. `settings` — Key-value registry for dynamic website configuration.
23. `activity_logs` — Administrator audit logs.

---

## 🔒 Security Best Practices Implemented

- **Session Security**: `httpOnly`, `sameSite: 'lax'`, and secure cookies in production.
- **SQL Injection Prevention**: Parameterized SQL queries throughout all endpoints (`mysql2/promise`).
- **File Upload Protection**: Multer whitelist filtering restricted to `image/jpeg`, `image/png`, `image/webp`, and `image/gif` with a 5MB size limit.
- **Brute-Force Rate Limiting**: Strict rate limits on `/api/auth/login` (5 attempts per 15 minutes).
- **HTTP Header Hardening**: Secured with `helmet` and sanitization middleware.

---

## 📄 License & Attribution

Designed and developed with devotion for **Kamadhenu Goushala Trust** © 2026. All rights reserved.
