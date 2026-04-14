# Mobile App Receipt Upload Guide

## Fixed Issues ✅

### 1. Order Status
- **Before**: Orders with paid status showed "pending_confirmation"
- **After**: Orders with paid status now show "processing" immediately
- Orders from mobile app are ready for fulfillment right away

### 2. Receipt Upload Support
- API now accepts `gcash_receipt` and `bank_receipt` file uploads
- Receipts stored in `storage/app/public/receipts/`
- Admin can view receipts in Order Details page

---

## API Endpoints

### 1. Create Order with Receipt (Recommended)
**Endpoint**: `POST /api/v1/orders`

**Content-Type**: `multipart/form-data`

**Parameters**:
```
customer_name: string (required)
customer_email: string (optional)
customer_phone: string (required)
shipping_address: string (required)
delivery_address: string (required)
payment_method: gcash|bank_transfer|cash (required)
payment_status: paid|pending (optional, defaults based on payment_method)
payment_reference: string (optional)
subtotal: number (required)
shipping_fee: number (optional)
discount: number (optional)
total: number (required)
delivery_type: pickup|delivery (optional)
notes: string (optional)
items: array (required) - JSON array of items
gcash_receipt: file (optional) - image file (jpeg, png, jpg, max 5MB)
bank_receipt: file (optional) - image file (jpeg, png, jpg, max 5MB)
```

**Example cURL**:
```bash
curl -X POST http://127.0.0.1:8000/api/v1/orders \
  -F "customer_name=Juan Dela Cruz" \
  -F "customer_phone=09123456789" \
  -F "shipping_address=123 Main St, Manila" \
  -F "delivery_address=123 Main St, Manila" \
  -F "payment_method=gcash" \
  -F "payment_status=paid" \
  -F "subtotal=1500" \
  -F "total=1505" \
  -F "items=[{\"product_id\":1,\"quantity\":1,\"price\":1500}]" \
  -F "gcash_receipt=@/path/to/receipt.jpg"
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 27,
    "order_ref": "ORD-ABC123",
    "status": "processing",
    "payment_status": "paid",
    "gcash_receipt": "receipts/xyz123.jpg",
    ...
  },
  "message": "Order created successfully"
}
```

---

### 2. Upload Receipt to Existing Order
**Endpoint**: `POST /api/v1/orders/{id}/upload-receipt`

**Content-Type**: `multipart/form-data`

**Parameters**:
```
gcash_receipt: file (optional) - image file
bank_receipt: file (optional) - image file
```

**Example cURL**:
```bash
curl -X POST http://127.0.0.1:8000/api/v1/orders/26/upload-receipt \
  -F "gcash_receipt=@/path/to/receipt.jpg"
```

**Response**:
```json
{
  "success": true,
  "data": {
    "id": 26,
    "status": "processing",
    "payment_status": "paid",
    "gcash_receipt": "receipts/abc456.jpg"
  },
  "message": "Receipt uploaded successfully"
}
```

**What happens when receipt is uploaded**:
- Receipt file saved to storage
- `payment_status` automatically set to "paid"
- `status` automatically set to "processing"

---

## Mobile App Implementation

### React Native Example (Creating Order with Receipt)

```javascript
const createOrderWithReceipt = async (orderData, receiptUri) => {
  const formData = new FormData();
  
  // Add order data
  formData.append('customer_name', orderData.customer_name);
  formData.append('customer_phone', orderData.customer_phone);
  formData.append('shipping_address', orderData.shipping_address);
  formData.append('delivery_address', orderData.delivery_address);
  formData.append('payment_method', 'gcash');
  formData.append('payment_status', 'paid');
  formData.append('subtotal', orderData.subtotal);
  formData.append('total', orderData.total);
  formData.append('items', JSON.stringify(orderData.items));
  
  // Add receipt file
  if (receiptUri) {
    const filename = receiptUri.split('/').pop();
    const fileType = filename.split('.').pop();
    
    formData.append('gcash_receipt', {
      uri: receiptUri,
      name: filename,
      type: `image/${fileType}`
    });
  }
  
  try {
    const response = await fetch('http://your-domain.com/api/v1/orders', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
      },
      body: formData
    });
    
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Order creation failed:', error);
    throw error;
  }
};
```

### Flutter Example

```dart
Future<Map<String, dynamic>> createOrderWithReceipt(
  Map<String, dynamic> orderData,
  File receiptFile
) async {
  var request = http.MultipartRequest(
    'POST',
    Uri.parse('http://your-domain.com/api/v1/orders'),
  );
  
  // Add text fields
  request.fields['customer_name'] = orderData['customer_name'];
  request.fields['customer_phone'] = orderData['customer_phone'];
  request.fields['shipping_address'] = orderData['shipping_address'];
  request.fields['delivery_address'] = orderData['delivery_address'];
  request.fields['payment_method'] = 'gcash';
  request.fields['payment_status'] = 'paid';
  request.fields['subtotal'] = orderData['subtotal'].toString();
  request.fields['total'] = orderData['total'].toString();
  request.fields['items'] = jsonEncode(orderData['items']);
  
  // Add receipt file
  if (receiptFile != null) {
    request.files.add(
      await http.MultipartFile.fromPath(
        'gcash_receipt',
        receiptFile.path,
      ),
    );
  }
  
  var response = await request.send();
  var responseData = await response.stream.bytesToString();
  return jsonDecode(responseData);
}
```

---

## Order Status Flow

```
Mobile Order Creation
  ↓
Payment Status = "Paid" (GCash/Bank Transfer)
  ↓
Order Status = "Processing" (Auto-set by API)
  ↓
Admin Views Order → Sees Receipt
  ↓
Admin Updates Status → Shipped/Delivered
```

---

## Testing

### Test Order #26
- ✅ Status updated to "processing"
- ⚠️ Receipt field is NULL (was created before fix)
- 📱 New orders from mobile will have receipts saved

### Create Test Order with Receipt:
```bash
# Save this to test_order_with_receipt.json
{
  "customer_name": "Test User",
  "customer_phone": "09123456789",
  "shipping_address": "Test Address",
  "delivery_address": "Test Address",
  "payment_method": "gcash",
  "payment_status": "paid",
  "subtotal": 500,
  "total": 505,
  "items": [{"product_id": 1, "quantity": 1, "price": 500}]
}

# Then upload with receipt:
curl -X POST http://127.0.0.1:8000/api/v1/orders \
  -F "customer_name=Test User" \
  -F "customer_phone=09123456789" \
  -F "shipping_address=Test Address" \
  -F "delivery_address=Test Address" \
  -F "payment_method=gcash" \
  -F "payment_status=paid" \
  -F "subtotal=500" \
  -F "total=505" \
  -F "items=[{\"product_id\":1,\"quantity\":1,\"price\":500}]" \
  -F "gcash_receipt=@path/to/your/receipt.jpg"
```

---

## Summary

✅ **Fixed**: Order status now "processing" when payment is paid
✅ **Fixed**: API accepts receipt file uploads
✅ **Added**: Upload receipt endpoint for existing orders
✅ **Ready**: Admin can view receipts in Payment Information section

🎯 **Next Steps for Mobile Team**:
1. Update order creation to send receipt as multipart/form-data
2. Test with new orders
3. Verify receipts appear in admin panel
