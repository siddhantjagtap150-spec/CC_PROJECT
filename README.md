<<<<<<< HEAD
# Cloud-Based Student Assignment Submission System

This project is a PHP 8.2+ student assignment submission platform designed for local XAMPP development and cloud readiness on AWS.

## Features

- Student registration, login, logout, dashboard, upload, profile
- Teacher dashboard for reviewing all student submissions
- Role-based access control
- Secure password hashing using `password_hash()` and `password_verify()`
- Prepared SQL statements throughout the application
- CSRF protection on form submissions
- File upload validation for assignment submissions
- Local storage abstraction with AWS S3-ready storage classes
- Clean, responsive front-end interface

## Local setup

1. Place this project in `C:\xampp\htdocs\CC_PROJECT`.
2. Start Apache and MySQL in XAMPP.
3. Ensure the `cloud_database` database exists and contains the `users` and `assignments` tables.
4. Update `config/config.php` or the environment variables for your MySQL connection if needed.
5. Access the project in the browser at `http://localhost/CC_PROJECT`.

## Demo accounts

If no teacher or student account exists in the `users` table, the project creates these demo credentials automatically:

- Teacher: `teacher@cloud.local` / `Teacher@123`
- Student: `student@cloud.local` / `Student@123`

## Database note

This application inspects and adapts to the already existing database schema in `cloud_database` without dropping tables or deleting data. The SQL script in `database/database.sql` only creates tables if they do not already exist.

## AWS-ready design

The project includes an AWS S3 abstraction under `aws/s3.php` and environment-driven configuration examples in `config/config.example.php`. The storage layer is designed to allow a smooth transition from local file storage to S3 without changing the application flow.
=======
# CC_PROJECT
>>>>>>> b086dac3f457501376bcf2d93ba8e06a00b3f94b
