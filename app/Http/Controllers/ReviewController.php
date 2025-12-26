<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\CustomOrder;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Show review form for an order
     */
    public function createForOrder(Order $order)
    {
        // Check if user owns this order (or guest order with null user_id)
        if ($order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if order is out for delivery or delivered
        if (!in_array($order->tracking_status, ['Out for Delivery', 'Delivered']) && $order->status !== 'delivered') {
            return redirect()->route('orders.show', $order)
                ->with('error', 'You can only review orders that are out for delivery or delivered');
        }

        // Get order items
        $items = $order->items()->with('product')->get();

        // Check if user has already reviewed this order
        $existingReviews = Review::where('order_id', $order->id)
            ->where('user_id', Auth::id())
            ->get();

        return view('reviews.create-order', compact('order', 'items', 'existingReviews'));
    }

    /**
     * Show review form for a custom order
     */
    public function createForCustomOrder(CustomOrder $customOrder)
    {
        // Check if user owns this custom order
        if ($customOrder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Check if order is delivered
        if ($customOrder->status !== 'completed' && $customOrder->status !== 'delivered') {
            return redirect()->route('custom_orders.show', $customOrder)
                ->with('error', 'You can only review completed orders');
        }

        // Check if user has already reviewed this order
        $existingReview = Review::where('custom_order_id', $customOrder->id)
            ->where('user_id', Auth::id())
            ->first();

        return view('reviews.create-custom-order', compact('customOrder', 'existingReview'));
    }

    /**
     * Store review for order item
     */
    public function storeForOrderItem(Request $request, OrderItem $orderItem)
    {
        $order = $orderItem->order;

        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Validate
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if review already exists
        $existingReview = Review::where('order_item_id', $orderItem->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update($validated);
            $message = 'Review updated successfully!';
        } else {
            // Create new review
            Review::create([
                'user_id' => Auth::id(),
                'product_id' => $orderItem->product_id,
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'],
                'comment' => $validated['comment'],
                'verified_purchase' => true,
            ]);
            $message = 'Review submitted successfully!';
        }

        return redirect()->route('orders.show', $order)
            ->with('success', $message);
    }

    /**
     * Store review for custom order
     */
    public function storeForCustomOrder(Request $request, CustomOrder $customOrder)
    {
        // Check if user owns this custom order
        if ($customOrder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Validate
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Check if review already exists
        $existingReview = Review::where('custom_order_id', $customOrder->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            // Update existing review
            $existingReview->update($validated);
            $message = 'Review updated successfully!';
        } else {
            // Create new review
            Review::create([
                'user_id' => Auth::id(),
                'product_id' => $customOrder->product_id,
                'custom_order_id' => $customOrder->id,
                'rating' => $validated['rating'],
                'title' => $validated['title'],
                'comment' => $validated['comment'],
                'verified_purchase' => true,
            ]);
            $message = 'Review submitted successfully!';
        }

        return redirect()->route('custom_orders.show', $customOrder)
            ->with('success', $message);
    }

    /**
     * Show all reviews for a product
     */
    public function showProductReviews(Product $product)
    {
        $reviews = Review::forProduct($product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $averageRating = Review::getAverageRating($product->id);
        $ratingDistribution = Review::getRatingDistribution($product->id);
        $totalReviews = Review::getReviewCount($product->id);

        return view('reviews.product-reviews', compact(
            'product',
            'reviews',
            'averageRating',
            'ratingDistribution',
            'totalReviews'
        ));
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful(Review $review)
    {
        $review->markAsHelpful();

        return response()->json([
            'success' => true,
            'helpful_count' => $review->helpful_count,
        ]);
    }

    /**
     * Mark review as unhelpful
     */
    public function markUnhelpful(Review $review)
    {
        $review->markAsUnhelpful();

        return response()->json([
            'success' => true,
            'unhelpful_count' => $review->unhelpful_count,
        ]);
    }

    /**
     * Delete review (user can only delete their own)
     */
    public function destroy(Review $review)
    {
        if ($review->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $review->delete();

        return redirect()->back()
            ->with('success', 'Review deleted successfully!');
    }

    /**
     * Admin: Show pending reviews
     */
    public function adminPending()
    {
        $this->authorize('isAdmin');

        $reviews = Review::pending()
            ->with('user', 'product', 'order', 'customOrder')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.reviews.pending', compact('reviews'));
    }

    /**
     * Admin: Approve review
     */
    public function adminApprove(Review $review)
    {
        $this->authorize('isAdmin');

        $review->approve(Auth::id());

        return redirect()->back()
            ->with('success', 'Review approved successfully!');
    }

    /**
     * Admin: Reject review
     */
    public function adminReject(Request $request, Review $review)
    {
        $this->authorize('isAdmin');

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $review->reject($validated['reason'], Auth::id());

        return redirect()->back()
            ->with('success', 'Review rejected successfully!');
    }

    /**
     * Get reviews for a product (API)
     */
    public function getProductReviews(Product $product)
    {
        $reviews = Review::forProduct($product->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $averageRating = Review::getAverageRating($product->id);
        $totalReviews = Review::getReviewCount($product->id);

        return response()->json([
            'success' => true,
            'average_rating' => round($averageRating, 1),
            'total_reviews' => $totalReviews,
            'reviews' => $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'title' => $review->title,
                    'comment' => $review->comment,
                    'user_name' => $review->user->name,
                    'created_at' => $review->created_at->diffForHumans(),
                    'helpful_count' => $review->helpful_count,
                    'unhelpful_count' => $review->unhelpful_count,
                ];
            }),
        ]);
    }
}
