<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Order;
use App\Models\CustomOrder;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\CloudinaryService;

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
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Upload review images
        $imageUrls = [];
        if ($request->hasFile('images')) {
            $cloudinary = new CloudinaryService();
            foreach ($request->file('images') as $img) {
                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($img, 'reviews');
                    if ($result) {
                        $imageUrls[] = $result['url'];
                    }
                } else {
                    $path = $img->store('reviews', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }
        }

        // Check if review already exists — match on the DB unique key (user_id + product_id)
        // so we never violate the unique constraint even when the same product appears in
        // multiple orders / order-items.
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $orderItem->product_id)
            ->first();

        if ($existingReview) {
            // Update existing review, refresh images only if new ones were uploaded
            $existingReview->update(array_merge($validated, [
                'order_id'       => $order->id,
                'order_item_id'  => $orderItem->id,
                'review_images'  => !empty($imageUrls) ? $imageUrls : ($existingReview->review_images ?? []),
                'verified_purchase' => true,
            ]));
            $message = 'Review updated successfully!';
        } else {
            // Create new review
            Review::create([
                'user_id'          => Auth::id(),
                'product_id'       => $orderItem->product_id,
                'order_id'         => $order->id,
                'order_item_id'    => $orderItem->id,
                'rating'           => $validated['rating'],
                'title'            => $validated['title'],
                'comment'          => $validated['comment'],
                'review_images'    => $imageUrls,
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
        // Railway/session-safe fallback: re-authenticate from auth_token when session is missing.
        if (!Auth::check()) {
            $token = (string) ($request->input('auth_token')
                ?? $request->query('auth_token')
                ?? $request->cookie('auth_token')
                ?? $request->header('X-Auth-Token')
                ?? '');

            if ($token !== '') {
                $authToken = DB::table('auth_tokens')
                    ->where('token', $token)
                    ->where('expires_at', '>', now())
                    ->first();

                if ($authToken) {
                    $user = \App\Models\User::find($authToken->user_id);
                    if ($user) {
                        Auth::login($user, true);
                        session(['auth_token' => $token]);

                        DB::table('auth_tokens')
                            ->where('token', $token)
                            ->update(['expires_at' => now()->addDays(30), 'updated_at' => now()]);
                    }
                }
            }
        }

        // Check if user owns this custom order
        if ($customOrder->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $redirectParams = ['order' => $customOrder->id];
        if ($request->filled('auth_token')) {
            $redirectParams['auth_token'] = (string) $request->input('auth_token');
        }
        $reviewSectionUrl = route('custom_orders.show', $redirectParams) . '#review-section';

        // Validate (manual so we can always return to the review section with clear feedback)
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->to($reviewSectionUrl)
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the review form errors and try again.');
        }

        $validated = $validator->validated();

        // Upload review images
        $imageUrls = [];
        if ($request->hasFile('images')) {
            $cloudinary = new CloudinaryService();
            foreach ($request->file('images') as $img) {
                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($img, 'reviews');
                    if ($result) {
                        $imageUrls[] = $result['url'];
                    }
                } else {
                    $path = $img->store('reviews', 'public');
                    $imageUrls[] = asset('storage/' . $path);
                }
            }
        }

        // Check if review already exists
        $existingReview = Review::where('custom_order_id', $customOrder->id)
            ->where('user_id', Auth::id())
            ->first();

        // Resolve a product reference for compatibility with schemas where product_id is non-null.
        $resolvedProductId = $customOrder->product_id;

        if (empty($resolvedProductId) && !empty($customOrder->product_type)) {
            $resolvedProductId = Product::query()
                ->whereRaw('LOWER(name) = ?', [strtolower(trim((string) $customOrder->product_type))])
                ->value('id');
        }

        if ($existingReview) {
            // Update existing review
            $existingReview->update(array_merge($validated, [
                'review_images' => !empty($imageUrls) ? $imageUrls : ($existingReview->review_images ?? []),
            ]));
            $message = 'Review updated successfully!';
        } else {
            try {
                // Respect the user+product unique key by updating an existing product review if present.
                if (!empty($resolvedProductId)) {
                    $existingProductReview = Review::where('user_id', Auth::id())
                        ->where('product_id', $resolvedProductId)
                        ->first();

                    if ($existingProductReview) {
                        $existingProductReview->update([
                            'custom_order_id' => $customOrder->id,
                            'rating' => $validated['rating'],
                            'title' => $validated['title'] ?? null,
                            'comment' => $validated['comment'] ?? null,
                            'review_images' => !empty($imageUrls) ? $imageUrls : ($existingProductReview->review_images ?? []),
                            'verified_purchase' => true,
                        ]);

                        $message = 'Review updated successfully!';

                        return redirect()->to($reviewSectionUrl)
                            ->with('success', $message);
                    }
                }

                // Create new review (works with both nullable and non-nullable product_id schemas).
                Review::create([
                    'user_id' => Auth::id(),
                    'product_id' => $resolvedProductId,
                    'custom_order_id' => $customOrder->id,
                    'rating' => $validated['rating'],
                    'title' => $validated['title'] ?? null,
                    'comment' => $validated['comment'] ?? null,
                    'review_images' => $imageUrls,
                    'verified_purchase' => true,
                ]);
                $message = 'Review submitted successfully!';
            } catch (QueryException $e) {
                $sqlState = (string) ($e->errorInfo[0] ?? '');
                $driverCode = (string) ($e->errorInfo[1] ?? '');
                $errorText = strtolower($e->getMessage());

                Log::warning('Custom order review insert failed', [
                    'custom_order_id' => $customOrder->id,
                    'user_id' => Auth::id(),
                    'resolved_product_id' => $resolvedProductId,
                    'sql_state' => $sqlState,
                    'driver_code' => $driverCode,
                    'message' => $e->getMessage(),
                ]);

                // Gracefully handle strict deployments where reviews.product_id is still required.
                if (($sqlState === '23000' || $driverCode === '1048') && str_contains($errorText, 'product_id')) {
                    return redirect()->to($reviewSectionUrl)->withInput()->with(
                        'error',
                        'This custom order cannot be reviewed yet because it has no linked product. Please contact support to link the product first.'
                    );
                }

                // Gracefully handle user+product unique collisions.
                if ($sqlState === '23000' && ($driverCode === '1062' || str_contains($errorText, 'duplicate'))) {
                    return redirect()->to($reviewSectionUrl)->withInput()->with(
                        'error',
                        'You already have a review for this product. Please edit your existing review instead.'
                    );
                }

                throw $e;
            }
        }

        return redirect()->to($reviewSectionUrl)
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
