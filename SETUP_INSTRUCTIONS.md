# Yakan WebApp Setup Instructions

## Initial Setup

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yakan_webapp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Database Setup
```bash
# Run migrations
php artisan migrate:fresh

# Seed the database with admin users
php artisan db:seed --class=AdminUserSeederUpdated
```

### 4. Default Login Credentials

**Admin Accounts:**
- Email: `admin@yakan.com` / Password: `admin123`
- Email: `kariljaslem@gmail.com` / Password: `admin123`

**Test User:**
- Email: `user@yakan.com` / Password: `user123`

**⚠️ IMPORTANT:** Change these passwords immediately after first login!

### 5. Access Points

**Admin Login:** `/admin/login`
**User Login:** `/login-user`
**Home Page:** `/`

### 6. Alternative: Quick Setup Route

Visit: `your-domain.com/create-admin` to create the admin account via browser.

**Note:** Disable this route in production!

## Troubleshooting

### Admin login not working?

1. **Check if admin user exists:**
```bash
php artisan tinker
>> \App\Models\User::where('email', 'admin@yakan.com')->first();
```

2. **If null, create manually:**
```bash
php artisan db:seed --class=AdminUserSeederUpdated
```

3. **Check authentication:**
Visit `/check-auth` to verify authentication status

4. **Clear cache:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Database issues?

```bash
# Reset database
php artisan migrate:fresh --seed
```
