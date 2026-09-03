# VolCord — Volunteer Coordination Management System

VolCord (VolCoord) is a centralized web platform to organize volunteers, manage activities, and connect people with meaningful community service opportunities.

Instead of scattered spreadsheets and manual coordination, Volunteers create profiles with skills, Customers (organizations / requesters) post opportunities, and Admins approve opportunities and assign volunteers — all in one place.

Built as a simple, no-framework PHP + MySQL app designed to run on XAMPP.

<img width="1898" height="888" alt="image" src="https://github.com/user-attachments/assets/d542e787-37c0-414b-8e3c-14fc2655cb9d" />
<img width="1919" height="926" alt="image" src="https://github.com/user-attachments/assets/94a7e556-94b8-4488-be1d-d9c86c2c9b93" />
<img width="1902" height="928" alt="image" src="https://github.com/user-attachments/assets/f23324f3-791c-4b12-9961-41c578cabec1" />
<img width="1899" height="926" alt="image" src="https://github.com/user-attachments/assets/7cf3781f-39cf-46a9-a173-7ad44fd204ad" />
<img width="1902" height="928" alt="image" src="https://github.com/user-attachments/assets/f6f22a09-3b01-44c6-b0b9-1ef648047bb1" />
<img width="1900" height="929" alt="image" src="https://github.com/user-attachments/assets/863eb234-0576-4979-809f-618d0f5b1088" />
<img width="1902" height="924" alt="image" src="https://github.com/user-attachments/assets/af431cc7-5231-410a-a5b4-c6a355d67c86" />
<img width="1901" height="930" alt="image" src="https://github.com/user-attachments/assets/a1d8f284-449b-4b70-9ba7-42e014d7f1b5" />
<img width="1898" height="924" alt="image" src="https://github.com/user-attachments/assets/27b5ed32-2ff2-4f1a-afb6-1169e1b03daf" />
<img width="1899" height="927" alt="image" src="https://github.com/user-attachments/assets/be89eca7-00b1-4d7b-9a0a-cbec40007ab3" />
<img width="1901" height="923" alt="image" src="https://github.com/user-attachments/assets/1ecbefc8-37e3-48e8-aac4-6817d5f51caf" />
<img width="1897" height="924" alt="image" src="https://github.com/user-attachments/assets/5104459a-179d-4345-a2fc-0ace3df0e8e5" />

## Features

### Volunteer
- Create account with full profile: name, email, phone, gender, address, skills
- Browse approved / open opportunities
- Apply to opportunities with optional message
- Track My Applications with status badges: `pending / accepted / rejected`
- AJAX apply without page reload

### Customer (Organization / Requester)
- Post new volunteer opportunities: title, description, location, date needed, required skills
- View all own postings with status: `pending / approved / rejected / completed`
- See application count per opportunity
- AJAX posting with instant feedback

### Admin
- Dashboard with stats: Total Users, Volunteers, Active Opportunities, Pending Approvals
- Charts (Chart.js):
  - Opportunities by Status (doughnut)
  - Applications last 6 months (bar)
- Review pending opportunities: Approve / Reject (AJAX, no reload)
- Review volunteer applications for approved opportunities: Accept / Reject
- View volunteer skills + contact info inline

### Auth & UX
- Role-based login + session routing: `Volunteer -> volunteerDashboard.php`, `Customer -> customerDashboard.php`, `Admin -> adminDashboard.php`
- Secure password hashing with `password_hash() / password_verify()`
- Prepared statements (mysqli) throughout
- AJAX via `fetch()` to `ajax_handler.php` for login, register, apply, post, reviews
- Responsive landing page + dashboards with vanilla CSS (`style.css`)

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 7.4+ / 8.x (vanilla, no framework, mysqli) |
| Database | MySQL / MariaDB (via XAMPP) |
| Server | Apache (XAMPP) |
| Frontend | HTML5, CSS3 (vanilla `style.css`), Vanilla JavaScript (Fetch API) |
| Charts | Chart.js via CDN (`https://cdn.jsdelivr.net/npm/chart.js`) |
| Auth | PHP Sessions + Cookies, `password_hash` (bcrypt default) |

No build step, no npm/composer dependencies.

## Project Structure

