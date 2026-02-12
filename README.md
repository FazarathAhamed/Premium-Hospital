# HealthyLife Hospital Management System

A comprehensive, role-based hospital management system built with **Core PHP**, **MySQL**, and **Vanilla CSS**. Designed for easy deployment on free hosting platforms like **InfinityFree**.

---

## 🌟 Features

### 🔐 **Multi-Role Authentication**
- **Admin**: Manage doctors, patients, staff, appointments, and billing
- **Doctor**: View schedule, manage patients, upload medical reports
- **Patient**: Book appointments, view reports, submit feedback, pay bills
- **Receptionist**: Manage appointments, assign rooms, handle inquiries

### 💎 **Premium UI/UX**
- **Role-based color themes** (Deep Navy for Admin, Teal for Doctor, Blue for Patient, Purple for Receptionist)
- **Responsive design** (mobile, tablet, desktop)
- **Smooth animations** and micro-interactions
- **Modern typography** (Google Fonts - Inter)
- **Font Awesome icons**

### 🏥 **Core Functionality**
- ✅ **Real-time database updates** (no static data)
- ✅ **Appointment booking** with auto-billing
- ✅ **Medical report uploads** with file storage
- ✅ **Room management** (assign/release)
- ✅ **Feedback & inquiry system** with replies
- ✅ **Billing & payments** (cash/card)
- ✅ **Session management** with 30-min timeout
- ✅ **Role-based access control**

---

## 📁 Project Structure

```
hospital-system/
│
├── index.php                       # Home page
├── index.css                       # Home page CSS
├── script.js                       # Home page JS
│
├── auth/                           # Authentication
│   ├── login.php
│   ├── register.php                # Patient registration
│   ├── forgot_password.php
│   ├── logout.php
│   └── auth.css
│
├── config/                         # Configuration
│   ├── db.php                      # Database connection + dynamic APP_URL
│   ├── session.php                 # Session management
│   └── browser_cache_control.php   # Cache control
│
├── middleware/                     # Security
│   ├── check_login.php             # Login required
│   └── check_role.php              # Role-based access
│
├── admin/                          # Admin module
│   ├── dashboard.php
│   ├── manage_doctors.php
│   ├── manage_patients.php
│   ├── manage_staff.php
│   ├── appointments.php
│   ├── billing_reports.php
│   └── admin.css
│
├── patient/                        # Patient module
│   ├── dashboard.php
│   ├── book_appointment.php
│   ├── appointment_history.php
│   ├── medical_reports.php
│   ├── feedback.php
│   ├── billing.php
│   └── patient.css
│
├── doctor/                         # Doctor module
│   ├── dashboard.php
│   ├── today_schedule.php
│   ├── patients_list.php
│   ├── patient_details.php
│   ├── upload_reports.php
│   └── doctor.css
│
├── receptionist/                   # Receptionist module
│   ├── dashboard.php
│   ├── manage_appointments.php
│   ├── assign_rooms.php
│   ├── inquiries.php
│   └── receptionist.css
│
├── includes/                       # Shared UI
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
│   └── includes.css
│
├── uploads/                        # File uploads
│   └── reports/
│
├── database/                       # Database
│   ├── schema.sql                  # InfinityFree-compatible schema
│   └── seed.sql                    # 20 sample users
│
└── README.md
```

---

## 🚀 Installation

### **Local Setup (XAMPP)**

1. **Clone/Download** this project to `C:\xampp\htdocs\Healthylife`

2. **Start XAMPP** and run Apache + MySQL

3. **Create Database**:
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Create database: `healthylife_db`
   - Import `database/schema.sql`
   - Import `database/seed.sql`

4. **Configure Database** (if needed):
   - Edit `config/db.php` and update:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'healthylife_db');
     ```

5. **Access the System**:
   - Homepage: `http://localhost/Healthylife`
   - Login: `http://localhost/Healthylife/auth/login.php`

---

### **InfinityFree Hosting Setup**

1. **Upload Files**:
   - Upload all files via **File Manager** or **FTP**
   - Place in `htdocs` or your domain root

2. **Create Database**:
   - Go to **MySQL Databases** in control panel
   - Create a new database
   - Import `database/schema.sql` via phpMyAdmin
   - Import `database/seed.sql`

3. **Update Database Config**:
   - Edit `config/db.php`:
     ```php
     define('DB_HOST', 'sql123.infinityfree.com'); // Your DB host
     define('DB_USER', 'epiz_12345678');           // Your DB username
     define('DB_PASS', 'your_password');           // Your DB password
     define('DB_NAME', 'epiz_12345678_healthylife'); // Your DB name
     ```

4. **Set Permissions**:
   - Set `uploads/reports/` folder to **755** or **777**

