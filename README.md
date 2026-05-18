# WorkSpace 🏢

> A premium co-working space booking & store platform built with PHP, MySQL, and vanilla JavaScript — based in Alexandria, Egypt.

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Custom%20Design-1572B6?logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?logo=javascript&logoColor=black)

---

## ✨ Features

- **Room Booking** — Individual desks and group rooms with availability checking and conflict detection
- **Online Store** — Browse and order beverages, stationery, tech accessories, and snacks
- **Contact Form** — Inquiry form with subject tagging and DB persistence
- **Demo Mode** — Full site works without a database (fallback demo data included)
- **Responsive Design** — Mobile-friendly layout with a clean warm-copper aesthetic
- **Scroll Animations** — Intersection Observer–based reveal effects and animated stat counters
- **Live Clock** — Real-time date/time display on the homepage

---

## 📁 Project Structure

```
workspace/
├── index.php           # Homepage — hero, stats, features, room preview
├── rooms.php           # All rooms with type filter
├── booking.php         # Individual & group booking forms
├── store.php           # Product catalog with cart and order form
├── contact.php         # Contact form with hours and map placeholder
├── nav.php             # Shared navigation bar (included via PHP)
├── footer.php          # Shared footer (included via PHP)
├── config.php          # DB config, connection, and demo data fallback
├── database.sql        # Full schema + seed data
├── css/
│   └── style.css       # Global stylesheet (CSS custom properties, components)
├── js/
│   └── main.js         # Global JS (clock, scroll reveal, counters, cart, filters)
└── img/
    ├── grean_tea.jpg
    ├── Latte_and_dark_coffee.jpg
    ├── mechanical_pencil.jpg
    ├── note_book_a5.jpg
    ├── protein_bar.jpg
    └── usb_c_cable.jpg
```

---

## 🚀 Getting Started

### Prerequisites

- PHP 8.0+
- MySQL 5.7+ (optional — the site runs in demo mode without it)
- A local server environment: [XAMPP](https://www.apachefriends.org/), [Laragon](https://laragon.org/), or similar

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-username/workspace.git
   cd workspace
   ```

2. **Move to your web server's root**
   ```
   # XAMPP example:
   cp -r workspace/ /xampp/htdocs/workspace
   ```

3. **Set up the database** *(optional — skip for demo mode)*
   - Open phpMyAdmin or your MySQL client
   - Run the provided SQL file:
     ```bash
     mysql -u root -p < database.sql
     ```
   - This creates the `workspace_db` database, all tables, and seeds initial room/product data.

4. **Configure the database connection**

   Open `config.php` and update if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'workspace_db');
   ```

5. **Add product images**

   Place the product images inside an `img/` folder at the project root. The expected filenames are:
   ```
   img/Latte_and_dark_coffee.jpg
   img/grean tea.jpg
   img/note book a5.jpg
   img/mechanical pencil.jpg
   img/usb c cable.jpg
   img/protein bar.jpg
   ```

6. **Visit the site**
   ```
   http://localhost/workspace/
   ```

---

## 🗃️ Database Schema

| Table | Description |
|---|---|
| `rooms` | Room catalog (name, type, capacity, price, amenities) |
| `individual_bookings` | Single-seat bookings with scheduled start time |
| `group_bookings` | Group room bookings with start/end times, deposit, and overlap detection |
| `products` | Store products with category, price, and stock |
| `orders` | Customer orders with payment method and delivery address |
| `order_items` | Line items per order (product, quantity, unit price) |
| `contacts` | Contact form submissions |

---

## 📄 Pages Overview

### `/index.php` — Home
Live clock bar, animated hero section, stats counters, feature cards, room preview grid, and a CTA banner.

### `/rooms.php` — Rooms
Full room catalog with Individual/Group filter buttons. Each card shows amenities, capacity, and a direct booking link.

### `/booking.php` — Book Now
Tab-based booking form:
- **Individual** — pick a room, enter name/phone/email, select check-in time. Validates against room capacity.
- **Group** — pick a room, set start/end times (overlap detection), auto-calculates 25% deposit.

### `/store.php` — Store
Product grid with category filter, add-to-cart functionality, and a sticky cart sidebar with order form (name, phone, address, payment method).

### `/contact.php` — Contact
Subject chip selector, contact form, opening hours grid, and a map placeholder for the Smouha, Alexandria location.

---

## 🎨 Design System

Defined in `css/style.css` via CSS custom properties:

| Variable | Value | Usage |
|---|---|---|
| `--accent` | `#b87333` | Warm copper — buttons, highlights |
| `--accent2` | `#2c3e50` | Deep navy — dark sections, footer |
| `--bg` | `#f7f5f1` | Warm off-white page background |
| `--ink` | `#1c1c1c` | Primary text |
| `--muted` | `#6b6b6b` | Secondary/caption text |
| `--border` | `#e8e2d6` | Card and input borders |

Fonts: [Playfair Display](https://fonts.google.com/specimen/Playfair+Display) (headings) + [DM Sans](https://fonts.google.com/specimen/DM+Sans) (body) via Google Fonts.

---

## ⚙️ JavaScript Modules (`js/main.js`)

| Module | Description |
|---|---|
| Live clock | Updates `#live-clock` every second |
| Nav highlight | Marks the current page link as active |
| Scroll reveal | Fades in `.reveal` elements via Intersection Observer |
| Stat counters | Animates `.counter` numbers when scrolled into view |
| Mobile nav | Burger menu toggle |
| Form validation | Highlights empty required fields on submit |
| Room filter | Shows/hides `.room-card` elements by type |
| Store cart | In-memory cart with toast notifications |

---

## 🔒 Booking Logic

**Individual rooms** — checks how many active bookings exist for the same room and time slot against the room's `capacity`. Rejects if full.

**Group rooms** — checks for any overlapping booking using a three-condition SQL overlap query before inserting. Returns a clear error if the slot is taken.

---

## 📦 Demo Mode

If the database is unavailable or not configured, `config.php` sets `DB_CONNECTED = false` and all pages fall back to hardcoded `$demo_rooms` and `$demo_products` arrays. Forms still submit and return a demo success message — no DB writes occur.

---

## 🤝 Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you'd like to change.

---

## 📜 License

[MIT](LICENSE)

---

> Built for WorkSpace · Smouha, Alexandria, Egypt 🇪🇬
