-- Add missing columns to chat_messages table for form functionality

-- Check if columns exist before adding
ALTER TABLE chat_messages 
ADD COLUMN IF NOT EXISTS message_type VARCHAR(255) DEFAULT 'text' AFTER sender_type;

ALTER TABLE chat_messages 
ADD COLUMN IF NOT EXISTS form_data JSON NULL AFTER message;

ALTER TABLE chat_messages
ADD COLUMN IF NOT EXISTS reference_images JSON NULL AFTER form_data;

-- Verify columns were added
DESCRIBE chat_messages;