```
volcord/
├── index.php               # Landing page (About, Features, CTA)
├── register.php            # Create Account (Volunteer / Customer)
├── login.php               # Sign In form + AJAX login
├── login_submit.php        # Non-AJAX fallback login handler
├── logout.php              # Destroy session
├── config.php              # MySQLi DB connection
├── database.sql            # DB + tables schema
├── seed_admin.php          # One-time admin seeder
├── ajax_handler.php        # JSON API: login, register, apply, post_opportunity, review_opportunity, review_application
├── volunteerDashboard.php  # Browse + apply + my applications
├── customerDashboard.php   # Post opportunity + my opportunities
├── adminDashboard.php      # Approve opportunities, assign volunteers, stats + charts
├── apply.php               # Non-AJAX fallback for volunteer apply
├── post_opportunity.php    # Non-AJAX fallback for posting
├── review_opportunity.php  # Non-AJAX fallback approve/reject opportunity
├── review_application.php  # Non-AJAX fallback accept/reject application
├── style.css               # All styling
├── hero.jpg, Home.png, Login.png, adminDashboard.png, adminDashboard2.png # Screenshots / assets
└── README.md
```

## Database Schema

Database: `volcord` — see `database.sql` for full DDL.

- `users(id, full_name, email UNIQUE, password_hash, role, skills, phone, gender, address, created_at)`
  - `role`: `Volunteer | Customer | Admin` (register only allows Volunteer/Customer, Admin via seeder)
- `opportunities(id, customer_id FK -> users.id, title, description, location, required_skills, needed_date, status, created_at, approved_by FK, approved_at)`
  - `status`: `pending / approved / rejected / completed`
- `applications(id, opportunity_id FK, volunteer_id FK, status, message, applied_at, UNIQUE(opportunity_id, volunteer_id))`
  - `status`: `pending / accepted / rejected`

Entity flow: `Customer posts (pending) -> Admin approves -> Volunteer applies (pending) -> Admin accepts -> done`

## Prerequisites

- [XAMPP](https://www.apachefriends.org/) with Apache + MySQL (or any PHP + MySQL stack)
- PHP 7.4 or higher (8.x recommended)
- Modern browser

## Installation

1. **Get the code into `htdocs`:**
   ```bash
   # Option A: clone
   cd C:\xampp\htdocs
   git clone 

   # Option B: copy this folder to
   C:\xampp\htdocs\volcord
   ```

2. **Start servers:**
   Open XAMPP Control Panel -> Start `Apache` and `MySQL`.

3. **Create database:**
   - Go to `http://localhost/phpmyadmin`
   - Import `database.sql` (or run manually):
   ```sql
   CREATE DATABASE IF NOT EXISTS volcord;
   ```
   Then import `C:\xampp\htdocs\volcord\database.sql`.

   Or via shell:
   ```bash
   mysql -u root < C:\xampp\htdocs\volcord\database.sql
   ```

4. **Verify DB config** in `config.php`:
   ```php
   $conn = new mysqli("localhost", "root", "", "volcord");
   ```
   Change host/user/pass/dbname if your MySQL credentials differ.

5. **Create admin account (one time):**
   Visit `http://localhost/volcord/seed_admin.php`
   ```
   Email: admin@volcord.local
   Password: Admin@1234
   ```
   > Delete or protect `seed_admin.php` after first use.

6. **Run the app:**
   ```
   http://localhost/volcord/
   ```
   Register as Volunteer/Customer, or sign in as Admin.

## Usage

1. `/` — landing, click Get Started / Sign In
2. `/register.php` — choose Role. Volunteers must list skills.
3. `/login.php` — auto-routed by role after login
4. Customer: `customerDashboard.php` -> Post a New Opportunity -> waits for `pending` approval
5. Admin: `adminDashboard.php` -> Pending Approval -> Approve -> appears in Volunteer dashboard
6. Volunteer: `volunteerDashboard.php` -> Open Opportunities -> Apply -> track in My Applications
7. Admin: Approved Opportunities table -> Accept / Reject applicants

## Configuration

All config is in `config.php` — single mysqli connection shared via `require_once "config.php"`. No `.env` needed.

To change admin seed credentials, edit `seed_admin.php` before first run.

## Screenshots

- `Home.png` — landing page
- `Login.png` — sign in
- `adminDashboard.png` / `adminDashboard2.png` — admin stats, charts, approvals
- `hero.jpg` — hero background

## Troubleshooting

- `Connection failed`: MySQL not running, or wrong creds in `config.php`.
- Blank page / redirect to `login.php`: session role mismatch — sign in with correct role account.
- `An admin account already exists`: `seed_admin.php` runs only once by design.
- AJAX `Network error`: check `ajax_handler.php?action=...` path, Apache running, browser console for JSON errors.
- Chart.js not loading: requires internet for CDN.

## Future Improvements

- Email notifications for approvals / acceptance
- Search / filter by skills + location
- Opportunity completion + volunteer hours tracking
- Password reset, profile edit, pagination

## License

No license specified. For class / portfolio use. Add a `LICENSE` file if you plan to open-source.
