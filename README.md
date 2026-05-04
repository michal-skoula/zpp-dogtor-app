# Dogtor

A web application for managing medical prescriptions between doctors and patients.

## Features

**Doctors (admin panel):**
- Create and manage prescriptions with dosage schedules
- Manage the drug catalog
- View and manage patient accounts

**Patients:**
- View assigned prescriptions and dose schedules

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Admin UI:** Filament 4, Livewire 3
- **Frontend:** Vite, Tailwind CSS
- **Database:** SQLite (default)

## Running Locally

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm

### Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
```

### Start the dev server

```bash
php artisan serve
npm run dev
```

This starts the Laravel server, queue listener, and Vite dev server concurrently.

## Demo Accounts

| Role    | Email                  | Password |
|---------|------------------------|----------|
| Doctor  | doctor@example.com     | password |
| Patient | patient@example.com    | password |

## Panels

| Panel          | URL                        |
|----------------|----------------------------|
| Doctor (admin) | http://localhost/admin     |
| Patient        | http://localhost/patient   |
