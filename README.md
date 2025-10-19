# 📊 Northwind Sales System

A PHP-based web application for managing sales data.  
Supports sales entry, detail view, editing, and reporting.

## 🔧 Tech Stack
- **Frontend:** HTML, CSS, Bootstrap  
- **Backend:** PHP  
- **Database:** MySQL / MariaDB  
- **Tools:** phpMyAdmin, VS Code, GitHub

## 🚀 Features
- 📝 **Add & Edit Sales** (`sales.php`, `sales_edit.php`)  
- 🔍 **View Sales Details** (`sales_detail.php`)  
- 📊 **Sales Reports** (`sales_report.php`)  
- 💾 **Database Connection** (`db_connect.php`)  
- 🛠 **Save & Update Orders** (`save_order.php`, `save_edit.php`)  

## 🖥️ Screenshots
<img width="1915" height="970" alt="image" src="https://github.com/user-attachments/assets/76f0058c-40be-4318-8aa1-ddedabb1e7ef" />
<img width="1908" height="975" alt="image" src="https://github.com/user-attachments/assets/cbd6dac1-024b-4361-959b-c73135ba8767" />
<img width="1908" height="968" alt="image" src="https://github.com/user-attachments/assets/fa823f39-ce2e-421b-a530-3c78f6d1e68b" />
<img width="1904" height="929" alt="image" src="https://github.com/user-attachments/assets/7fca1880-7ac4-4b62-a413-12722f8cf3a4" />
<img width="1908" height="967" alt="image" src="https://github.com/user-attachments/assets/905197de-0deb-4090-86ff-45b4aaaf9c93" />
<img width="1917" height="971" alt="image" src="https://github.com/user-attachments/assets/48ad9ce9-3586-49a7-acf7-3080f6c9a3a2" />
<img width="1889" height="791" alt="image" src="https://github.com/user-attachments/assets/3ab92cb1-a76a-47f2-952f-1d7cb07a9cb2" />
<img width="1916" height="935" alt="image" src="https://github.com/user-attachments/assets/29b4ef25-ab45-4e67-80dc-d6dcfa2b96ae" />
<img width="1914" height="967" alt="image" src="https://github.com/user-attachments/assets/54314674-6fd1-4acd-88a5-af4ce0fc4202" />

## 🔗 Repository
- **GitHub Repository:** [Foam-01/Northwind-Sales-System](https://github.com/Foam-01/Northwind-Sales-System)

## 🏁 Getting Started
Follow these steps to get the project running on your local machine:

1. **Clone the repository:**
```bash
git clone https://github.com/Foam-01/Northwind-Sales-System.git
```
### Set up the database
1. Import the provided SQL file into your MySQL database.  
2. Update `db_connect.php` with your database credentials:

```php
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "northwind";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

Run the application

Use a local server like XAMPP or WAMP

Place the project in the htdocs folder (XAMPP) or equivalent

Open the app in your browser:

http://localhost/Northwind-Sales-System/


📝 Contributing

We welcome contributions! Follow these steps:

# Fork the repository

# Create your feature branch
git checkout -b feature/YourFeature

# Commit your changes
git commit -m "Add some feature"

# Push to your branch
git push origin feature/YourFeature

# Open a Pull Request














