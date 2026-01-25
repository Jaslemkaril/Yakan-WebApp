# Database Setup Guide

## Overview

This application uses **MySQL** as the primary database for both development and production environments. MySQL is a robust, scalable relational database management system that provides excellent performance and reliability.

## Database Configuration

The database is configured in `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yakan_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

## Local Development Setup

### Option 1: Using XAMPP (Recommended for Windows)

1. **Download and Install XAMPP**:
   - Download from [https://www.apachefriends.org/](https://www.apachefriends.org/)
   - Install and start Apache and MySQL services

2. **Create the Database**:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Click "New" to create a database
   - Name it `yakan_db`
   - Select `utf8mb4_unicode_ci` as collation

3. **Configure Environment**:
   - Copy `.env.example` to `.env`
   - Update database settings:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=yakan_db
     DB_USERNAME=root
     DB_PASSWORD=
     ```

4. **Run Migrations**:
   ```bash
   php artisan migrate
   ```

### Option 2: Using MySQL Server Directly

1. **Install MySQL Server**:
   - Download from [https://dev.mysql.com/downloads/](https://dev.mysql.com/downloads/)
   - Follow installation instructions for your OS

2. **Create the Database**:
   ```bash
   mysql -u root -p -e "CREATE DATABASE yakan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. **Configure and Run Migrations** (same as Option 1 steps 3-4)

## Production Deployment (Railway)

### Step 1: Add MySQL Service in Railway

1. Go to your Railway project dashboard
2. Click **"New"** button (top right)
3. Select **"Database"**
4. Choose **"Add MySQL"**
5. Wait for MySQL to provision (~30 seconds)

### Step 2: Configure Environment Variables

Railway automatically creates these variables:
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

In your web service, add these environment variables:

```env
DB_CONNECTION=mysql
DB_HOST=${{MYSQLHOST}}
DB_PORT=${{MYSQLPORT}}
DB_DATABASE=${{MYSQLDATABASE}}
DB_USERNAME=${{MYSQLUSER}}
DB_PASSWORD=${{MYSQLPASSWORD}}
```

**Important**: Use `${{VARIABLE}}` with double curly braces for Railway variable references.

### Step 3: Deploy

Railway will automatically redeploy after saving environment variables. Check deploy logs for successful migration.

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

## Common Commands

### View Database Information
```bash
php artisan db:show
```

### Run Migrations
```bash
php artisan migrate
```

### Fresh Install (Reset Database)
```bash
php artisan migrate:fresh
```
**Warning**: This will delete all existing data!

### Seed Sample Data
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=CategorySeeder
```

## Troubleshooting

### Connection Refused
If you get "Connection refused" error:
```bash
# Check if MySQL is running
# On Windows (XAMPP): Start MySQL from XAMPP Control Panel
# On Linux: sudo systemctl start mysql
# On Mac: brew services start mysql
```

### Access Denied
If you get "Access denied" error:
- Verify username and password in `.env`
- Check MySQL user privileges
- For root user with no password, use empty string: `DB_PASSWORD=`

### Database Not Found
If you get "Unknown database" error:
```bash
mysql -u root -p -e "CREATE DATABASE yakan_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Migration Errors
If migrations fail:
1. Check database connection: `php artisan db:show`
2. Review error message for specific table/column issues
3. Ensure database user has proper permissions

## Database Backups

### Export Database
```bash
# Using mysqldump
mysqldump -u root -p yakan_db > backup.sql

# From phpMyAdmin: Select database → Export → Go
```

### Import Database
```bash
# Using mysql command
mysql -u root -p yakan_db < backup.sql

# From phpMyAdmin: Select database → Import → Choose file
```

## Performance Tips

1. **Indexing**: All migrations include proper indexes for lookup columns
2. **Connection Pooling**: Laravel handles connection pooling automatically
3. **Query Optimization**: Use Laravel's query builder and Eloquent ORM
4. **Caching**: Enable query caching in production (configured in `config/cache.php`)

## Security Best Practices

1. **Strong Passwords**: Use strong passwords for database users
2. **Limited Privileges**: Create separate database users with limited privileges for production
3. **Environment Variables**: Never commit `.env` file to version control
4. **SSL Connections**: Enable SSL for production database connections
5. **Regular Backups**: Schedule regular database backups

## Additional Resources

- [Laravel Database Documentation](https://laravel.com/docs/database)
- [Laravel Migrations](https://laravel.com/docs/migrations)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Railway MySQL Guide](https://docs.railway.app/databases/mysql)
