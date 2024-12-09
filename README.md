# Laravel Authentication API

This is a Laravel project that implements API authentication with the following features:
- **User Registration**
- **User Login** 
- **Email Verification** 
- **Forgot Password** 
- **Change Password** 
- **Get User Profile** 

---

## **Requirements**
- PHP >= 8.1
- Composer
- MySQL
- Laravel >= 10

---

## **Setup Instructions**

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/laravel-auth-api.git
cd laravel-auth-api
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
```bash
cp .env.example .env
```
Update the following variables in the .env file with your database details:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=your_encryption
MAIL_FROM_ADDRESS=your_mail_address
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Run Database Migrations
```bash
php artisan migrate
```

### 5. Start the Application
```bash
php artisan serve
```
