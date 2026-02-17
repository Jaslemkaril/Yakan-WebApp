-- Update chat message image URLs from /storage/ to /chat-image/
-- Replace domain with your actual domain
UPDATE chat_messages 
SET image_path = REPLACE(image_path, '/storage/chats/', '/chat-image/chats/')
WHERE image_path LIKE '%/storage/chats/%';

-- Update payment proof URLs
UPDATE chat_payments 
SET payment_proof = REPLACE(payment_proof, '/storage/payments/', '/chat-image/payments/')
WHERE payment_proof LIKE '%/storage/payments/%';

-- Verify changes
SELECT id, image_path FROM chat_messages WHERE image_path LIKE '%/chat-image/%' ORDER BY id DESC LIMIT 10;
SELECT id, payment_proof FROM chat_payments WHERE payment_proof LIKE '%/chat-image/%' ORDER BY id DESC LIMIT 10;
