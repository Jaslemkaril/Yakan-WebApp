# Utility Scripts

## Database Scripts (`/scripts/database`)
Scripts for database maintenance, fixes, and debugging.

### Usage
```bash
php scripts/database/script_name.php
```

### Categories
- **fix_*.php**: Database correction scripts
- **check_*.php**: Database validation scripts
- **debug_*.php**: Debugging utilities
- **cleanup_*.php**: Data cleanup scripts
- **verify_*.php**: Data verification scripts
- **update_*.php**: Data update scripts
- **create_*.php**: User/data creation utilities
- **seed_*.php**: Database seeding scripts
- **add_*.php**: Scripts for adding data to specific records
- **inspect_*.php**: Scripts for inspecting specific records
- **remove_duplicate_patterns.php**: Remove duplicate pattern entries
- **simple-cleanup.php**: General cleanup script
- **test_pattern_creation.php**: Test pattern creation functionality

## Generator Scripts (`/scripts/generators`)
Scripts for generating SKUs, images, and other assets.

### Available Scripts
- **generate_skus.php**: Generate SKU codes for products
- **generate_pattern_images.php**: Generate pattern images

## Sync Scripts (`/scripts/sync`)
Scripts for synchronizing data and storage.

### Available Scripts
- **sync_product_stock.php**: Synchronize product stock data

## Important Notes
- These scripts are intended for development and maintenance only
- Always backup your database before running fix/update scripts
- Test scripts in development environment first
- Some scripts are specific to particular orders or records and may need modification before reuse
