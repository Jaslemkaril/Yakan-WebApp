<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'product_type' => $this->product_type,
            'specifications' => $this->specifications,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'patterns' => $this->patterns,
            
            // Contact Information
            'phone' => $this->phone,
            'email' => $this->email,
            'delivery_address' => $this->delivery_address,
            
            // Product Details
            'dimensions' => $this->dimensions,
            'preferred_colors' => $this->preferred_colors,
            
            // Color Customization
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            
            // Budget & Timeline
            'budget_range' => $this->budget_range,
            'expected_date' => $this->expected_date,
            
            // Additional Details
            'additional_notes' => $this->additional_notes,
            'estimated_price' => $this->estimated_price,
            'final_price' => $this->final_price,
            'price_breakdown' => $this->getPriceBreakdown(),
            'admin_notes_text' => $this->getAdminNotesText(),
            
            // Admin fields
            'admin_notes' => $this->when(!is_null($this->admin_notes), $this->admin_notes),
            'approved_at' => $this->when(!is_null($this->approved_at), $this->approved_at),
            'rejected_at' => $this->when(!is_null($this->rejected_at), $this->rejected_at),
            'rejection_reason' => $this->when(!is_null($this->rejection_reason), $this->rejection_reason),
            'price_quoted_at' => $this->when(!is_null($this->price_quoted_at), $this->price_quoted_at),
            'user_notified_at' => $this->when(!is_null($this->user_notified_at), $this->user_notified_at),
            
            // Payment fields
            'payment_method' => $this->when(!is_null($this->payment_method), $this->payment_method),
            'transaction_id' => $this->when(!is_null($this->transaction_id), $this->transaction_id),
            'payment_receipt' => $this->when(!is_null($this->payment_receipt), $this->payment_receipt),
            'payment_notes' => $this->when(!is_null($this->payment_notes), $this->payment_notes),
            'transfer_date' => $this->when(!is_null($this->transfer_date), $this->transfer_date),
            
            // Design file
            'design_upload' => $this->when(!is_null($this->design_upload), $this->design_upload),
            'design_upload_url' => $this->when(!is_null($this->design_upload), url('storage/' . $this->design_upload)),
            
            // Computed properties
            'status_description' => $this->getStatusDescription(),
            'available_actions' => $this->getAvailableActions(),
            'workflow_stage' => $this->getWorkflowStage(),
            'is_pending_review' => $this->isPendingReview(),
            'is_price_quoted' => $this->isPriceQuoted(),
            'is_awaiting_user_decision' => $this->isAwaitingUserDecision(),
            'is_ready_for_payment' => $this->isReadyForPayment(),
            'is_cancelled' => $this->isCancelled(),
            'is_completed' => $this->isCompleted(),
            
            // Relationships
            'product' => $this->when($this->relationLoaded('product'), function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => $this->product->price,
                    'image' => $this->product->image ? url('storage/' . $this->product->image) : null,
                ];
            }),
            
            'user' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get workflow stage for the order
     */
    private function getWorkflowStage(): string
    {
        if ($this->isPendingReview()) {
            return 'admin_review';
        }
        
        if ($this->isAwaitingUserDecision()) {
            return 'user_decision';
        }
        
        if ($this->status === 'approved' && $this->payment_status === 'pending') {
            return 'payment';
        }
        
        if ($this->status === 'processing') {
            return 'production';
        }
        
        if ($this->isCompleted()) {
            return 'completed';
        }
        
        return 'other';
    }
}
