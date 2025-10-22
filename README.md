# Bridge Pen Pal Platform

A Laravel-based web application that connects volunteers with residents in care facilities through a pen-pal program. Organizations can manage their own volunteers and residents, facilitating meaningful connections through letter writing.

## 🎯 Project Overview

Bridge Pen Pal is a multi-tenant platform where:

-   **Organizations** (care facilities, nursing homes) can register and manage their programs
-   **Volunteers** can apply to ONE specific organization to become pen pals (permanent commitment)
-   **Residents** can be registered by organizations to participate in the program
-   **Admins** can approve volunteer applications and manage pen-pal matches within their organization

## 🚀 Getting Started

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   MySQL database
-   XAMPP (for local development)

### Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/Steve-at-Mohawk-College/capstone-project-calvinhuynh14.git
    cd bridge-pen-pal
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install JavaScript dependencies**

    ```bash
    npm install
    ```

4. **Environment setup**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Configure your database**

    - Update `.env` file with your database credentials:

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=bridge_pen_pal
    DB_USERNAME=your_username
    DB_PASSWORD=your_password
    ```

6. **Run database migrations**

    ```bash
    php artisan migrate
    ```

7. **Seed the database (optional)**

    ```bash
    php artisan db:seed
    ```

8. **Build frontend assets**

    ```bash
    npm run build
    # or for development:
    npm run dev
    ```

9. **Start the development server**

    ```bash
    php artisan serve
    ```

    The application will be available at `http://localhost:8000`

## 🔧 Development Commands

### Frontend Development

```bash
# Watch for changes and rebuild assets
npm run dev

# Build for production
npm run build
```

### Database Management

```bash
# Run migrations
php artisan migrate

# Reset database and run all migrations
php artisan migrate:fresh

# Reset database, run migrations, and seed
php artisan migrate:fresh --seed

# Create a new migration
php artisan make:migration create_table_name

# Create a new model with migration
php artisan make:model ModelName -m
```

### Other Useful Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

## 🗄️ Database Schema

### Core Tables

#### `users`

Stores all user accounts (admins, volunteers, residents)

-   `id` - Primary key
-   `name` - User's full name
-   `email` - Unique email address
-   `password` - Hashed password
-   `user_type` - Enum: 'admin', 'volunteer', 'resident'
-   `email_verified_at` - Email verification timestamp
-   `created_at`, `updated_at` - Timestamps

#### `organization`

Stores care facility/organization information

-   `id` - Primary key
-   `name` - Organization name
-   `created_at`, `updated_at` - Timestamps

#### `admin`

Links users to organizations (admin users)

-   `id` - Primary key
-   `user_id` - Foreign key to users table
-   `organization_id` - Foreign key to organization table
-   `created_at`, `updated_at` - Timestamps

### Application Tables

#### `volunteer`

Stores volunteer applications and status

-   `id` - Primary key
-   `user_id` - Foreign key to users table
-   `organization_id` - Foreign key to organization table
-   `status` - Enum: 'pending', 'approved', 'rejected'
-   `application_date` - When the volunteer applied
-   `application_notes` - Optional notes from volunteer
-   `created_at`, `updated_at` - Timestamps
-   **Unique constraint**: (user_id) - One organization per volunteer permanently

#### `resident`

Stores resident applications and information

-   `id` - Primary key
-   `user_id` - Foreign key to users table
-   `organization_id` - Foreign key to organization table
-   `status` - Enum: 'pending', 'approved', 'rejected'
-   `application_date` - When the resident was registered
-   `application_notes` - Optional notes
-   `medical_notes` - Medical information for care facilities
-   `created_at`, `updated_at` - Timestamps
-   **Unique constraint**: (user_id, organization_id)

#### `pen_pal_match`

Stores pen-pal relationships between volunteers and residents

-   `id` - Primary key
-   `volunteer_id` - Foreign key to volunteer table
-   `resident_id` - Foreign key to resident table
-   `organization_id` - Foreign key to organization table
-   `status` - Enum: 'active', 'inactive', 'paused', 'ended'
-   `match_date` - When the match was created
-   `end_date` - When the match ended (if applicable)
-   `match_notes` - Admin notes about the match
-   `created_at`, `updated_at` - Timestamps
-   **Unique constraint**: (volunteer_id, resident_id)

## 🔐 Authentication & Authorization

### User Types

1. **Admin**: Organization administrators who can:

    - Approve/reject volunteer applications
    - Register residents
    - Manage pen-pal matches
    - View organization dashboard

2. **Volunteer**: Individuals who want to become pen pals:

    - Apply to ONE specific organization (permanent commitment)
    - Wait for admin approval before gaining access
    - View their application status
    - Communicate with matched residents (after approval)

3. **Resident**: Care facility residents who:
    - Are registered by organization admins
    - Participate in pen-pal relationships
    - Receive letters from volunteers

### Google OAuth Integration

-   Volunteers and admins can sign up using Google OAuth
-   User type is determined during the registration process
-   Organization selection is required for volunteers

## 🏗️ Architecture

### Technology Stack

-   **Backend**: Laravel 12 (PHP 8.2+)
-   **Frontend**: Vue.js 3 with Inertia.js
-   **Database**: MySQL
-   **Authentication**: Laravel Fortify + Laravel Sanctum
-   **OAuth**: Laravel Socialite (Google)
-   **Styling**: TailwindCSS
-   **Build Tool**: Vite

### Key Features

-   Multi-tenant organization structure
-   Google OAuth integration
-   Role-based access control
-   Application approval workflow
-   Pen-pal matching system
-   Responsive design

## 📝 Development Notes

### Capstone Requirements

-   **Raw SQL Queries**: Business logic uses raw SQL queries instead of Eloquent ORM
-   **Authentication Exception**: Laravel's built-in authentication system is allowed despite using Eloquent
-   **Database Design**: Normalized database structure with proper foreign key relationships

### File Structure

```
app/
├── Http/Controllers/     # API and web controllers
├── Models/              # Eloquent models (for auth only)
├── Actions/Fortify/     # User creation logic
└── Providers/          # Service providers

resources/
├── js/
│   ├── Components/     # Vue.js components
│   ├── Pages/         # Inertia.js pages
│   └── Layouts/       # Application layouts
└── css/               # Stylesheets

database/
├── migrations/        # Database schema migrations
└── seeders/          # Database seeders
```

## 🚀 Deployment

### Production Setup

1. Configure production database
2. Set up environment variables
3. Run `npm run build` for production assets
4. Run `php artisan migrate` to set up database
5. Configure web server (Apache/Nginx)
6. Set up SSL certificates
7. Configure Google OAuth credentials

### Recommended Hosting

-   **Laravel Forge** + **DigitalOcean** (recommended)
-   **Laravel VPS** (alternative)
-   **Railway** (simpler option)

## 🤝 Contributing

This is a capstone project for Mohawk College. For questions or issues, please contact the development team.

## 📄 License

This project is part of an academic capstone project at Mohawk College.
