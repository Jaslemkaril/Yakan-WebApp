# Database Setup Guide

## Overview

This application uses **SQLite** as the default database for development and local environments. SQLite is a lightweight, file-based database that requires no separate server installation.

## Database Configuration

The database is configured in `.env` file:

```env
DB_CONNECTION=sqlite
```

The SQLite database file is located at: `database/database.sqlite`

## Initial Setup

The database has been initialized with all required tables. If you need to reset or recreate the database:

1. **Delete the existing database** (optional):
   ```bash
   rm database/database.sqlite
   ```

2. **Create a new empty database file**:
   ```bash
   touch database/database.sqlite
   ```

3. **Run migrations** to create all tables:
   ```bash
   php artisan migrate
   ```

## Database Schema

The application includes the following main tables:

- **users** - User accounts and authentication
- **products** - Product catalog
- **categories** - Product categories
- **orders** - Customer orders
- **order_items** - Items within each order
- **custom_orders** - Custom Yakan pattern orders
- **yakan_patterns** - Traditional Yakan weaving patterns
- **reviews** - Product reviews
- **inventory** - Stock management
- **carts** - Shopping cart data
- And many more...

## Viewing Database Information

To view database details:
```bash
php artisan db:show
```

## Using MySQL Instead

If you prefer to use MySQL for production:

1. Update `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=yakan_db
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

2. Create the database:
   ```bash
   mysql -u root -p -e "CREATE DATABASE yakan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

## Troubleshooting

### Database file not found
If you get "database file not found" error:
```bash
touch database/database.sqlite
php artisan migrate
```

### Permission denied
If you get permission errors:
```bash
chmod 664 database/database.sqlite
chmod 775 database/
```

### Fresh installation
To start with a clean database:
```bash
php artisan migrate:fresh
```

**Warning**: This will delete all existing data!

## Production Deployment

For production environments (like Railway):

- **Option 1**: Use SQLite with persistent volume
  - Set `DB_CONNECTION=sqlite`
  - Mount `/app/storage` as a volume
  
- **Option 2**: Use managed MySQL service
  - Add MySQL database service
  - Configure environment variables
  - See `RAILWAY_FIX_DATABASE.md` for details

## Database Backups

### Backup SQLite database
```bash
cp database/database.sqlite database/database.sqlite.backup
```

### Restore from backup
```bash
cp database/database.sqlite.backup database/database.sqlite
```

## Seeding Data

To populate the database with sample data:
```bash
php artisan db:seed
```

## Additional Resources

- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Laravel Migrations](https://laravel.com/docs/migrations)
- [SQLite Documentation](https://www.sqlite.org/docs.html)
