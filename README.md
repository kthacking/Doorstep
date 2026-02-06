# Doorstep Service Booking Platform

A comprehensive full-stack web application designed to bring government services directly to a user's doorstep. This platform allows users to book professional assistance for tasks like PAN card applications, Aadhaar updates, and more.

## 🚀 Features
- **Modern UI**: Clean, responsive design with glassmorphism and smooth gradients.
- **User Module**: Registration, Login, and Profile Management.
- **Service Catalog**: Browse government services with document checklists and clear pricing.
- **Dynamic Booking**: Select date and time slots for home visits.
- **Real-time Tracking**: A multi-stage progress bar showing the exact status of each booking.
- **Admin Command Center**: Complete control over services, professional agents, and booking statuses.

---

## 🛠️ System Architecture
- **Frontend**: HTML5, CSS3 (Vanilla), JavaScript (Vanilla).
- **Backend**: PHP (PDO for secure MySQL interaction).
- **Database**: MySQL.
- **Architecture**: MVC-inspired folder structure (Actions for logic, Pages for view).

---

## 📂 Folder Structure
- `/actions/`: PHP logical handlers for forms (Login, Register, Bookings).
- `/admin/`: Admin-only dashboard and management pages.
- `/assets/`: CSS styles, JS scripts, and images.
- `/config/`: Database connection and configuration settings.
- `/includes/`: Reusable components like Header and Footer.
- `/pages/`: Publicly accessible pages (Home, Services, Auth).
- `/user/`: User-specific dashboard and profile.

---

## ⚙️ How to Run Locally

### Prerequisites:
1. Install [XAMPP](https://www.apachefriends.org/index.html).

### Steps:
1. **Copy Files**: Place the project folder inside `C:\xampp\htdocs\`.
2. **Start XAMPP**: Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Setup Database**:
   - Open your browser and go to `http://localhost/phpmyadmin/`.
   - Create a new database named `doorstep_service_db`.
   - Import the `db.sql` file provided in the project root.
4. **Access Website**:
   - Go to `http://localhost/project/foodapp/index.php`.

### Admin Credentials:
- **Email**: `admin@doorstep.com`
- **Password**: `admin123`

---

## 🎤 Viva / Exam Points
1. **Security**: Explain how **PDO prepared statements** prevent SQL Injection. Mention **password_hash()** for securing user passwords.
2. **Session Management**: How `$_SESSION` is used to maintain login state and protect admin pages.
3. **Database Normalization**: Explain the relationship between `users`, `services`, and `bookings` (foreign keys).
4. **UX/UI**: Discuss the use of the **Progress Stepper** to reduce user anxiety during the application process.
5. **Logic**: How the `status_logs` table allows for an audit trail of every booking update.
