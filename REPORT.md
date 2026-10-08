# Project Report

## Overview

This project implements a secure cloud-based student assignment submission system with role-based access for students and teachers.

## Database compatibility

The application checks the existing `cloud_database` schema and uses the following tables without modifying or deleting data:

- `users` (`id`, `name`, `email`, `password`, `role`)
- `assignments` (`id`, `student_id`, `title`, `filename`, `upload_date`)

The project intentionally does not drop or recreate existing database tables.

## Security measures

- Password hashing with `password_hash()`
- Password verification with `password_verify()`
- Prepared SQL statements for all queries
- Session-based authentication and authorization
- CSRF validation for POST requests
- Server-side validation for uploaded files
- Safe file naming and path checks
- Access control for downloads

## Storage model

The assignment upload system uses a storage abstraction that supports local storage in `uploads/` and is designed for eventual AWS S3 integration.

## Deployment readiness

The app is organized for EC2 hosting with environment-driven configuration, S3 abstraction, and a clear AWS architecture plan documented in the main project specification.
