<?php

/**
 * Laravel Models & Migrations Setup Guide
 * 
 * This file documents the required models and migrations for the YAKAN backend API
 * integration with the React Native mobile app.
 */

// ============================================================================
// USER MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'profile_image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
*/

// ============================================================================
// USER MIGRATION
// ============================================================================

/*
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('first_name');
    $table->string('last_name');
    $table->string('email')->unique();
    $table->string('password');
    $table->string('phone')->nullable();
    $table->string('profile_image')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
});
*/

// ============================================================================
// PRODUCT MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'image',
        'is_featured',
        'stock_quantity',
    ];

    protected $casts = [
        'price' => 'float',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
*/

// ============================================================================
// PRODUCT MIGRATION
// ============================================================================

/*
Schema::create('products', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->string('category');
    $table->string('image')->nullable();
    $table->boolean('is_featured')->default(false);
    $table->integer('stock_quantity')->default(0);
    $table->timestamps();
    
    $table->index('category');
    $table->index('is_featured');
});
*/

// ============================================================================
// ORDER MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_status',
        'shipping_address_id',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'float',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
*/

// ============================================================================
// ORDER MIGRATION
// ============================================================================

/*
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('order_number')->unique();
    $table->decimal('total_amount', 10, 2);
    $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
    $table->enum('payment_status', ['pending', 'pending_verification', 'verified', 'rejected'])->default('pending');
    $table->foreignId('shipping_address_id')->nullable()->constrained('addresses')->onDelete('set null');
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index('user_id');
    $table->index('order_number');
    $table->index('status');
    $table->index('payment_status');
});
*/

// ============================================================================
// ORDER ITEM MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'float',
        'subtotal' => 'float',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
*/

// ============================================================================
// ORDER ITEM MIGRATION
// ============================================================================

/*
Schema::create('order_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('order_id')->constrained()->onDelete('cascade');
    $table->foreignId('product_id')->constrained()->onDelete('restrict');
    $table->integer('quantity');
    $table->decimal('unit_price', 10, 2);
    $table->decimal('subtotal', 10, 2);
    $table->timestamps();
    
    $table->index('order_id');
    $table->index('product_id');
});
*/

// ============================================================================
// ADDRESS MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'street_address',
        'barangay',
        'city',
        'province',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
*/

// ============================================================================
// ADDRESS MIGRATION
// ============================================================================

/*
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('label'); // Home, Office, etc.
    $table->string('full_name');
    $table->string('phone');
    $table->string('street_address');
    $table->string('barangay');
    $table->string('city');
    $table->string('province');
    $table->string('postal_code');
    $table->boolean('is_default')->default(false);
    $table->timestamps();
    
    $table->index('user_id');
});
*/

// ============================================================================
// PAYMENT MODEL
// ============================================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'proof_image',
        'status',
        'uploaded_at',
        'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
*/

// ============================================================================
// PAYMENT MIGRATION
// ============================================================================

/*
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
    $table->string('proof_image'); // Path to uploaded payment proof
    $table->enum('status', ['pending_verification', 'verified', 'rejected'])->default('pending_verification');
    $table->timestamp('uploaded_at')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->timestamps();
    
    $table->index('user_id');
    $table->index('order_id');
    $table->index('status');
});
*/

// ============================================================================
// INSTALLATION INSTRUCTIONS
// ============================================================================

/*
1. Copy the model code from this file into their respective files in app/Models/

2. Create migration files using:
   php artisan make:migration create_users_table
   php artisan make:migration create_products_table
   php artisan make:migration create_orders_table
   php artisan make:migration create_order_items_table
   php artisan make:migration create_addresses_table
   php artisan make:migration create_payments_table

3. Copy the migration code into database/migrations/YYYY_MM_DD_HHMMSS_create_*_table.php files

4. Run migrations:
   php artisan migrate

5. Set up JWT authentication:
   composer require php-open-source-saver/jwt-auth
   php artisan jwt:secret
   
   Update config/auth.php:
   'guards' => [
       'api' => [
           'driver' => 'jwt',
           'provider' => 'users',
           'hash' => false,
       ],
   ]

6. Configure CORS in config/cors.php:
   'allowed_origins' => ['*'],
   'allowed_methods' => ['*'],
   'allowed_headers' => ['*'],
   'exposed_headers' => ['Authorization'],
   'max_age' => 0,

7. Update .env with JWT and database settings:
   JWT_SECRET=your-secret-key
   DATABASE_URL=your-database-connection-string
   FILESYSTEM_DISK=public (for file uploads)
*/
