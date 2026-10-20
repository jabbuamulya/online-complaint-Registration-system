# Online Complaint Registration System (Civic Resolve)

A complete, full-stack web application designed for transparent, accountable, and citizen-centric public grievance redressal. The platform enables citizens to register municipal complaints, track progress in real-time with unique IDs, and hold administrative departments accountable.

> **SDG 16 Alignment**: Aligned with the **United Nations Sustainable Development Goal 16 (Peace, Justice and Strong Institutions)**, particularly **Target 16.6** (*Develop effective, accountable and transparent institutions at all levels*) and **Target 16.10** (*Ensure public access to information*).

---

## 🌟 Key Features

### 👤 Citizen / User Portal
* **User Registration & Secure Authentication**: Hashed passwords (`password_hash`), session validation, input sanitization.
* **Citizen Dashboard**: Visual count cards for Total, Pending, In-Progress, and Resolved complaints with recent activity feed.
* **Lodge New Complaint**:
  - Title, Category, Detailed description, Exact location/landmark.
  - File upload support (Images: JPG, PNG / Documents: PDF, DOC, max 5MB).
  - Automatic unique Complaint ID generation (e.g. `CMP20260001`).
* **Instant Confirmation**: Clear display of the Complaint ID with single-click clipboard copy.
* **Track Complaint**: Public and user-specific tracking with a 5-stage visual progress timeline (`Submitted` → `Under Review` → `Assigned` → `In Progress` → `Resolved`).
* **My Complaints**: Comprehensive grievance history with search by ID/Title and filtering by status and category.
* **Interactive Complaint Details**: Complete case overview, official administrative responses, chronological audit trail, and ability for the citizen to post supplementary information or updates.
* **Citizen Profile**: Update contact phone number, address, and secure password change.

### 🛡️ Administrator Portal
* **Separate Admin Authentication**: Isolated login screen with role-based session protection.
* **Executive Dashboard**: Real-time counter metrics for Total, New (Submitted), Under Review, In Progress, Resolved, and Rejected complaints.
* **Manage Complaints**: Search and filter by category, workflow status, and date ranges.
* **Officer Assignment**: Dynamic allocation of grievances to municipal field officers and department engineers.
* **Status Updates & Official Responses**: Update statuses across all 6 stages and provide official citizen-facing explanations.
* **Fast Resolution / Rejection**: One-click modal actions to mark complaints as `Resolved` (with completion notes and timestamp) or `Rejected` (with mandatory justification).
* **Citizen Management**: Directory of registered citizens with contact details and count of filed complaints.
* **Officer Directory**: Add, edit, and manage department officers across municipal divisions.
* **Visual Reports & Analytics**: Interactive charts using Chart.js:
  - Complaints by Category (Bar Chart)
  - Complaints by Status (Doughnut Chart)
  - Monthly Grievance Intake Trends (Line Chart)
  - Resolved vs Pending Ratio (Pie Chart)
  - Category SLA Redressal Table with print-ready view.

---

## 🛠️ Technology Stack

* **Frontend**: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5.3 (CDN), FontAwesome 6.5 (CDN), Chart.js (CDN)
* **Backend**: PHP 7.4+ / PHP 8.x (using PDO with prepared statements)
* **Database**: MySQL / MariaDB
* **Development Environment**: XAMPP (Apache + MySQL)

---

## 📁 Project Structure

```text
online complaint/
│
├── index.php                      # Public Landing & Showcase Page
├── about.php                      # About & SDG 16 Charter Page
├── login.php                      # Citizen Login
├── register.php                   # Citizen Registration
├── logout.php                     # Citizen Logout
├── track.php                      # Public Complaint Tracker
│
├── includes/
│   ├── header.php                 # Public Header with SDG 16 Strip & Navigation
│   └── footer.php                 # Public Footer with Helpline & Quick Links
│
├── user/                          # Citizen Module
│   ├── dashboard.php              # Citizen Overview & Metrics
│   ├── submit_complaint.php       # Lodge New Grievance
│   ├── complaints.php             # Grievance History & Filters
│   ├── complaint_details.php      # Full Details, Timeline & Add Info
│   ├── profile.php                # Citizen Profile & Password Change
│   └── includes/
│       ├── user_header.php        # User Navigation & Layout
│       └── user_footer.php        # User Layout Footer
│
├── admin/                         # Administrative Module
│   ├── login.php                  # Admin Login
│   ├── dashboard.php              # Admin Overview & 6-Status Metrics
│   ├── complaints.php             # Manage All Grievances & Filter
│   ├── complaint_details.php      # Assign Officer, Update Status, Remarks
│   ├── users.php                  # Manage Registered Citizens
│   ├── officers.php               # Manage Departmental Officers
│   ├── reports.php                # Visual Charts (Chart.js) & Audit Table
│   ├── logout.php                 # Admin Logout
│   └── includes/
│       ├── admin_header.php       # Admin Navbar & Sidebar Layout
│       └── admin_footer.php       # Admin Layout Footer
│
├── config/
│   ├── database.php               # PDO MySQL Connection & Connection Guard
│   └── helpers.php                # Auth checks, URL helper, badges & utils
│
├── assets/
│   ├── css/
│   │   └── style.css              # Civic design system & status timeline
│   └── js/
│       └── script.js              # File upload preview, copy ID & alerts
│
├── uploads/                       # Directory for supporting images/documents
│   └── .gitkeep
│
├── database/
│   └── complaint_system.sql       # MySQL Schema & Seed Data
│
└── README.md                      # Documentation & Setup Instructions
```

