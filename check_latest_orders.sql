SELECT 
    id, 
    status, 
    fabric_type, 
    fabric_quantity_meters, 
    estimated_price, 
    final_price,
    delivery_address
FROM custom_orders
ORDER BY id DESC
LIMIT 5;