5. **Access**:
   - Visit your domain: `https://yourdomain.infinityfreeapp.com`

---

## 👥 Default Login Credentials

All passwords: **`password123`**

| Role          | Email                          |
|---------------|--------------------------------|
| **Admin**     | admin@healthylife.com          |
| **Doctor**    | james.wilson@healthylife.com   |
| **Patient**   | john.doe@healthylife.com       |
| **Receptionist** | nadia.f@healthylife.com     |

---

## 🗄️ Database Schema

### **Users Table**
- `id`, `full_name`, `email`, `password`, `role`, `phone`, `gender`, `date_of_birth`, `address`, `specialization`, `is_active`

### **Appointments Table**
- `id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `reason`, `notes`

### **Medical Reports Table**
- `id`, `patient_id`, `doctor_id`, `report_title`, `report_description`, `file_path`

### **Rooms Table**
- `id`, `room_number`, `room_type`, `status`, `patient_id`

### **Billing Table**
- `id`, `patient_id`, `appointment_id`, `amount`, `payment_method`, `payment_status`, `paid_at`

### **Feedback Table**
- `id`, `patient_id`, `subject`, `message`, `type`, `reply`, `replied_by`, `replied_at`

### **Departments Table**
- `id`, `name`, `description`

---

## 🎨 Design System

### **Color Themes**
- **Admin**: Deep Navy (`#1a1f3d`) + Gold Accent (`#c8a951`)
- **Doctor**: Teal (`#0d4f4f`) + Medical Green (`#14b8a6`)
- **Patient**: Deep Blue (`#1b3a5c`) + Sage (`#6bc5a0`)
- **Receptionist**: Purple (`#3d1a3d`) + Violet (`#a855f7`)

### **Typography**
- **Font**: Inter (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700, 800, 900

### **Components**
- Stat cards with gradient borders
- Modal overlays for forms
- Responsive tables
- Badge system for status
- Animated alerts (auto-dismiss)

---

## 📱 Responsive Breakpoints

- **Desktop**: > 900px
- **Tablet**: 768px - 900px
- **Mobile**: < 768px

---

## 🔒 Security Features

- ✅ **Password hashing** (bcrypt)
- ✅ **Prepared statements** (SQL injection prevention)
- ✅ **Session timeout** (30 minutes)
- ✅ **Role-based access control**
- ✅ **Browser cache control** (prevent back-button after logout)
- ✅ **XSS prevention** (htmlspecialchars)

---

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+ (Core PHP, no frameworks)
- **Database**: MySQL 5.7+ (InnoDB, utf8mb4)
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (ES6)
- **Icons**: Font Awesome 6.5.1
- **Fonts**: Google Fonts (Inter)

---

## 📝 Key Features Explained

### **Real-Time Updates**
- All data is fetched from the database
- After booking an appointment → instantly appears in lists
- After payment → billing status updates immediately
- No page refresh required for most actions

### **File Upload System**
- Doctors can upload medical reports (PDF, JPG, PNG, DOC)
- Files stored in `uploads/reports/`
- Database stores file path reference
- Patients can download reports

### **Appointment Workflow**
1. Patient books appointment → status: `pending`
2. Admin/Receptionist approves → status: `approved`
3. Doctor marks complete → status: `completed`
4. Auto-creates billing record on booking

### **Feedback System**
- Patients submit feedback/inquiries
- Receptionists view and reply
- Patients see replies in their feedback page

---

## 🐛 Troubleshooting

### **Database Connection Error**
- Check `config/db.php` credentials
- Ensure MySQL is running
- Verify database name exists

### **File Upload Not Working**
- Check `uploads/reports/` folder exists
- Set folder permissions to 755 or 777
- Check PHP `upload_max_filesize` in php.ini

### **Session Timeout Too Fast**
- Edit `config/session.php`
- Change `$timeout = 1800;` (30 minutes)

### **Blank Page / White Screen**
- Enable error reporting in `config/db.php`:
  ```php
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  ```

---

## 📄 License

This project is open-source and free to use for educational purposes.

---

## 👨‍💻 Developer Notes

- **Clean Code**: Well-commented, student-friendly
- **No Frameworks**: Pure PHP for learning
- **Hosting-Friendly**: Works on free hosting (InfinityFree, 000webhost)
- **Scalable**: Easy to add new features
- **Modular**: Each role in separate folder

---

## 🎯 Future Enhancements

- [ ] Email notifications (appointment confirmations)
- [ ] SMS reminders
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Dark mode toggle
- [ ] Export reports to PDF
- [ ] Calendar view for appointments

---

## 📧 Support

For issues or questions, please check the code comments or contact your instructor.

---

**Built with ❤️ for students learning web development**
