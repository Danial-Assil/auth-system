
# Auth System

## Description

This project is an Authentication API built with Laravel. It includes user registration, login, password reset, email verification, and two-factor authentication (2FA).

---

## Features

- User Registration and Login using API
- Password Reset via Email
- Email Verification
- Two-Factor Authentication (2FA) with email code
- JWT Authentication (optional integration)
- Secure password hashing
- Role-based Access Control (can be added)

---

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/Danial-Assil/auth-system.git
   ```

2. Navigate to the project directory:

   ```bash
   cd auth-system
   ```

3. Install dependencies:

   ```bash
   composer install
   npm install
   ```

4. Copy `.env.example` to `.env` and configure your environment variables:

   ```bash
   cp .env.example .env
   ```

5. Generate application key:

   ```bash
   php artisan key:generate
   ```

6. Configure your database settings in `.env`.

7. Run migrations:

   ```bash
   php artisan migrate
   ```

8. (Optional) Setup Mailtrap or your mail service for email sending.

---

## Usage

- Register a new user via API.
- Login to get authentication token.
- Enable Two-Factor Authentication to receive verification code on email.
- Reset password using email link.

---

## Technologies

- Laravel 12
- PHP 8+
- MySQL / MariaDB / PostgreSQL
- Mailtrap (for testing emails)
- JWT (optional)
- Composer / NPM

---

## Contributing

Feel free to fork and submit pull requests.

---

## License

MIT License

---

## Author

Danial-Assil
