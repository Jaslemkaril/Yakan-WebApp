#!/bin/bash
# Test the form submission directly

# Get CSRF token from the step 4 page
CSRF=$(curl -s -c /tmp/cookies.txt "http://127.0.0.1:8000/custom-orders/create/step4" 2>/dev/null | grep -o 'name="_token" value="[^"]*' | cut -d'"' -f4)

echo "CSRF Token: $CSRF"

# Submit the form
curl -s -b /tmp/cookies.txt -X POST "http://127.0.0.1:8000/custom-orders/create/complete" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "_token=$CSRF&quantity=1&specifications=&delivery_type=delivery&address_id=1&delivery_zip=&delivery_landmark=" \
  -i | head -50