---

## 🚀 Setup & Execution Instructions (XAMPP)

Follow these simple steps to run the application on your computer:

### Step 1: Install & Start XAMPP
1. Download and install **XAMPP** from [apachefriends.org](https://www.apachefriends.org/) (if not already installed).
2. Open the **XAMPP Control Panel**.
3. Click **Start** for both **Apache** and **MySQL**. Both modules should turn green.

### Step 2: Deploy Project to `htdocs`
1. Copy this project folder into your XAMPP web root:
   - Path: `C:\xampp\htdocs\complaint-system`
   *(Or keep folder name as `online-complaint` or `complaint-system` — the system automatically detects base URLs dynamically).*

### Step 3: Import the Database
1. Open your web browser and go to:
   `http://localhost/phpmyadmin`
2. In the left sidebar, click **New**.
3. Create a database named:
   `complaint_system`
   *(Collation: `utf8mb4_unicode_ci` or standard default).*
4. Click on the newly created `complaint_system` database.
5. Go to the **Import** tab at the top.
6. Click **Choose File** / **Browse** and select:
   `C:/xampp/htdocs/complaint-system/database/complaint_system.sql`
7. Click the **Go** / **Import** button at the bottom.
8. You should see a green success message indicating that all 5 tables (`users`, `admins`, `officers`, `complaints`, `complaint_updates`) and sample records were created.

### Step 4: Verify Database Connection
Open `config/database.php`. The default credentials are pre-configured for standard XAMPP:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'complaint_system');
define('DB_USER', 'root');
define('DB_PASS', ''); // default XAMPP empty password
```

### Step 5: Launch the Application
Open your browser and navigate to:
```text
http://localhost/complaint-system/
```
*(If your folder is named `online complaint`, use `http://localhost/online%20complaint/`).*

---

## 🔑 Default Login Credentials

### 🛡️ Administrator Account
* **URL**: `http://localhost/complaint-system/admin/login.php`
* **Username**: `admin`
* **Password**: `admin123`

### 👤 Sample Citizen Account
* **URL**: `http://localhost/complaint-system/login.php`
* **Email**: `rahul@example.com`
* **Password**: `user123`
*(Or register a new account on `register.php`).*

---

## 🔍 Preloaded Test Tracking IDs

You can immediately test public tracking on `track.php` using any of these pre-seeded IDs:

| Complaint ID | Title | Category | Status |
| :--- | :--- | :--- | :--- |
| `CMP20260001` | Deep pothole causing accidents near main junction | Roads | **Resolved** |
| `CMP20260002` | Low water pressure and muddy water supply | Water Supply | **In Progress** |
| `CMP20260003` | Street light not working for two weeks | Street Lights | **Assigned** |
| `CMP20260004` | Uncollected garbage accumulating near community park | Garbage | **Submitted** |

---

## 🛡️ Security Implementations

1. **Password Hashing**: Citizen and administrator passwords use PHP's `password_hash()` with `PASSWORD_DEFAULT` (Bcrypt).
2. **SQL Injection Defense**: 100% of database queries use PDO prepared statements with parameter binding.
3. **Cross-Site Scripting (XSS) Mitigation**: All user inputs rendered in HTML are sanitized through the `e()` helper function (`htmlspecialchars` with `ENT_QUOTES`).
4. **Role-Based Access Control (RBAC)**: Unauthenticated requests to `/user/*` or `/admin/*` are immediately redirected with warning flash alerts.
5. **Secure File Upload**: Validation of file size (max 5MB), MIME types, and whitelist extensions (`.jpg`, `.jpeg`, `.png`, `.pdf`, `.doc`, `.docx`). Uploaded files are renamed with unique randomized cryptographic hashes.

---

## 🎓 Academic Relevance (B.Tech Web Technology)

This application serves as an ideal final project for **Web Technologies / Full Stack Web Development** showcasing:
* MVC-inspired modular architecture with separation of config, views, and controllers.
* Relational database design with Foreign Keys and Referential Integrity (`ON DELETE CASCADE`, `ON DELETE SET NULL`).
* Asynchronous and real-time visualization with Chart.js and CSS3 animations.
* Direct civic and societal contribution towards **SDG 16**.
