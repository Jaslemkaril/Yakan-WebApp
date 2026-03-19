<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\Category;
use App\Models\YakanPattern;
use App\Models\User;
use App\Services\CloudinaryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomOrderController extends Controller
{
    /**
     * Redirect to a named route with auth_token appended as a query parameter.
     * This ensures the user stays authenticated across steps on Railway,
     * where session cookies are not reliably persisted.
     */
    private function redirectToRouteWithToken(string $routeName, $routeParams = [], array $queryParams = []): \Illuminate\Http\RedirectResponse
    {
        $token = request()->input('auth_token') ?? request()->query('auth_token') ?? session('auth_token');
        
        // If no token exists but user is authenticated, create/retrieve one from DB
        if (!$token && auth()->check()) {
            // Try to get existing valid token from DB
            $token = \DB::table('auth_tokens')
                ->where('user_id', auth()->id())
                ->where('expires_at', '>', now())
                ->orderByDesc('updated_at')
                ->value('token');
            
            // If no valid token exists, create a new one
            if (!$token) {
                $token = \Illuminate\Support\Str::random(64);
                \DB::table('auth_tokens')->insert([
                    'user_id' => auth()->id(),
                    'token' => $token,
                    'expires_at' => now()->addDays(30),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Log::info('Created new auth_token for user during redirect', [
                    'user_id' => auth()->id(),
                    'route' => $routeName
                ]);
            }
            
            // Store in session for subsequent requests
            session(['auth_token' => $token]);
        }
        
        if ($token) {
            $queryParams['auth_token'] = $token;
        }
        $url = route($routeName, $routeParams);
        if (!empty($queryParams)) {
            $url .= '?' . http_build_query($queryParams);
        }
        return redirect($url);
    }

    /**
     * Get wizard data from database (persists across requests on Railway where sessions are broken).
     */
    private function getWizardData(): array
    {
        if (!auth()->check()) {
            return [];
        }
        $draft = \DB::table('custom_order_drafts')->where('user_id', auth()->id())->first();
        if ($draft && $draft->wizard_data) {
            return json_decode($draft->wizard_data, true) ?? [];
        }
        // Fallback: try session (for local development)
        return request()->session()->get('wizard', []);
    }

    /**
     * Save wizard data to database so it persists across requests on Railway.
     */
    private function saveWizardData(array $data): void
    {
        if (!auth()->check()) {
            return;
        }
        \DB::table('custom_order_drafts')->upsert(
            [
                'user_id'     => auth()->id(),
                'wizard_data' => json_encode($data),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            ['user_id'],
            ['wizard_data', 'updated_at']
        );
        // Also keep session in sync for local dev
        request()->session()->put('wizard', $data);
    }

    /**
     * Delete the wizard draft from the database (called after order is placed).
     */
    private function clearWizardData(): void
    {
        if (auth()->check()) {
            \DB::table('custom_order_drafts')->where('user_id', auth()->id())->delete();
        }
        request()->session()->forget('wizard');
    }

    /**
     * Validate wizard session data with comprehensive logging
     */
    private function validateWizardSession(Request $request, string $step = 'unknown')
    {
        $sessionData = $this->getWizardData();
        
        \Log::info("Wizard session validation for step: {$step}", [
            'session_keys' => array_keys($sessionData),
            'has_fabric' => isset($sessionData['fabric']),
            'has_pattern' => isset($sessionData['pattern']),
            'has_colors' => isset($sessionData['colors']),
            'has_design' => isset($sessionData['design']),
            'has_details' => isset($sessionData['details']),
        ]);

        return $sessionData;
    }

    /**
     * Log wizard errors with detailed context
     */
    private function logWizardError(Request $request, string $message, string $step, \Exception $e = null)
    {
        $context = [
            'step' => $step,
            'user_id' => auth()->id(),
            'session_data' => $this->getWizardData(),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
        ];

        if ($e) {
            $context['error'] = $e->getMessage();
            $context['trace'] = $e->getTraceAsString();
            \Log::error("Wizard Error: {$message}", $context);
        } else {
            \Log::warning("Wizard Warning: {$message}", $context);
        }
    }

    /**
     * Step 3: Order Details
     */
    public function createStep3(Request $request)
    {
        try {
            $wizardData = $this->getWizardData();

            if (!$wizardData) {
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Please start your custom order.');
            }

            // For Fabric Flow (fabric + pattern), skip Order Details and go directly to Review
            if (isset($wizardData['fabric']) && isset($wizardData['pattern']) && !isset($wizardData['product'])) {
                return $this->redirectToRouteWithToken('custom_orders.create.step4');
            }

            if (isset($wizardData['product'])) {
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if (!$product) {
                    return $this->redirectToRouteWithToken('custom_orders.create.product')
                        ->with('error', 'Please select a product first.');
                }
                if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                    return $this->redirectToRouteWithToken('custom_orders.create.product.customize')
                        ->with('error', 'Please customize your design first.');
                }
                return view('custom_orders.wizard.step3', [
                    'product' => $product,
                    'isProductFlow' => true
                ]);
            }

            if (!isset($wizardData['fabric'])) {
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Please select a fabric first.');
            }
            if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                return $this->redirectToRouteWithToken('custom_orders.create.pattern')
                    ->with('error', 'Please select a pattern or create a design first.');
            }

            return view('custom_orders.wizard.step3', [
                'isProductFlow' => false
            ]);
        } catch (\Exception $e) {
            \Log::error('createStep3 error', ['error' => $e->getMessage()]);
            return $this->redirectToRouteWithToken('custom_orders.create.step1')
                ->with('error', 'Unable to load details page. Please try again.');
        }
    }

    /**
     * Store Step 3: Order Details
     */
    public function storeStep3(Request $request)
    {
        try {
            $validated = $request->validate([
                'order_name' => 'required|string|max:255',
                'size' => 'required|string|max:50',
                'delivery_type' => 'required|in:delivery,pickup',
                'address_id' => 'required_if:delivery_type,delivery|nullable|integer|exists:user_addresses,id',
                'customer_email' => 'nullable|email',
                'customer_phone' => 'nullable|string|max:50',
                'special_instructions' => 'nullable|string|max:2000',
                'addons' => 'nullable|array',
                'addons.*' => 'string',
            ]);

            $wizardData = $this->getWizardData();

            // Get the selected address if delivery type is delivery
            $deliveryAddress = null;
            $deliveryCity = null;
            $deliveryProvince = null;
            if ($validated['delivery_type'] === 'delivery' && $validated['address_id']) {
                $address = auth()->user()->addresses()->find($validated['address_id']);
                if ($address) {
                    $deliveryAddress = "{$address->street}, {$address->barangay}, {$address->city}, {$address->province} {$address->postal_code}";
                    $deliveryCity = $address->city;
                    $deliveryProvince = $address->province;
                }
            }

            $wizardData['details'] = [
                'order_name' => $validated['order_name'],
                'size' => $validated['size'],
                'delivery_type' => $validated['delivery_type'],
                'address_id' => $validated['address_id'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'delivery_address' => $deliveryAddress,
                'delivery_city' => $deliveryCity,
                'delivery_province' => $deliveryProvince,
                'special_instructions' => $validated['special_instructions'] ?? null,
                'addons' => $validated['addons'] ?? [],
                'updated_at' => now()->toISOString(),
            ];

            $wizardData['step'] = 'details_complete';
            $this->saveWizardData($wizardData);
            
            \Log::info('storeStep3 - saved', [
                'wizard_keys' => array_keys($wizardData),
                'has_pattern' => isset($wizardData['pattern']),
                'has_details' => isset($wizardData['details']),
            ]);

            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
                $redirectUrl = route('custom_orders.create.step4') . ($token ? '?auth_token=' . urlencode($token) : '');
                return response()->json(['success' => true, 'message' => 'Order details saved!', 'redirect_url' => $redirectUrl]);
            }
            return $this->redirectToRouteWithToken('custom_orders.create.step4');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('storeStep3 error', ['error' => $e->getMessage()]);
            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Unable to save order details. Please try again.'], 500);
            }
            return redirect()->back()->with('error', 'Unable to save order details. Please try again.');
        }
    }

    /**
     * Redirect with wizard-specific error handling and progress tracking
     */
    private function wizardRedirect(Request $request, string $route, string $message, string $step, \Exception $e = null)
    {
        $this->logWizardError($request, $message, $step, $e);
        
        // Add progress information to help users understand where they are
        $progressInfo = $this->getStepProgress($step);
        
        return redirect()->route($route)
            ->with('error', $message)
            ->with('wizard_step', $step)
            ->with('progress_info', $progressInfo);
    }

    /**
     * Get progress information for each step
     */
    private function getStepProgress(string $currentStep): array
    {
        $steps = [
            'step1' => ['name' => 'Fabric Selection', 'completed' => false, 'current' => false],
            'colors' => ['name' => 'Pattern & Colors', 'completed' => false, 'current' => false],
            'step3' => ['name' => 'Order Details', 'completed' => false, 'current' => false],
            'step4' => ['name' => 'Review & Payment', 'completed' => false, 'current' => false],
        ];

        // Mark current step
        if (isset($steps[$currentStep])) {
            $steps[$currentStep]['current'] = true;
        }

        // Mark completed steps based on current step
        $stepOrder = ['step1', 'colors', 'step3', 'step4'];
        $currentIndex = array_search($currentStep, $stepOrder);
        
        for ($i = 0; $i < $currentIndex; $i++) {
            $stepKey = $stepOrder[$i];
            if (isset($steps[$stepKey])) {
                $steps[$stepKey]['completed'] = true;
            }
        }

        return [
            'steps' => $steps,
            'current_step' => $currentStep,
            'total_steps' => count($steps),
            'completed_steps' => $currentIndex,
            'progress_percentage' => ($currentIndex / count($steps)) * 100
        ];
    }

    /**
     * Validate wizard session integrity with comprehensive checks
     */
    private function validateWizardSessionIntegrity(Request $request, string $step): array
    {
        $wizardData = $this->getWizardData();
        $issues = [];
        $warnings = [];

        // Check basic session existence
        if (empty($wizardData)) {
            $issues[] = 'No wizard session data found';
            return ['valid' => false, 'issues' => $issues, 'warnings' => $warnings, 'data' => []];
        }

        // Validate step-specific requirements
        switch ($step) {
            case 'colors':
                if (!isset($wizardData['fabric'])) {
                    $issues[] = 'Fabric selection missing';
                } elseif (!isset($wizardData['fabric']['type'])) {
                    $issues[] = 'Fabric type missing';
                }
                break;

            case 'step3':
                $required = ['fabric', 'pattern', 'colors'];
                foreach ($required as $key) {
                    if (!isset($wizardData[$key])) {
                        $issues[] = ucfirst($key) . ' data missing';
                    }
                }
                
                // Validate pattern integrity
                if (isset($wizardData['pattern']) && (!isset($wizardData['pattern']['id']) || !isset($wizardData['pattern']['name']))) {
                    $issues[] = 'Pattern data incomplete';
                }
                
                // Validate color integrity
                if (isset($wizardData['colors']) && (!isset($wizardData['colors']['primary']) || !isset($wizardData['colors']['secondary']))) {
                    $issues[] = 'Color data incomplete';
                }
                break;

            case 'step4':
                $required = ['fabric', 'pattern', 'colors', 'details'];
                foreach ($required as $key) {
                    if (!isset($wizardData[$key])) {
                        $issues[] = ucfirst($key) . ' data missing';
                    }
                }
                
                // Validate order details
                if (isset($wizardData['details'])) {
                    $requiredDetails = ['order_name', 'size', 'priority'];
                    foreach ($requiredDetails as $detail) {
                        if (!isset($wizardData['details'][$detail]) || empty($wizardData['details'][$detail])) {
                            $issues[] = "Order detail '{$detail}' missing or empty";
                        }
                    }
                }
                break;
        }

        // Check for data consistency
        // Removed product ID check since we're now using fabric-based orders

        $isValid = empty($issues);

        \Log::info("Wizard session validation for {$step}", [
            'valid' => $isValid,
            'issues_count' => count($issues),
            'warnings_count' => count($warnings),
            'session_keys' => array_keys($wizardData)
        ]);

        return [
            'valid' => $isValid,
            'issues' => $issues,
            'warnings' => $warnings,
            'data' => $wizardData
        ];
    }

    /**
     * Ensure required session data exists with defaults
     */
    private function ensureSessionData(Request $request, array $requiredKeys, array $defaults = [])
    {
        $wizardData = $this->getWizardData();
        
        foreach ($requiredKeys as $key) {
            if (!isset($wizardData[$key])) {
                if (isset($defaults[$key])) {
                    $wizardData[$key] = $defaults[$key];
                    \Log::info("Applied default for missing session key: {$key}", ['default' => $defaults[$key]]);
                } else {
                    return false;
                }
            }
        }

        $this->saveWizardData($wizardData);
        return $wizardData;
    }

    /**
     * Backup wizard session data (no-op: DB storage is always persistent)
     */
    private function backupWizardSession(Request $request, string $step)
    {
        // DB-backed storage is inherently persistent — no backup needed
    }

    /**
     * Restore wizard session from backup (reads from DB directly)
     */
    private function restoreWizardSession(Request $request, string $preferredStep = null)
    {
        $data = $this->getWizardData();
        return !empty($data) ? ($data['step'] ?? 'step1') : false;
    }

    /**
     * Clear wizard session and backups
     */
    private function clearWizardSession(Request $request)
    {
        $this->clearWizardData();
        \Log::info("Wizard data cleared for user", ['user_id' => auth()->id()]);
    }

    /**
     * Resolve all orders in the same user batch as the provided order.
     */
    private function getUserBatchOrders(CustomOrder $order, int $userId, bool $unpaidOnly = false): \Illuminate\Support\Collection
    {
        $query = CustomOrder::where('user_id', $userId);
        $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

        if ($hasBatchColumn && !empty($order->batch_order_number)) {
            $query->where('batch_order_number', $order->batch_order_number);
        } else {
            // Fallback grouping for same submission process (same user + same minute)
            $minuteKey = optional($order->created_at)->format('Y-m-d H:i');

            if ($minuteKey) {
                $query->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$minuteKey]);

                if ($hasBatchColumn) {
                    $query->where(function ($q) {
                        $q->whereNull('batch_order_number')
                          ->orWhere('batch_order_number', '');
                    });
                }
            } else {
                $query->where('id', $order->id);
            }
        }

        if ($unpaidOnly) {
            $query->where('payment_status', '!=', 'paid');
        }

        $orders = $query->orderBy('id')->get();

        return $orders->isNotEmpty()
            ? $orders
            : CustomOrder::where('id', $order->id)->get();
    }

    /**
     * Compute payable amount for one custom order row in list contexts.
     *
     * Rule used to avoid double-counting:
     * - Before payment method is chosen: add shipping fee (if delivery).
     * - After payment method is chosen: rely on stored price as-is.
     */
    private function calculateOrderPayableTotal(CustomOrder $order): float
    {
        $base = (float) ($order->final_price ?? $order->estimated_price ?? 0);

        $deliveryType = $order->delivery_type ?? ($order->delivery_address ? 'delivery' : 'pickup');
        if ($deliveryType === 'pickup') {
            return $base;
        }

        $shipping = (float) ($order->shipping_fee ?? 0);
        if ($shipping <= 0) {
            return $base;
        }

        $breakdown = $order->getPriceBreakdown();
        $deliveryFeeInBreakdown = (float) (($breakdown['breakdown']['delivery_fee'] ?? 0));
        $alreadyIncludedInBase = $deliveryFeeInBreakdown > 0;

        return $alreadyIncludedInBase ? $base : ($base + $shipping);
    }

    /**
     * Sum payable amount across a set of custom orders.
     */
    private function calculateOrdersTotal(\Illuminate\Support\Collection $orders): float
    {
        if ($orders->count() <= 1) {
            return (float) $orders->sum(function (CustomOrder $item) {
                return $this->calculateOrderPayableTotal($item);
            });
        }

        $quotedSubtotal = (float) $orders->sum(
            fn(CustomOrder $item) => (float) ($item->final_price ?? $item->estimated_price ?? 0)
        );

        // NOTE: Shipping is calculated separately in initiatePaymentAjax() to avoid double-counting
        // This method returns ONLY the items subtotal (no shipping)
        return $quotedSubtotal;
    }

    /**
     * Compute shipping fee from delivery location using the same zone model shown in step4 UI.
     */
    private function resolveAddressBasedShippingFee(
        ?string $deliveryType,
        ?string $city,
        ?string $province,
        ?string $fullAddress = null
    ): float {
        if (($deliveryType ?? 'delivery') === 'pickup') {
            return 0.0;
        }

        $haystack = strtolower(trim((string) ($fullAddress ?? '')) . ' ' . trim((string) ($city ?? '')) . ' ' . trim((string) ($province ?? '')));

        // Zone 0: Within Zamboanga area (₱100)
        if (
            str_contains($haystack, 'zamboanga') // Catch any zamboanga variant
        ) {
            return 100.0;
        }

        // Zone 1: BARMM (₱100)
        if (
            str_contains($haystack, 'barmm') ||
            str_contains($haystack, 'bangsamoro') ||
            str_contains($haystack, 'basilan') ||
            str_contains($haystack, 'sulu') ||
            str_contains($haystack, 'tawi')
        ) {
            return 100.0;
        }

        // Zone 2: Other Mindanao regions
        if (
            str_contains($haystack, 'mindanao') ||
            str_contains($haystack, 'davao') ||
            str_contains($haystack, 'cagayan de oro') ||
            str_contains($haystack, 'iligan') ||
            str_contains($haystack, 'cotabato') ||
            str_contains($haystack, 'caraga') ||
            str_contains($haystack, 'general santos') ||
            str_contains($haystack, 'soccsksargen')
        ) {
            return 180.0;
        }

        // Zone 3: Visayas
        if (
            str_contains($haystack, 'visaya') ||
            str_contains($haystack, 'cebu') ||
            str_contains($haystack, 'iloilo') ||
            str_contains($haystack, 'bacolod') ||
            str_contains($haystack, 'tacloban') ||
            str_contains($haystack, 'leyte') ||
            str_contains($haystack, 'samar') ||
            str_contains($haystack, 'bohol') ||
            str_contains($haystack, 'negros')
        ) {
            return 250.0;
        }

        // Zone 4: NCR + nearby Luzon
        if (
            str_contains($haystack, 'ncr') ||
            str_contains($haystack, 'metro manila') ||
            str_contains($haystack, 'manila') ||
            str_contains($haystack, 'quezon city') ||
            str_contains($haystack, 'makati') ||
            str_contains($haystack, 'calabarzon') ||
            str_contains($haystack, 'central luzon') ||
            str_contains($haystack, 'laguna') ||
            str_contains($haystack, 'cavite') ||
            str_contains($haystack, 'bulacan')
        ) {
            return 300.0;
        }

        // Zone 5: Far Luzon / remote
        return 350.0;
    }

    /**
     * List custom orders for the logged-in user
     */
    public function userIndex()
    {
        $hasBatchColumn = \Schema::hasColumn('custom_orders', 'batch_order_number');

        // Detect implicit grouped submissions (same user + same minute).
        $implicitGroupsQuery = \DB::table('custom_orders')
            ->select(
                'user_id',
                \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as minute_key"),
                \DB::raw('MIN(id) as primary_id'),
                \DB::raw('COUNT(*) as cnt')
            )
            ->where('user_id', Auth::id());

        if ($hasBatchColumn) {
            $implicitGroupsQuery->where(function ($q) {
                $q->whereNull('batch_order_number')
                  ->orWhere('batch_order_number', '');
            });
        }

        $implicitGroups = $implicitGroupsQuery
            ->groupBy('user_id', \DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')"))
            ->havingRaw('cnt > 1')
            ->get();

        $implicitExcludeIds = collect();
        $fallbackBatchMeta = [];
        foreach ($implicitGroups as $group) {
            $groupOrdersQuery = CustomOrder::query()
                ->where('user_id', $group->user_id)
                ->whereRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') = ?", [$group->minute_key]);

            if ($hasBatchColumn) {
                $groupOrdersQuery->where(function ($q) {
                    $q->whereNull('batch_order_number')
                      ->orWhere('batch_order_number', '');
                });
            }

            $groupOrders = $groupOrdersQuery->orderBy('id')->get();

            $fallbackBatchMeta[$group->minute_key] = [
                'item_count' => (int) $group->cnt,
                'batch_total' => $this->calculateOrdersTotal($groupOrders),
            ];

            $memberIds = $groupOrders
                ->pluck('id')
                ->filter(fn($id) => (int) $id !== (int) $group->primary_id)
                ->values();

            $implicitExcludeIds = $implicitExcludeIds->merge($memberIds);
        }

        // Build base query to surface one row per grouped submission.
        if ($hasBatchColumn) {
            $batchPrimaryIds = CustomOrder::query()
                ->where('user_id', Auth::id())
                ->whereNotNull('batch_order_number')
                ->where('batch_order_number', '!=', '')
                ->selectRaw('MIN(id) as primary_id')
                ->groupBy('batch_order_number')
                ->pluck('primary_id');

            $query = CustomOrder::with('product')
                ->where('user_id', Auth::id())
                ->where(function ($q) use ($batchPrimaryIds) {
                    $q->whereNull('batch_order_number')
                      ->orWhere('batch_order_number', '')
                      ->orWhereIn('id', $batchPrimaryIds);
                })
                ->when($implicitExcludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $implicitExcludeIds))
                ->orderByDesc('created_at');
        } else {
            $query = CustomOrder::with('product')
                ->where('user_id', Auth::id())
                ->when($implicitExcludeIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $implicitExcludeIds))
                ->orderByDesc('created_at');
        }

        $orders = $query->paginate(10);

        $batchMeta = [];
        if ($hasBatchColumn) {
            $batchNumbers = $orders->getCollection()
                ->pluck('batch_order_number')
                ->filter()
                ->unique()
                ->values();

            if ($batchNumbers->isNotEmpty()) {
                $batchRows = CustomOrder::query()
                    ->where('user_id', Auth::id())
                    ->whereIn('batch_order_number', $batchNumbers)
                    ->get()
                    ->groupBy('batch_order_number');

                $batchMeta = $batchRows
                    ->map(function ($rows) {
                        return [
                            'item_count' => $rows->count(),
                            'batch_total' => $this->calculateOrdersTotal($rows),
                        ];
                    })
                    ->toArray();
            }
        }

        return view('custom_orders.index', compact('orders', 'batchMeta', 'fallbackBatchMeta'));
    }

    /**
     * Show form to create a new custom order
     * Redirects directly to fabric selection (Pattern/Fabric Design Flow)
     */
    public function create()
    {
        return $this->redirectToRouteWithToken('custom_orders.create.step1');
    }


    /**
     * Step 1: Fabric Selection
     */
    public function createStep1(Request $request)
    {
        try {
            \Log::info('createStep1 called', ['user_id' => auth()->id()]);

            // Fresh entrypoint from /custom-orders/create should always start clean.
            if ($request->boolean('new_submission')) {
                $this->clearWizardData();
                \Log::info('Started fresh custom-order submission; cleared previous wizard draft', [
                    'user_id' => auth()->id(),
                ]);
            }
            
            // Test basic database connection
            try {
                \DB::connection()->getPdo();
                \Log::info('Database connection OK');
            } catch (\Exception $dbEx) {
                \Log::error('Database connection failed', ['error' => $dbEx->getMessage()]);
                throw $dbEx;
            }

            // Load active fabric types and intended uses
            $fabricTypes = \App\Models\FabricType::active()->get();
            $intendedUses = \App\Models\IntendedUse::active()->get();
            
            // Get price per meter from settings
            $pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 200);
            
            \Log::info('Loading fabric selection step', [
                'fabric_types_count' => $fabricTypes->count(),
                'intended_uses_count' => $intendedUses->count(),
                'price_per_meter' => $pricePerMeter
            ]);
            
            return view('custom_orders.wizard.step1', [
                'fabricTypes' => $fabricTypes,
                'intendedUses' => $intendedUses,
                'pricePerMeter' => $pricePerMeter
            ]);
            
        } catch (\Exception $e) {
            \Log::error('createStep1 error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->wizardRedirect($request, 'custom_orders.index', 
                'Unable to load fabric selection. Please try again.', 'step1', $e);
        }
    }

    /**
     * Restore wizard from backup
     */
    public function restoreWizard(Request $request)
    {
        try {
            $restoredStep = $this->restoreWizardSession($request);
            
            if ($restoredStep) {
                return $this->redirectToRouteWithToken("custom_orders.create.{$restoredStep}")
                    ->with('success', 'Your previous progress has been restored.');
            } else {
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('info', 'No previous progress found to restore.');
            }
        } catch (\Exception $e) {
            return $this->wizardRedirect($request, 'custom_orders.create.step1', 
                'Unable to restore progress. Please start fresh.', 'restore', $e);
        }
    }

    /**
     * Store wizard step 1 (product selection) - Redirect to pattern selection
     */
    public function storeStep1(Request $request)
    {
        try {
            \Log::info('storeStep1 called', [
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'headers' => $request->headers->all(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'requested_with' => $request->header('X-Requested-With')
            ]);

            // Validate fabric selection instead of product
            $request->validate([
                'fabric_type' => 'required|integer|exists:fabric_types,id',
                'fabric_quantity_meters' => 'required|numeric|min:0.5|max:100',
                'intended_use' => 'required|integer|exists:intended_uses,id',
            ]);

            // Get fabric type details (for now using string, can be updated to use FabricType model later)
            $fabricType = $request->fabric_type;
            
            // Store fabric selection in DB (persists across Railway requests)
            $wizardData = $this->getWizardData();
            $wizardData['fabric'] = [
                'type' => $fabricType,
                'quantity_meters' => $request->fabric_quantity_meters,
                'intended_use' => $request->intended_use,
                'fabric_specifications' => $request->fabric_specifications,
                'special_requirements' => $request->special_requirements,
            ];
            $this->saveWizardData($wizardData);
            
            \Log::info("Step1 completed successfully", [
                'fabric_type' => $fabricType,
                'quantity_meters' => $request->fabric_quantity_meters,
                'intended_use' => $request->intended_use,
                'session_saved' => true,
                'session_data_after_save' => $wizardData,
            ]);

            // Check if it's an AJAX request (improved detection)
            $isAjax = $request->ajax() || 
                     $request->wantsJson() || 
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     $request->header('Accept') === 'application/json';

            if ($isAjax) {
                $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
                $redirectUrl = route('custom_orders.create.pattern') . ($token ? '?auth_token=' . urlencode($token) : '');
                return response()->json([
                    'success' => true,
                    'message' => 'Fabric selection saved successfully',
                    'redirect_url' => $redirectUrl,
                    'fabric' => [
                        'type' => $fabricType,
                        'quantity_meters' => $request->fabric_quantity_meters,
                        'intended_use' => $request->intended_use,
                    ]
                ]);
            }

            // Redirect directly to pattern selection (skip image upload)
            return $this->redirectToRouteWithToken('custom_orders.create.pattern');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in storeStep1', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            // Check if it's an AJAX request
            $isAjax = $request->ajax() || 
                     $request->wantsJson() || 
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     $request->header('Accept') === 'application/json';

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return $this->wizardRedirect($request, 'custom_orders.create.step1', 
                'Validation failed. Please try again.', 'step1');
                
        } catch (\Exception $e) {
            \Log::error('Exception in storeStep1', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Check if it's an AJAX request
            $isAjax = $request->ajax() || 
                     $request->wantsJson() || 
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     $request->header('Accept') === 'application/json';

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to save product selection. Please try again.',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return $this->wizardRedirect($request, 'custom_orders.create.step1', 
                'Unable to save product selection. Please try again.', 'step1', $e);
        }
    }

    /**
     * NEW: Image Upload Step (Step 2)
     */
    public function createImageUpload(Request $request)
    {
        // Validate fabric selection exists
        $wizardData = $this->getWizardData();
        if (!isset($wizardData['fabric'])) {
            return $this->redirectToRouteWithToken('custom_orders.create.step1')
                ->with('error', 'Please select a fabric first.');
        }

        return view('custom_orders.wizard.image_upload');
    }

    /**
     * Store uploaded reference image
     */
    public function storeImage(Request $request)
    {
        try {
            // Validate
            $request->validate([
                'reference_image' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB
                'description' => 'required|string|min:10|max:1000',
            ]);

            $imagePath = null;

            // Handle image upload
            if ($request->hasFile('reference_image')) {
                $image = $request->file('reference_image');
                $cloudinary = new CloudinaryService();
                
                // Try Cloudinary first (persistent storage)
                if ($cloudinary->isEnabled()) {
                    $result = $cloudinary->uploadFile($image, 'custom-orders/references');
                    if ($result) {
                        $imagePath = $result['url'];
                        \Log::info('Custom order reference image uploaded to Cloudinary', [
                            'url' => $imagePath,
                        ]);
                    }
                }
                
                // Fallback to local storage
                if (!$imagePath) {
                    $imagePath = $image->store('custom_orders/references', 'public');
                    \Log::info('Custom order reference image uploaded to local storage', [
                        'path' => $imagePath,
                    ]);
                }
            }

            // Store in DB
            $wizardData = $this->getWizardData();
            $wizardData['reference'] = [
                'image_path' => $imagePath,
                'description' => $request->description,
            ];
            $this->saveWizardData($wizardData);

            // Redirect to pattern selection
            return $this->redirectToRouteWithToken('custom_orders.create.pattern')
                ->with('success', 'Reference uploaded successfully!');

        } catch (\Exception $e) {
            \Log::error('Error uploading reference image', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Unable to upload image. Please try again.')
                ->withInput();
        }
    }

    /**
     * Step 3: Pattern Selection (New Approach)
     */
    public function createPatternSelection(Request $request)
    {
        try {
            \Log::info('createPatternSelection called');
            
            // Temporarily bypass fabric validation for testing
            // if (!$request->session()->has('wizard.fabric')) {
            //     \Log::error('No fabric in session', ['session' => $request->session()->get('wizard')]);
            //     return $this->redirectToRouteWithToken('custom_orders.create.step1')
            //         ->with('error', 'Please select a fabric first.');
            // }

            // Wizard data and flow detection
            $wizardData = $this->getWizardData();
            $isProductFlow = isset($wizardData['product']);

            // Capture preselected pattern from query and store in DB
            $patternId = $request->query('pattern_id');
            if ($patternId) {
                $wizardData['pattern_id'] = (int) $patternId;
                $this->saveWizardData($wizardData);
            }

            // Resolve product if in product flow
            $product = null;
            if ($isProductFlow && isset($wizardData['product']['id'])) {
                $product = \App\Models\Product::find($wizardData['product']['id']);
            }

            // Resolve selected pattern for highlighting in step2
            $selectedPattern = null;
            $selectedPatternId = $wizardData['pattern_id'] ?? null;
            if ($selectedPatternId) {
                $selectedPattern = \App\Models\YakanPattern::with('media')->find($selectedPatternId);
            }

            // Load all active patterns from database
            $patterns = \App\Models\YakanPattern::with('media')
                ->where('is_active', true)
                ->orderBy('created_at', 'desc')
                ->orderBy('popularity_score', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // Get fabric type from session if available
            $fabricType = $wizardData['fabric']['type'] ?? null;
            $fabricQuantity = $wizardData['fabric']['quantity_meters'] ?? null;

            // Look up fabric type name for Live Preview display
            $fabricTypeName = null;
            if ($fabricType) {
                $fabricTypeModel = \App\Models\FabricType::find($fabricType);
                $fabricTypeName = $fabricTypeModel ? $fabricTypeModel->name : ('Fabric #' . $fabricType);
            }

            return view('custom_orders.wizard.pattern_selection', [
                'product' => $product,
                'isProductFlow' => $isProductFlow,
                'selectedPattern' => $selectedPattern,
                'patterns' => $patterns,
                'fabricType' => $fabricType,
                'fabricTypeName' => $fabricTypeName,
                'fabricQuantity' => $fabricQuantity,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('createPatternSelection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->redirectToRouteWithToken('custom_orders.create.step1')
                ->with('error', 'Unable to load design interface: ' . $e->getMessage());
        }
    }

    /**
     * Store pattern/design data from step2
     */
    public function storePattern(Request $request)
    {
        try {
            $wizardData = $this->getWizardData();
            
            // Validate pattern selection data - more lenient
            $validated = $request->validate([
                'patterns' => 'required|array|min:1',
                'patterns.*' => 'integer|min:1',
                'selection_mode' => 'nullable|string',
                'product_id' => 'nullable|integer',
                'preview_image' => 'nullable|string',
                'customization_settings' => 'nullable|string',
            ]);
            
            \Log::info('Pattern storage - validated data:', $validated);
            \Log::info('Current wizard data:', $wizardData);
            
            // Parse customization settings JSON if provided
            $customizationSettings = null;
            if (isset($validated['customization_settings'])) {
                $customizationSettings = json_decode($validated['customization_settings'], true);
            }
            
            // Normalize preview image to a stored file or lightweight URL to avoid bloating the session
            $previewImageUrl = null;
            $previewImagePath = null;
            $rawPreview = trim($validated['preview_image'] ?? '');

            if ($rawPreview !== '') {
                if (str_starts_with($rawPreview, 'data:image')) {
                    $previewImagePath = $this->savePreviewImage($rawPreview);
                    $previewImageUrl = $previewImagePath ? Storage::url($previewImagePath) : null;
                } elseif (strlen($rawPreview) < 2048) {
                    // Allow small URLs but skip mega strings
                    $previewImageUrl = $rawPreview;
                }
            }

            // Store pattern selection data - use 'pattern' key to match step4 expectations
            $wizardData['pattern'] = [
                'selected_ids' => $validated['patterns'],
                'selection_mode' => $validated['selection_mode'] ?? 'single',
                'preview_image' => $previewImageUrl,
                'preview_image_path' => $previewImagePath,
                'customization_settings' => $customizationSettings,
                'created_at' => now()->toISOString(),
            ];
            
            $this->saveWizardData($wizardData);
            
            \Log::info('Pattern storage - saved:', [
                'pattern' => $wizardData['pattern'],
                'fabric' => $wizardData['fabric'] ?? null,
                'session_has_pattern' => isset($wizardData['pattern']),
            ]);
            
            // Return JSON response — go to step4 directly for fabric flow (fabric+pattern, no product)
            $token = request()->input('auth_token') ?? request()->query('auth_token') ?? session('auth_token');
            $isFabricFlow = isset($wizardData['fabric']) && !isset($wizardData['product']);
            $nextRoute = $isFabricFlow ? 'custom_orders.create.step4' : 'custom_orders.create.step3';
            $nextUrl = route($nextRoute) . ($token ? '?auth_token=' . $token : '');
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patterns saved successfully!',
                    'redirect_url' => $nextUrl,
                    'review_url' => $nextUrl,
                    'next_url' => $nextUrl,
                ]);
            }

            return $this->redirectToRouteWithToken($nextRoute);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Pattern validation error:', $e->errors());
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', array_map(fn($e) => implode(', ', $e), $e->errors())),
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Pattern storage error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save patterns: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to save patterns. Please try again.');
        }
    }

    /**
     * Step 2: Design Your Product
     */
    public function createStep2(Request $request)
    {
        try {
            $wizardData = $this->getWizardData();
            
            if (!$wizardData || !isset($wizardData['fabric'])) {
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Please select a fabric first.');
            }
            
            // Get product for design interface
            $product = null;
            if (isset($wizardData['product_id'])) {
                $product = \App\Models\Product::find($wizardData['product_id']);
            }
            
            return view('custom_orders.wizard.step2', compact('product'));
            
        } catch (\Exception $e) {
            \Log::error('Step 2 creation error: ' . $e->getMessage());
            return $this->redirectToRouteWithToken('custom_orders.create.step1')
                ->with('error', 'Unable to load design interface. Please try again.');
        }
    }

    /**
     * Store Step 2: Design Data
     */
    public function storeStep2(Request $request)
    {
        try {
            $wizardData = $this->getWizardData();
            
            // Validate design data
            $validated = $request->validate([
                'design_image' => 'required|string',
                'design_metadata' => 'required|string',
            ]);
            
            // Store design data
            $wizardData['design'] = [
                'image' => $validated['design_image'],
                'metadata' => json_decode($validated['design_metadata'], true),
                'created_at' => now()->toISOString(),
            ];
            
            $this->saveWizardData($wizardData);
            
            return $this->redirectToRouteWithToken('custom_orders.create.step3');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('Step 2 storage error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to save design. Please try again.');
        }
    }

    /**
     * Step 4: Review and Submit (Simplified for fabric orders)
     */
    public function createStep4(Request $request)
    {
        try {
            $wizardData = $this->getWizardData();
            
            // Load system settings for pricing
            $pricePerMeter = \App\Models\SystemSetting::get('price_per_meter', 500);
            $patternFeeSimple = \App\Models\SystemSetting::get('pattern_fee_simple', 0);
            $patternFeeMedium = \App\Models\SystemSetting::get('pattern_fee_medium', 0);
            $patternFeeComplex = \App\Models\SystemSetting::get('pattern_fee_complex', 0);
            
            \Log::info('createStep4 - wizard data check', [
                'has_wizard' => !empty($wizardData),
                'wizard_keys' => $wizardData ? array_keys($wizardData) : [],
                'has_pattern' => isset($wizardData['pattern']),
                'has_design' => isset($wizardData['design']),
                'has_product' => isset($wizardData['product']),
            ]);
            
            if (!$wizardData) {
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Please start your custom order.');
            }
            $userAddresses = \App\Models\UserAddress::where('user_id', auth()->id())
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
            $defaultAddress = \App\Models\UserAddress::where('user_id', auth()->id())
                ->where('is_default', true)
                ->first();

            if (isset($wizardData['product'])) {
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if (!$product) {
                    return $this->redirectToRouteWithToken('custom_orders.create.product')
                        ->with('error', 'Please select a product first.');
                }
                if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                    return $this->redirectToRouteWithToken('custom_orders.create.product.customize')
                        ->with('error', 'Please customize your design first.');
                }
                // Resolve preview image and selected patterns if available
                $previewImage = $wizardData['pattern']['preview_image'] ?? ($wizardData['design']['image'] ?? null);
                $selectedPatternIds = $wizardData['pattern']['selected_ids'] ?? [];
                $selectedPatterns = !empty($selectedPatternIds)
                    ? \App\Models\YakanPattern::with('media')->whereIn('id', $selectedPatternIds)->get()
                    : collect();
                return view('custom_orders.wizard.step4', [
                    'product' => $product,
                    'isProductFlow' => true,
                    'previewImage' => $previewImage,
                    'selectedPatterns' => $selectedPatterns,
                    'wizardData' => $wizardData,
                    'userAddresses' => $userAddresses,
                    'defaultAddress' => $defaultAddress,
                    'pricePerMeter' => $pricePerMeter,
                    'patternFeeSimple' => $patternFeeSimple,
                    'patternFeeMedium' => $patternFeeMedium,
                    'patternFeeComplex' => $patternFeeComplex,
                    'batchItems' => $wizardData['__batch_items'] ?? [],
                ]);
            }

            if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                \Log::warning('createStep4 - no pattern or design found', [
                    'wizard_keys' => array_keys($wizardData),
                    'full_wizard' => $wizardData,
                ]);
                // If we have details but no pattern, go back to pattern selection
                if (isset($wizardData['details'])) {
                    return $this->redirectToRouteWithToken('custom_orders.create.pattern')
                        ->with('error', 'Please select a pattern first.');
                }
                // If we don't have details either, start from the beginning
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Session expired. Please start your custom order again.');
            }

            // Fabric flow
            $previewImage = $wizardData['pattern']['preview_image'] ?? ($wizardData['design']['image'] ?? null);
            $selectedPatternIds = $wizardData['pattern']['selected_ids'] ?? [];
            $selectedPatterns = !empty($selectedPatternIds)
                ? \App\Models\YakanPattern::with('media')->whereIn('id', $selectedPatternIds)->get()
                : collect();
            return view('custom_orders.wizard.step4', [
                'product' => null,
                'isProductFlow' => false,
                'previewImage' => $previewImage,
                'selectedPatterns' => $selectedPatterns,
                'wizardData' => $wizardData,
                'userAddresses' => $userAddresses,
                'defaultAddress' => $defaultAddress,
                'pricePerMeter' => $pricePerMeter,
                'patternFeeSimple' => $patternFeeSimple,
                'patternFeeMedium' => $patternFeeMedium,
                'patternFeeComplex' => $patternFeeComplex,
                'batchItems' => $wizardData['__batch_items'] ?? [],
            ]);
        } catch (\Exception $e) {
            \Log::error('createStep4 error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'wizard_data' => $wizardData ?? null,
            ]);
            
            // If we have wizard data but just missing pattern, redirect to pattern selection
            if (!empty($wizardData) && !isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                return $this->redirectToRouteWithToken('custom_orders.create.pattern')
                    ->with('error', 'Please select a pattern first.');
            }
            
            return $this->redirectToRouteWithToken('custom_orders.create.step1')
                ->with('error', 'Unable to load review page. Please try again.');
        }
    }

    /**
     * Save the current wizard item to the batch and restart the wizard for a new item.
     * This allows users to include multiple custom order items under one order number.
     */
    public function addToBatch(Request $request)
    {
        try {
            $request->validate([
                'quantity'         => 'required|integer|min:1',
                'specifications'   => 'nullable|string|max:1000',
                'delivery_type'    => 'required|in:delivery,pickup',
                'address_id'       => 'required_if:delivery_type,delivery|nullable|integer|exists:user_addresses,id',
                'delivery_zip'     => 'nullable|string|max:20',
                'delivery_landmark'=> 'nullable|string|max:255',
            ]);

            $wizardData = $this->getWizardData();

            if (!$wizardData || (!isset($wizardData['fabric']) && !isset($wizardData['product']))) {
                return redirect()->back()->with('error', 'Session expired. Please start your custom order again.');
            }

            // Build a human-readable summary for the batch list display
            $fabricTypeName = '—';
            if (isset($wizardData['fabric']['type'])) {
                $ft = \App\Models\FabricType::find($wizardData['fabric']['type']);
                $fabricTypeName = $ft ? $ft->name : $wizardData['fabric']['type'];
            }
            $patternName = '—';
            if (!empty($wizardData['pattern']['selected_ids'])) {
                $p = \App\Models\YakanPattern::find($wizardData['pattern']['selected_ids'][0]);
                $patternName = $p ? $p->name : '—';
            } elseif (!empty($wizardData['pattern']['name'])) {
                $patternName = $wizardData['pattern']['name'];
            }
            $qty   = (int) $request->input('quantity', 1);
            $meters = $wizardData['fabric']['quantity_meters'] ?? null;
            $summary = "Fabric: {$fabricTypeName}, Pattern: {$patternName}, Qty: {$qty}" . ($meters ? ", {$meters}m" : '');

            // Pull only the current wizard item data (exclude any existing batch_items key)
            $currentItemWizardData = array_filter(
                $wizardData,
                fn($k) => $k !== '__batch_items',
                ARRAY_FILTER_USE_KEY
            );

            // Resolve the delivery address string to store with the item
            $resolvedDeliveryAddress = null;
            $resolvedDeliveryCity    = null;
            $resolvedDeliveryProvince= null;
            if ($request->input('delivery_type') === 'delivery') {
                $addrId = $request->input('address_id');
                if ($addrId) {
                    $addr = auth()->user()->addresses()->find($addrId);
                    if ($addr) {
                        $resolvedDeliveryAddress  = implode(', ', array_filter([
                            $addr->house_number,
                            $addr->street_name,
                            $addr->barangay,
                            $addr->city,
                            $addr->province,
                            $addr->zip_code ? 'ZIP ' . $addr->zip_code : null,
                            $addr->landmark ? 'Landmark: ' . $addr->landmark : null,
                        ]));
                        $resolvedDeliveryCity     = $addr->city;
                        $resolvedDeliveryProvince = $addr->province ?? $addr->region ?? null;
                    }
                } else {
                    // Manual entry fields
                    $resolvedDeliveryAddress = implode(', ', array_filter([
                        $request->input('delivery_house'),
                        $request->input('delivery_street'),
                        $request->input('delivery_barangay'),
                        $request->input('delivery_city'),
                        $request->input('delivery_province'),
                        $request->input('delivery_zip') ? 'ZIP ' . $request->input('delivery_zip') : null,
                        $request->input('delivery_landmark') ? 'Landmark: ' . $request->input('delivery_landmark') : null,
                    ]));
                    $resolvedDeliveryCity     = $request->input('delivery_city');
                    $resolvedDeliveryProvince = $request->input('delivery_province');
                }
            }

            $batchItem = [
                'wizard_data' => $currentItemWizardData,
                'form_data'   => [
                    'quantity'                => $qty,
                    'delivery_type'           => $request->input('delivery_type'),
                    'address_id'              => $request->input('address_id'),
                    'specifications'          => $request->input('specifications'),
                    'delivery_house'          => $request->input('delivery_house'),
                    'delivery_street'         => $request->input('delivery_street'),
                    'delivery_barangay'       => $request->input('delivery_barangay'),
                    'delivery_city'           => $request->input('delivery_city'),
                    'delivery_province'       => $request->input('delivery_province'),
                    'delivery_zip'            => $request->input('delivery_zip'),
                    'delivery_landmark'       => $request->input('delivery_landmark'),
                    'resolved_delivery_address' => $resolvedDeliveryAddress,
                    'resolved_delivery_city'    => $resolvedDeliveryCity,
                    'resolved_delivery_province'=> $resolvedDeliveryProvince,
                ],
                'summary'   => $summary,
                'added_at'  => now()->format('Y-m-d H:i:s'),
            ];

            // Append to existing batch items and save; then reset per-item wizard data
            $existingBatchItems   = $wizardData['__batch_items'] ?? [];
            $existingBatchItems[] = $batchItem;

            $this->saveWizardData(['__batch_items' => $existingBatchItems]);

            $itemCount = count($existingBatchItems);
            $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
            $url   = route('custom_orders.create.step1') . ($token ? '?auth_token=' . urlencode($token) : '');

            return redirect($url)->with(
                'success',
                "Item {$itemCount} added to your order! Customize another item below, or go back to Review to submit all items together."
            );

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('addToBatch error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to add item to batch. Please try again.');
        }
    }

    /**
     * Remove a specific item from the current wizard batch by index.
     */
    public function removeBatchItem(Request $request, int $index)
    {
        $wizardData = $this->getWizardData();
        $batchItems = $wizardData['__batch_items'] ?? [];

        if (isset($batchItems[$index])) {
            array_splice($batchItems, $index, 1);
            $wizardData['__batch_items'] = $batchItems;
            $this->saveWizardData($wizardData);
        }

        $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
        $url   = route('custom_orders.create.step4') . ($token ? '?auth_token=' . urlencode($token) : '');
        return redirect($url)->with('success', 'Item removed from your batch.');
    }

    /**
     * Create a single CustomOrder from pre-saved batch item data (wizard_data + form_data).
     * Called during completeWizard to materialise previously queued batch items.
     */
    private function createOrderFromSavedData(array $wizardData, array $formData, int $userId, ?string $batchOrderNumber): CustomOrder
    {
        $batchColumnExists = \Schema::hasColumn('custom_orders', 'batch_order_number');
        $isProductFlow = isset($wizardData['product']);
        $details       = $wizardData['details'] ?? [];
        $formQuantity  = (int) ($formData['quantity'] ?? 1);

        // ----- Price calculation (mirrors completeWizard logic) -----
        $basePrice  = 0;
        $patternFee = 0;
        $fabricCost = 0;
        $shippingFee = 0;

        if ($isProductFlow) {
            $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
            if ($product) {
                $basePrice = $product->price;
            }
        } else {
            // Pattern fee
            $patternIds = [];
            if (!empty($wizardData['pattern']['selected_ids'])) {
                $patternIds = $wizardData['pattern']['selected_ids'];
            } elseif (!empty($wizardData['pattern']['id'])) {
                $patternIds = [$wizardData['pattern']['id']];
            }
            if (!empty($patternIds)) {
                $patterns = \App\Models\YakanPattern::whereIn('id', $patternIds)->get();
                foreach ($patterns as $p) {
                    $patternFee += ($p->pattern_price ?? 0);
                }
            }
            // Fabric cost
            if (isset($wizardData['fabric']['quantity_meters']) && !empty($patternIds)) {
                $meters = (float) $wizardData['fabric']['quantity_meters'];
                $pp = \App\Models\YakanPattern::find($patternIds[0]);
                $fabricCost = $meters * ($pp ? ($pp->price_per_meter ?? 0) : 0);
            }
            $deliveryCity = $formData['resolved_delivery_city'] ?? null;
            $deliveryProvince = $formData['resolved_delivery_province'] ?? null;
            $deliveryType = $formData['delivery_type'] ?? 'delivery';
            $deliveryAddress = $formData['resolved_delivery_address'] ?? null;
            $shippingFee = $this->resolveAddressBasedShippingFee($deliveryType, $deliveryCity, $deliveryProvince, $deliveryAddress);
            $basePrice = ($patternFee + $fabricCost) * $formQuantity + $shippingFee;
        }

        // ----- Design / pattern data (mirrors completeWizard logic) -----
        $imagePath       = null;
        $patternsArray   = [];
        $designMetadata  = null;
        $designMethod    = 'pattern';
        $customizationSettings = $wizardData['pattern']['customization_settings'] ?? null;

        if (isset($wizardData['design']) && $wizardData['design']) {
            $imagePath       = $this->saveDesignImage($wizardData['design']['image']);
            $designMetadata  = $this->sanitizeDesignMetadata($wizardData['design']['metadata'] ?? []);
            $patternsArray   = $this->extractPatternsFromMetadata($designMetadata);
            $designMethod    = 'visual';
        } elseif (isset($wizardData['pattern'])) {
            $patternIds = $wizardData['pattern']['selected_ids'] ?? ($wizardData['pattern']['id'] ? [$wizardData['pattern']['id']] : []);
            $patternsArray = $patternIds;
            $patternId   = $patternIds[0] ?? null;
            $patternName = null;
            if ($patternId) {
                $pm = \App\Models\YakanPattern::find($patternId);
                if ($pm) {
                    $patternName = $pm->name;
                    if (isset($wizardData['pattern']['preview_image_path'])) {
                        $imagePath = $wizardData['pattern']['preview_image_path'];
                    } elseif (isset($wizardData['pattern']['preview_image'])) {
                        $imagePath = $wizardData['pattern']['preview_image'];
                    }
                }
            }
            $designMetadata = [
                'pattern_id'              => $patternId,
                'pattern_name'            => $patternName,
                'colors'                  => $wizardData['colors'] ?? [],
                'pattern_data'            => $wizardData['pattern_data'] ?? [],
                'customization_settings'  => $customizationSettings,
            ];
            $designMethod = 'pattern';
        }

        // ----- Delivery address -----
        $formDeliveryType = $formData['delivery_type'] ?? 'delivery';
        $formDeliveryAddr = $formData['resolved_delivery_address'] ?? null;
        $formDeliveryCity = $formData['resolved_delivery_city'] ?? ($details['delivery_city'] ?? null);
        $formDeliveryProv = $formData['resolved_delivery_province'] ?? ($details['delivery_province'] ?? null);

        // ----- Build order -----
        if ($isProductFlow) {
            $order = new CustomOrder();
            if ($batchColumnExists) $order->batch_order_number = $batchOrderNumber;
            $order->user_id            = $userId;
            $order->product_id         = $wizardData['product']['id'] ?? null;
            $order->specifications     = $formData['specifications'] ?? ($details['description'] ?? null);
            $order->quantity           = max(1, $formQuantity);
            $order->status             = 'pending';
            $order->payment_status     = 'pending';
            $order->estimated_price    = $basePrice;
            $order->delivery_type      = $formDeliveryType;
            $order->delivery_address   = $formDeliveryAddr ?: ($details['delivery_address'] ?? null);
            $order->delivery_city      = $formDeliveryCity;
            $order->delivery_province  = $formDeliveryProv;
            $order->shipping_fee       = $shippingFee;
            $order->phone              = $details['customer_phone'] ?? null;
            $order->email              = $details['customer_email'] ?? null;
            if (!empty($patternsArray)) $order->patterns = $patternsArray;
            if ($imagePath)             $order->design_upload = $imagePath;
            if ($customizationSettings) $order->customization_settings = $customizationSettings;
            $order->save();
        } else {
            $specifications = $formData['specifications'] ?? '';
            if (empty($specifications)) {
                $fabricTypeId   = $wizardData['fabric']['type'] ?? null;
                $fabricTypeName = 'N/A';
                if ($fabricTypeId) {
                    $ft = \App\Models\FabricType::find($fabricTypeId);
                    $fabricTypeName = $ft ? $ft->name : $fabricTypeId;
                }
                $intendedUseId   = $wizardData['fabric']['intended_use'] ?? null;
                $intendedUseName = 'N/A';
                if ($intendedUseId) {
                    $iu = \App\Models\IntendedUse::find($intendedUseId);
                    $intendedUseName = $iu ? $iu->name : $intendedUseId;
                }
                $specifications = "Custom Fabric Order\nFabric Type: {$fabricTypeName}\n"
                    . "Quantity: " . ($wizardData['fabric']['quantity_meters'] ?? 0) . " meters\n"
                    . "Intended Use: {$intendedUseName}";
            }
            $order = CustomOrder::create(array_merge([
                'user_id'                => $userId,
                'product_id'             => null,
                'specifications'         => $specifications,
                'patterns'               => $patternsArray ?: null,
                'quantity'               => max(1, $formQuantity),
                'estimated_price'        => $basePrice,
                'final_price'            => $basePrice,
                'status'                 => 'pending',
                'payment_status'         => 'pending',
                'design_upload'          => $imagePath,
                'design_method'          => $designMethod,
                'design_metadata'        => $designMetadata,
                'customization_settings' => $customizationSettings,
                'fabric_type'            => $wizardData['fabric']['type'] ?? null,
                'fabric_quantity_meters' => $wizardData['fabric']['quantity_meters'] ?? null,
                'intended_use'           => $wizardData['fabric']['intended_use'] ?? null,
                'fabric_specifications'  => $wizardData['fabric']['fabric_specifications'] ?? null,
                'special_requirements'   => $wizardData['fabric']['special_requirements'] ?? null,
                'delivery_type'          => $formDeliveryType,
                'delivery_address'       => $formDeliveryAddr ?: ($details['delivery_address'] ?? null),
                'delivery_city'          => $formDeliveryCity,
                'delivery_province'      => $formDeliveryProv,
                'shipping_fee'           => $shippingFee,
                'phone'                  => $details['customer_phone'] ?? null,
                'email'                  => $details['customer_email'] ?? null,
            ], $batchColumnExists ? ['batch_order_number' => $batchOrderNumber] : []));
        }

        return $order;
    }

    /**
     * Complete wizard and create order
     */
    public function completeWizard(Request $request)
    {
        \Log::info('completeWizard method called', [
            'request_method' => $request->method(),
            'request_data' => $request->all(),
            'session_has_wizard' => !empty($this->getWizardData()),
            'session_data_keys' => array_keys($this->getWizardData()),
            'full_session_data' => $this->getWizardData()
        ]);
        
        try {
            // Add basic validation - INSIDE the try block so we can catch exceptions
            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
                'specifications' => 'nullable|string|max:1000',
                'delivery_type' => 'required|in:delivery,pickup',
                'address_id' => 'required_if:delivery_type,delivery|nullable|integer|exists:user_addresses,id',
                'delivery_zip' => 'nullable|string|max:20',
                'delivery_landmark' => 'nullable|string|max:255',
            ]);
            
            $wizardData = $this->getWizardData();

            if (!$wizardData) {
                \Log::error('No wizard data found');
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Session expired. Please start your custom order again.');
            }

            // Extract previously queued batch items, then strip that key so the rest of
            // the logic only operates on the current (last) wizard item.
            $savedBatchItems = $wizardData['__batch_items'] ?? [];
            unset($wizardData['__batch_items']);

            // One batch_order_number ties every item in this submission together.
            // Check if the column exists first (safe for Railway deployments where migration may be pending).
            $batchColumnExists = \Schema::hasColumn('custom_orders', 'batch_order_number');
            $batchOrderNumber  = $batchColumnExists
                ? 'CO-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(5))
                : null;

            $isProductFlow = isset($wizardData['product']);
            $isFabricFlow = isset($wizardData['fabric']);
            $details      = $wizardData['details'] ?? [];

            if (!$isProductFlow && !$isFabricFlow) {
                \Log::error('Neither product nor fabric flow detected', ['wizard_data' => $wizardData]);
                return $this->redirectToRouteWithToken('custom_orders.create.step1')
                    ->with('error', 'Please select a fabric first.');
            }

            // Require a design selection for both flows
            if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                \Log::error('No pattern or design in wizard data', ['wizard_data' => $wizardData]);
                return $isProductFlow
                    ? $this->redirectToRouteWithToken('custom_orders.create.product.customize')->with('error', 'Please customize your design first.')
                    : $this->redirectToRouteWithToken('custom_orders.create.pattern')->with('error', 'Please select a pattern first.');
            }

            // Ensure user is authenticated
            if (!auth()->check()) {
                \Log::error('User not authenticated in completeWizard', [
                    'session_data' => $wizardData,
                    'auth_check' => auth()->check(),
                    'user_id' => auth()->id()
                ]);
                return redirect()->route('login')
                    ->with('error', 'Please login to complete your order.');
            }

            $userId = auth()->id();
            \Log::info('Creating order for user', ['user_id' => $userId]);
            \Log::info('Wizard data at order creation', [
                'isProductFlow' => $isProductFlow,
                'fabric_quantity' => $wizardData['fabric']['quantity_meters'] ?? 'MISSING',
                'fabric_type' => $wizardData['fabric']['type'] ?? 'MISSING',
                'pattern_data' => isset($wizardData['pattern']) ? 'EXISTS' : 'MISSING'
            ]);

            // Calculate base price dynamically based on order components
            $basePrice = 0;
            $patternFee = 0;
            $fabricCost = 0;
            $shippingFee = 0;
            
            if ($isProductFlow) {
                // Product-based flow
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if ($product) {
                    $basePrice = $product->price;
                }
            } else {
                // Fabric-based flow - calculate from components
                
                // 1. Calculate pattern fee based on selected patterns
                if (isset($wizardData['pattern'])) {
                    // Use each pattern's individual price
                    $patternIds = [];
                    if (isset($wizardData['pattern']['selected_ids']) && !empty($wizardData['pattern']['selected_ids'])) {
                        $patternIds = $wizardData['pattern']['selected_ids'];
                    } elseif (isset($wizardData['pattern']['id'])) {
                        $patternIds = [$wizardData['pattern']['id']];
                    }
                    
                    // Calculate total pattern fee from each pattern's individual price
                    if (!empty($patternIds)) {
                        $patterns = \App\Models\YakanPattern::whereIn('id', $patternIds)->get();
                        foreach ($patterns as $pattern) {
                            // Use pattern's individual price_per_meter (which is the pattern fee)
                            $patternFee += ($pattern->pattern_price ?? 0);
                        }
                    }
                }
                
                // 2. Calculate fabric cost (quantity × price per meter from the pattern)
                if (isset($wizardData['fabric']['quantity_meters'])) {
                    $meters = (float) $wizardData['fabric']['quantity_meters'];
                    // Get price per meter from the first selected pattern
                    $pricePerMeter = 0;
                    if (!empty($patternIds)) {
                        $pattern = \App\Models\YakanPattern::find($patternIds[0]);
                        if ($pattern) {
                            $pricePerMeter = $pattern->price_per_meter ?? 0;
                        }
                    }
                    $fabricCost = $meters * $pricePerMeter;
                    \Log::info('Fabric cost calculated', [
                        'meters' => $meters,
                        'pricePerMeter' => $pricePerMeter,
                        'fabricCost' => $fabricCost
                    ]);
                } else {
                    \Log::warning('Fabric quantity_meters NOT found in wizardData', [
                        'fabric_data' => $wizardData['fabric'] ?? 'NO FABRIC KEY'
                    ]);
                }
                
                // 3. Calculate shipping fee based on delivery address (aligned with step4 zones)
                $deliveryCity = $addrCity ?? null;
                $deliveryProvince = $addrProvince ?? null;

                if (!$deliveryCity) {
                    $defaultAddress = auth()->user()->addresses->where('is_default', true)->first();
                    if ($defaultAddress) {
                        $deliveryCity = $defaultAddress->city;
                        $deliveryProvince = $defaultAddress->province ?? $defaultAddress->region;
                    }
                }

                $shippingFee = $this->resolveAddressBasedShippingFee(
                    $validated['delivery_type'] ?? 'delivery',
                    $deliveryCity,
                    $deliveryProvince,
                    null
                );
                
                // Calculate total from components
                // Pattern fee and fabric cost are per unit, so multiply by quantity
                // Shipping stays the same regardless of quantity
                $formQuantity = $validated['quantity'];
                $basePrice = ($patternFee + $fabricCost) * $formQuantity + $shippingFee;
                
                \Log::info('Price calculation complete', [
                    'quantity' => $formQuantity,
                    'patternFee' => $patternFee,
                    'fabricCost' => $fabricCost,
                    'shippingFee' => $shippingFee,
                    'basePrice' => $basePrice
                ]);
            }

            // Handle pattern-based vs visual design
            $imagePath = null;
            $patternsArray = [];
            $complexity = 'medium';
            $designMetadata = null;
            $designMethod = 'pattern';

            if (isset($wizardData['design']) && $wizardData['design']) {
                // Visual design flow
                $imagePath = $this->saveDesignImage($wizardData['design']['image']);
                $designMetadata = $this->sanitizeDesignMetadata($wizardData['design']['metadata'] ?? []);
                $patternsArray = $this->extractPatternsFromMetadata($designMetadata);
                $complexity = $this->calculateComplexityFromMetadata($designMetadata);
                $designMethod = 'visual';
            } elseif (isset($wizardData['pattern'])) {
                // Pattern-based flow
                $patternName = null;
                $patternId = null;
                $patternDifficulty = 'medium';

                // Check for selected_ids array (from wizard pattern selection)
                if (isset($wizardData['pattern']['selected_ids']) && !empty($wizardData['pattern']['selected_ids'])) {
                    // Get the first pattern ID from the array
                    $patternId = $wizardData['pattern']['selected_ids'][0];
                    
                    // Load the pattern model
                    $patternModel = \App\Models\YakanPattern::find($patternId);
                    if ($patternModel) {
                        $patternName = $patternModel->name;
                        $patternDifficulty = $patternModel->difficulty_level ?? $patternDifficulty;
                        $patternsArray = $wizardData['pattern']['selected_ids']; // Store all selected pattern IDs
                    }
                } elseif (is_array($wizardData['pattern'])) {
                    $patternName = $wizardData['pattern']['name'] ?? null;
                    $patternId = $wizardData['pattern']['id'] ?? null;
                    $patternDifficulty = $wizardData['pattern']['difficulty'] ?? $patternDifficulty;
                    
                    // Extract preview image from pattern data
                    if (isset($wizardData['pattern']['preview_image_path'])) {
                        $imagePath = $wizardData['pattern']['preview_image_path'];
                    } elseif (isset($wizardData['pattern']['preview_image'])) {
                        $imagePath = $wizardData['pattern']['preview_image'];
                    }
                } else {
                    $patternName = $wizardData['pattern'];
                }

                // Try to resolve pattern details from DB if not already loaded
                if (!$patternId && ($patternId || $patternName)) {
                    $patternModel = null;
                    if ($patternId) {
                        $patternModel = \App\Models\YakanPattern::find($patternId);
                    }
                    if (!$patternModel && $patternName) {
                        $patternModel = \App\Models\YakanPattern::where('name', $patternName)->first();
                    }
                    if ($patternModel) {
                        $patternName = $patternModel->name;
                        $patternId = $patternModel->id;
                        $patternDifficulty = $patternModel->difficulty_level ?? $patternDifficulty;
                    }
                }

                // Set patterns array if not already set
                if (empty($patternsArray)) {
                    $patternsArray = $patternId ? [$patternId] : array_values(array_filter([$patternName]));
                }
                $complexity = $patternDifficulty;
                
                // Include customization settings and preview in metadata
                $designMetadata = [
                    'pattern_id' => $patternId,
                    'pattern_name' => $patternName,
                    'colors' => $wizardData['colors'] ?? [],
                    'pattern_data' => $wizardData['pattern_data'] ?? [],
                    'customization_settings' => $wizardData['pattern']['customization_settings'] ?? null,
                ];
                $designMethod = 'pattern';
            }

            // Allow step4 form to override some details (quantity, specs, delivery)
            $formQuantity       = (int) ($validated['quantity'] ?? 1);
            $formSpecifications = $validated['specifications'] ?? null;
            $formDeliveryType   = $validated['delivery_type'];
            $selectedAddressId  = $validated['address_id'] ?? null;
            // Structured delivery fields from step4
            $addrHouse     = $validated['delivery_house'] ?? null;
            $addrStreet    = $validated['delivery_street'] ?? null;
            $addrBarangay  = $validated['delivery_barangay'] ?? null;
            $addrCity      = $validated['delivery_city'] ?? null;
            $addrProvince  = $validated['delivery_province'] ?? null;
            $addrZip       = $validated['delivery_zip'] ?? null;
            $addrLandmark  = $validated['delivery_landmark'] ?? null;

            // If user picked a saved address (radio), resolve city/province from that address.
            if ($formDeliveryType === 'delivery' && !$addrCity && !empty($selectedAddressId)) {
                $selectedAddress = auth()->user()->addresses()->find($selectedAddressId);
                if ($selectedAddress) {
                    $addrCity = $selectedAddress->city;
                    $addrProvince = $selectedAddress->province ?? $selectedAddress->region;
                }
            }
            
            // Extract customization settings
            $customizationSettings = $wizardData['pattern']['customization_settings'] ?? null;

            $formDeliveryAddr = null;
            if ($formDeliveryType === 'delivery') {
                $parts = array_filter([
                    $addrHouse,
                    $addrStreet,
                    $addrBarangay,
                    $addrCity,
                    $addrProvince,
                    $addrZip ? 'ZIP ' . $addrZip : null,
                    $addrLandmark ? 'Landmark: ' . $addrLandmark : null,
                ]);
                if (!empty($parts)) {
                    $formDeliveryAddr = implode(', ', $parts);
                }

                if (!$formDeliveryAddr && !empty($selectedAddressId)) {
                    $selectedAddress = auth()->user()->addresses()->find($selectedAddressId);
                    if ($selectedAddress) {
                        $formDeliveryAddr = implode(', ', array_filter([
                            $selectedAddress->house_number,
                            $selectedAddress->street_name,
                            $selectedAddress->barangay,
                            $selectedAddress->city,
                            $selectedAddress->province,
                            $selectedAddress->zip_code ? 'ZIP ' . $selectedAddress->zip_code : null,
                            $selectedAddress->landmark ? 'Landmark: ' . $selectedAddress->landmark : null,
                        ]));
                    }
                }
            }

            // Create custom order
            if ($isProductFlow) {
                // Create a product-based custom order (safe property assignment)
                $order = new CustomOrder();
                if ($batchColumnExists) $order->batch_order_number = $batchOrderNumber;
                $order->user_id = $userId;
                $order->product_id = $wizardData['product']['id'] ?? null;
                $order->specifications = $formSpecifications ?? ($details['description'] ?? null);
                $order->quantity = max(1, $formQuantity);
                $order->status = 'pending';
                $order->payment_status = 'pending';
                $order->estimated_price = $basePrice;
                // Contact and delivery info
                $order->delivery_type = $formDeliveryType ?: ($details['delivery_type'] ?? null);
                $order->delivery_address = $formDeliveryAddr ?: ($details['delivery_address'] ?? null);
                $order->delivery_city = $addrCity ?? ($details['delivery_city'] ?? null);
                $order->delivery_province = $addrProvince ?? ($details['delivery_province'] ?? null);
                $order->shipping_fee = $shippingFee;
                $order->phone = $details['customer_phone'] ?? null;
                $order->email = $details['customer_email'] ?? null;
                if (!empty($patternsArray)) {
                    $order->patterns = $patternsArray;
                }
                if ($imagePath) {
                    $order->design_upload = $imagePath;
                }
                if ($customizationSettings) {
                    $order->customization_settings = $customizationSettings;
                }
                $order->save();
                $customOrder = $order;
            } else {
                // Fabric flow creation - use request input for specifications
                $specifications = $formSpecifications ?? '';
                
                // Add fabric details to specifications if empty
                if (empty($specifications)) {
                    // Look up fabric type and intended use names from IDs
                    $fabricTypeId = $wizardData['fabric']['type'] ?? null;
                    $fabricTypeName = 'N/A';
                    if ($fabricTypeId) {
                        $fabricType = \App\Models\FabricType::find($fabricTypeId);
                        $fabricTypeName = $fabricType ? $fabricType->name : $fabricTypeId;
                    }
                    
                    $intendedUseId = $wizardData['fabric']['intended_use'] ?? null;
                    $intendedUseName = 'N/A';
                    if ($intendedUseId) {
                        $intendedUse = \App\Models\IntendedUse::find($intendedUseId);
                        $intendedUseName = $intendedUse ? $intendedUse->name : $intendedUseId;
                    }
                    
                    $specifications = "Custom Fabric Order\n";
                    $specifications .= "Fabric Type: " . $fabricTypeName . "\n";
                    $specifications .= "Quantity: " . ($wizardData['fabric']['quantity_meters'] ?? 0) . " meters\n";
                    $specifications .= "Intended Use: " . $intendedUseName;
                }
                
                $orderData = [
                    'user_id' => $userId,
                    'product_id' => null, // No product for fabric orders
                    'specifications' => $specifications,
                    'patterns' => $patternsArray ?: null,
                    'quantity' => max(1, $formQuantity),
                    'estimated_price' => $basePrice,
                    'final_price' => $basePrice,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'design_upload' => $imagePath,
                    'design_method' => $designMethod,
                    'design_metadata' => $designMetadata,
                    'customization_settings' => $customizationSettings,
                    
                    // Fabric-specific fields
                    'fabric_type' => $wizardData['fabric']['type'] ?? null,
                    'fabric_quantity_meters' => $wizardData['fabric']['quantity_meters'] ?? null,
                    'intended_use' => $wizardData['fabric']['intended_use'] ?? null,
                    'fabric_specifications' => $wizardData['fabric']['fabric_specifications'] ?? null,
                    'special_requirements' => $wizardData['fabric']['special_requirements'] ?? null,

                    // Contact and delivery info
                    'delivery_type' => $formDeliveryType ?: ($details['delivery_type'] ?? null),
                    'delivery_address' => $formDeliveryAddr ?: ($details['delivery_address'] ?? null),
                    'delivery_city' => $addrCity ?? ($details['delivery_city'] ?? null),
                    'delivery_province' => $addrProvince ?? ($details['delivery_province'] ?? null),
                    'shipping_fee' => $shippingFee,
                    'phone' => $details['customer_phone'] ?? null,
                    'email' => $details['customer_email'] ?? null,
                ];
                if ($batchColumnExists) $orderData['batch_order_number'] = $batchOrderNumber;
                
                \Log::info('About to create fabric order', ['orderData' => $orderData]);
                
                $customOrder = CustomOrder::create($orderData);
                
                \Log::info('Fabric order created successfully', [
                    'order_id' => $customOrder->id,
                    'fabric_type' => $customOrder->fabric_type,
                    'quantity' => $customOrder->quantity,
                ]);
            }

            // Create orders for all previously queued batch items using the same batch number
            $allOrders = [$customOrder];
            foreach ($savedBatchItems as $batchItem) {
                try {
                    $bOrder = $this->createOrderFromSavedData(
                        $batchItem['wizard_data'],
                        $batchItem['form_data'],
                        $userId,
                        $batchOrderNumber
                    );
                    $allOrders[] = $bOrder;
                    \Log::info('Batch item order created', ['order_id' => $bOrder->id, 'batch' => $batchOrderNumber]);
                } catch (\Exception $batchEx) {
                    \Log::error('Failed to create batch item order: ' . $batchEx->getMessage());
                }
            }

            // Clear wizard session and backups
            $this->clearWizardSession($request);

            // Determine notification wording based on item count
            $orderCount   = count($allOrders);
            $batchLabel   = $orderCount > 1 ? " (Batch #{$batchOrderNumber}, {$orderCount} items)" : "";
            $orderIdsText = implode(', #', array_map(fn($o) => $o->id, $allOrders));

            // Create notification for user
            \App\Models\Notification::createNotification(
                $userId,
                'custom_order',
                'Custom Order Submitted',
                "Your custom order{$batchLabel} has been submitted successfully. Order ID(s): #{$orderIdsText}. Pending admin review.",
                route('custom_orders.show', $customOrder->id),
                [
                    'order_id'         => $customOrder->id,
                    'batch_order_number' => $batchOrderNumber,
                    'order_count'      => $orderCount,
                    'order_name'       => $orderCount > 1 ? "Batch Order #{$batchOrderNumber}" : 'Custom Order #' . $customOrder->id,
                    'estimated_price'  => $customOrder->estimated_price
                ]
            );

            // Create notification for admins
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                \App\Models\Notification::createNotification(
                    $admin->id,
                    'custom_order',
                    'New Custom Order',
                    "A new custom order{$batchLabel} has been submitted by {$customOrder->user->name}. Order ID(s): #{$orderIdsText}.",
                    url('/admin/custom-orders'),
                    [
                        'order_id'           => $customOrder->id,
                        'batch_order_number' => $batchOrderNumber,
                        'order_count'        => $orderCount,
                        'customer_name'      => $customOrder->user->name,
                        'order_name'         => $orderCount > 1 ? "Batch Order #{$batchOrderNumber}" : 'Custom Order #' . $customOrder->id,
                        'estimated_price'    => $customOrder->estimated_price
                    ]
                );
            }

            // Redirect to success page
            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                $token = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
                $successUrl = route('custom_orders.success', ['order' => $customOrder->id]) . ($token ? '?auth_token=' . urlencode($token) : '');
                return response()->json(['success' => true, 'message' => 'Order submitted successfully!', 'redirect_url' => $successUrl]);
            }
            return $this->redirectToRouteWithToken('custom_orders.success', ['order' => $customOrder->id]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed in completeWizard:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);

            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Validation failed.', 'errors' => $e->errors()], 422);
            }
            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \Log::error('Error completing wizard:', [
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            $isAjax = $request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest';
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create your order. Please try again. (' . $e->getMessage() . ')'
                ], 500);
            }
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create your order. Please try again.']);
        }
    }

    /**
     * Show success page with admin link
     */
    public function success($orderId)
    {
        \Log::info('Success page accessed', ['order_id' => $orderId]);
        
        try {
            $order = CustomOrder::findOrFail($orderId);
            
            \Log::info('Order found, rendering success page', ['order' => $order->id]);

            // Load all orders in the same submission batch (by batch_order_number or same-minute fallback)
            $batchOrders = $this->getUserBatchOrders($order, (int) $order->user_id)
                ->load(['user:id,name,email', 'product:id,name,price,image', 'fabricType:id,name', 'intendedUse:id,name']);
            
            return view('custom_orders.success', compact('order', 'batchOrders'));
            
        } catch (\Exception $e) {
            \Log::error('Success page error: ' . $e->getMessage());
            
            return $this->redirectToRouteWithToken('custom_orders.index')
                ->with('error', 'Order not found. Please check your order history.');
        }
    }

    /**
     * Store a new custom order (supports both text and visual designs)
     */
    public function store(Request $request)
    {
        // Debug logging
        \Log::info('Custom Order Store Request Data:', [
            'design_method' => $request->design_method,
            'product_ids' => $request->product_ids,
            'patterns' => $request->patterns,
            'specifications' => $request->specifications,
            'all_request_data' => $request->all()
        ]);

        // Handle visual design submissions
        if ($request->design_method === 'visual') {
            return $this->storeVisualDesign($request);
        }

        // Handle text-based submissions (existing logic)
        $request->validate([
            'product_ids' => 'required|string', // JSON string of product objects
            'specifications' => 'nullable|string|max:1000',
            'design_upload' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'patterns' => 'nullable', // Can be string (JSON) or array
        ]);

        try {
            // Decode product IDs JSON
            $productData = json_decode($request->product_ids, true);
            if (!is_array($productData) || empty($productData)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['product_ids' => 'Please select at least one product.']);
            }

            // Debug log product data
            \Log::info('Decoded Product Data:', $productData);

            // Validate each product
            foreach ($productData as $product) {
                if (!isset($product['id']) || !isset($product['quantity']) || $product['quantity'] <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['product_ids' => 'Invalid product selection. Please try again.']);
                }

                // Verify product exists
                if (!Product::find($product['id'])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['product_ids' => 'One or more selected products are not available.']);
                }
            }

            // Process patterns data once
            $processedPatterns = null;
            if ($request->patterns) {
                // Handle both string (JSON) and array formats
                if (is_string($request->patterns)) {
                    $processedPatterns = json_decode($request->patterns, true);
                } else {
                    $processedPatterns = $request->patterns;
                }
                
                \Log::info('Patterns Data Received:', [
                    'raw_patterns' => $request->patterns,
                    'processed_patterns' => $processedPatterns,
                    'is_array' => is_array($processedPatterns)
                ]);
            } else {
                \Log::info('No patterns data received');
            }

            // Create orders for each selected product
            $createdOrders = [];
            foreach ($productData as $productData) {
                $order = new CustomOrder();
                $order->user_id = Auth::id();
                $order->product_id = $productData['id'];
                $order->quantity = $productData['quantity'];
                $order->specifications = $request->specifications;
                $order->status = 'pending';
                $order->payment_status = 'unpaid';

                // Store patterns if provided
                if ($processedPatterns && is_array($processedPatterns)) {
                    $order->patterns = $processedPatterns; // Store as array, will be cast to JSON
                    \Log::info('Patterns stored for order ' . $order->id . ':', $processedPatterns);
                }

                if ($request->hasFile('design_upload')) {
                    $designFile = $request->file('design_upload');
                    $cloudinary = new CloudinaryService();
                    $storedPath = null;
                    
                    // Try Cloudinary first (persistent storage)
                    if ($cloudinary->isEnabled()) {
                        $result = $cloudinary->uploadFile($designFile, 'custom-orders/designs');
                        if ($result) {
                            $storedPath = $result['url'];
                            \Log::info('Custom order design uploaded to Cloudinary', [
                                'url' => $storedPath,
                            ]);
                        }
                    }
                    
                    // Fallback to local storage
                    if (!$storedPath) {
                        $storedPath = $designFile->store('custom_designs', 'public');
                        \Log::info('Custom order design uploaded to local storage', [
                            'path' => $storedPath,
                        ]);
                    }
                    
                    $order->design_upload = $storedPath;
                }

                $order->save();
                $createdOrders[] = $order;
                
                \Log::info('Order created:', [
                    'order_id' => $order->id,
                    'product_id' => $order->product_id,
                    'patterns' => $order->patterns,
                    'specifications' => $order->specifications
                ]);
            }

            $message = count($createdOrders) === 1 
                ? 'Custom order created successfully!' 
                : count($createdOrders) . ' custom orders created successfully!';

            return $this->redirectToRouteWithToken('custom_orders.index')->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Custom order creation failed: ' . $e->getMessage());
            \Log::error('Exception details:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'An error occurred while creating your order. Please try again.']);
        }
    }

    /**
     * Show a single custom order
     */
    public function show($id)
    {
        try {
            // Ensure user is authenticated
            if (!Auth::check()) {
                \Log::warning('CustomOrder show - user not authenticated', [
                    'order_id' => $id,
                    'redirect_to' => 'login'
                ]);
                return redirect()->route('login')->with('error', 'Please log in to view your order.');
            }

            // Find the order manually to catch any issues
            $order = CustomOrder::findOrFail($id);
            
            // Debug: Log authentication and authorization details
            \Log::info('CustomOrder show access attempt', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'authenticated_user_id' => Auth::id(),
                'is_authenticated' => Auth::check(),
                'user_email' => Auth::user()?->email,
                'matches' => $order->user_id === Auth::id()
            ]);

            // Check ownership
            if ($order->user_id !== Auth::id()) {
                \Log::warning('CustomOrder access denied - ownership mismatch', [
                    'order_id' => $order->id,
                    'order_user_id' => $order->user_id,
                    'authenticated_user_id' => Auth::id(),
                    'reason' => 'Order does not belong to authenticated user'
                ]);
                return redirect()->route('custom_orders.index')->with('error', 'You do not have permission to view this order.');
            }

            $order->load('product');
            $batchOrders = $this->getUserBatchOrders($order, Auth::id());
            $isBatchOrder = $batchOrders->count() > 1;
            $batchPaymentTotal = $this->calculateOrdersTotal(
                $batchOrders->where('payment_status', '!=', 'paid')->values()
            );

            return view('custom_orders.show', compact('order', 'batchOrders', 'isBatchOrder', 'batchPaymentTotal'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('CustomOrder not found', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('custom_orders.index')->with('error', 'Custom order not found.');
            
        } catch (\Exception $e) {
            \Log::error('CustomOrder show error', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('custom_orders.index')->with('error', 'An error occurred while loading the order.');
        }
    }

    /**
     * Handle customer response to a price quote (accept or cancel)
     */
    public function respondToQuote(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'action' => 'required|in:accept,cancel',
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($request->action === 'accept') {
            $order->status = 'processing';
            $order->payment_status = 'unpaid';
            $order->save();
            $message = 'You have accepted the price quote. Please complete your payment.';
            return $this->redirectToRouteWithToken('custom_orders.payment', $order)->with('success', $message);
        } else {
            $order->status = 'cancelled';
            $order->rejection_reason = $request->reason;
            $order->save();
            $message = 'You have cancelled the price quote.';
            return $this->redirectToRouteWithToken('custom_orders.show', $order)->with('success', $message);
        }
    }

    /**
     * Show payment method selection page
     */
    public function showPayment(CustomOrder $order)
    {
        try {
            \Log::info('showPayment method called', [
                'order_id' => $order->id,
                'auth_check' => auth()->check(),
                'order_user_id' => $order->user_id,
                'current_user_id' => auth()->id()
            ]);

            if (!auth()->check() || $order->user_id !== auth()->id()) {
                \Log::error('showPayment - Unauthorized', [
                    'order_id' => $order->id,
                    'auth_check' => auth()->check(),
                    'order_user_id' => $order->user_id,
                    'current_user_id' => auth()->id()
                ]);
                abort(403, 'Unauthorized');
            }

            $batchOrders = $this->getUserBatchOrders($order, auth()->id());
            $isBatchPayment = $batchOrders->count() > 1;
            $paymentOrders = $batchOrders->where('payment_status', '!=', 'paid')->values();

            if ($paymentOrders->isEmpty()) {
                \Log::info('showPayment - Order already paid', [
                    'order_id' => $order->id,
                    'payment_status' => $order->payment_status
                ]);
                return $this->redirectToRouteWithToken('custom_orders.show', $order)->with('info', 'This order is already paid.');
            }

            $notApproved = $paymentOrders->where('status', '!=', 'approved')->values();
            if ($notApproved->isNotEmpty()) {
                // If the anchor order itself is approved, filter to only approved orders and proceed
                if ($order->status === 'approved') {
                    $paymentOrders = $paymentOrders->where('status', 'approved')->values();
                    if ($paymentOrders->isEmpty()) {
                        return $this->redirectToRouteWithToken('custom_orders.show', $order)
                            ->with('info', 'Payment is only available after admin approval.');
                    }
                } else {
                    \Log::info('showPayment - Order not yet approved', [
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'not_approved_count' => $notApproved->count(),
                    ]);
                    if ($isBatchPayment) {
                        return $this->redirectToRouteWithToken('custom_orders.show', $order)
                            ->with('info', 'Payment for this batch will be available once all items are approved by admin.');
                    }
                    return $this->redirectToRouteWithToken('custom_orders.show', $order)
                        ->with('info', 'Payment is only available after admin approval. Your order is currently ' . $order->status . '.');
                }
            }

            \Log::info('showPayment - Loading relationships', [
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'user_id' => $order->user_id
            ]);

            // Load related product and user so the payment page can show full summary
            $order->load(['product', 'user']);
            $paymentOrders->load(['product', 'user', 'fabricType', 'intendedUse']);
            $paymentTotal = $this->calculateOrdersTotal($paymentOrders);
            
            \Log::info('showPayment - Rendering view', [
                'order_id' => $order->id,
                'product_loaded' => isset($order->product),
                'product_name' => $order->product->name ?? 'NULL',
                'user_loaded' => isset($order->user),
                'user_name' => $order->user->name ?? 'NULL',
                'is_batch_payment' => $isBatchPayment,
                'batch_items' => $paymentOrders->count(),
                'payment_total' => $paymentTotal,
            ]);

            $authToken = request()->input('auth_token')
                ?? request()->query('auth_token')
                ?? session('auth_token')
                ?? '';
            
            // If no token exists but user is authenticated, create/retrieve one
            // This ensures the payment form will have a valid token for submission
            if (!$authToken && auth()->check()) {
                $authToken = \DB::table('auth_tokens')
                    ->where('user_id', auth()->id())
                    ->where('expires_at', '>', now())
                    ->orderByDesc('updated_at')
                    ->value('token');
                
                if (!$authToken) {
                    $authToken = \Illuminate\Support\Str::random(64);
                    \DB::table('auth_tokens')->insert([
                        'user_id' => auth()->id(),
                        'token' => $authToken,
                        'expires_at' => now()->addDays(30),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    \Log::info('Created new auth_token for payment page', [
                        'user_id' => auth()->id(),
                        'order_id' => $order->id
                    ]);
                }
                
                session(['auth_token' => $authToken]);
            }

            return view('custom_orders.payment', compact('order', 'paymentOrders', 'isBatchPayment', 'paymentTotal', 'authToken'));
            
        } catch (\Exception $e) {
            \Log::error('showPayment error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Payment page error: ' . $e->getMessage());
        }
    }

    /**
     * Process payment method selection
     */
    public function processPayment(Request $request, $id)
    {
        // Debug log - this should appear if the request reaches this method
        \Log::info('=== CUSTOM ORDER PAYMENT PROCESS STARTED ===', [
            'order_id' => $id,
            'method' => $request->method(),
            'path' => $request->path(),
            'has_auth_token_query' => $request->has('auth_token'),
            'has_auth_token_input' => $request->filled('auth_token'),
            'auth_check_before' => Auth::check(),
            'auth_id_before' => Auth::id(),
            'session_id' => session()->getId()
        ]);
        
        try {
            // Handle auth_token authentication if not already authenticated
            if (!Auth::check()) {
                $token = $request->input('auth_token') 
                    ?? $request->query('auth_token') 
                    ?? session('auth_token');
                
                \Log::info('processPayment: Attempting token auth', [
                    'has_token' => !empty($token),
                    'token_prefix' => $token ? substr($token, 0, 10) . '...' : null
                ]);
                
                if ($token) {
                    $authToken = \DB::table('auth_tokens')
                        ->where('token', $token)
                        ->where('expires_at', '>', now())
                        ->first();
                    
                    if ($authToken) {
                        $user = User::find($authToken->user_id);
                        if ($user) {
                            Auth::login($user, true);
                            session(['auth_token' => $token]);
                            
                            // Keep token alive
                            \DB::table('auth_tokens')
                                ->where('token', $token)
                                ->update(['expires_at' => now()->addDays(30), 'updated_at' => now()]);
                            
                            \Log::info('Payment processing - authenticated via token', [
                                'user_id' => $user->id,
                                'order_id' => $id
                            ]);
                        }
                    }
                }
                
                // If still not authenticated after token check, redirect to login
                if (!Auth::check()) {
                    \Log::warning('Payment processing failed - not authenticated', [
                        'order_id' => $id,
                        'has_token' => !empty($token)
                    ]);
                    
                    return redirect()->route('login.user.form')
                        ->with('error', 'Please login to continue with payment.')
                        ->with('redirect_to', route('custom_orders.payment', $id));
                }
            }
            
            $order = CustomOrder::findOrFail($id);
            
            // Verify ownership
            if ($order->user_id !== Auth::id()) {
                \Log::error('Payment processing - unauthorized access attempt', [
                    'order_id' => $id,
                    'order_user_id' => $order->user_id,
                    'current_user_id' => Auth::id()
                ]);
                abort(403, 'Unauthorized access to this order.');
            }

            $request->validate([
                'payment_method' => 'required|in:online_banking,bank_transfer',
                'shipping_fee'   => 'nullable|numeric|min:0',
                'delivery_city'  => 'nullable|string|max:255',
                'delivery_province' => 'nullable|string|max:255',
            ]);

            $paymentOrders = $this->getUserBatchOrders($order, Auth::id())
                ->where('payment_status', '!=', 'paid')
                ->values();

            if ($paymentOrders->isEmpty()) {
                return $this->redirectToRouteWithToken('custom_orders.show', $order)
                    ->with('info', 'This order is already paid.');
            }

            $notApproved = $paymentOrders->where('status', '!=', 'approved')->values();
            if ($notApproved->isNotEmpty()) {
                return back()->with('error', 'Payment is only available after admin approval of all items in this order.');
            }

            $isBatchPayment = $paymentOrders->count() > 1;
            $selectedPaymentMethod = $request->payment_method;
            $shippingFee = (float) ($request->shipping_fee ?? $order->shipping_fee ?? 0);

            $generatedTransactionId = null;

            // Handle Maya redirect checkout
            if ($selectedPaymentMethod === 'online_banking' && config('services.maya.enabled', false)) {
                try {
                    $publicKey  = config('services.maya.public_key');
                    $baseUrl    = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');
                    $amount     = (float) $this->calculateOrdersTotal($paymentOrders);
                    $buyer      = $order->user ?? \App\Models\User::find($order->user_id);
                    $authTokenForCallback = $request->input('auth_token') ?? $request->query('auth_token') ?? session('auth_token');
                    $tokenQuery = $authTokenForCallback ? '?auth_token=' . urlencode($authTokenForCallback) : '';
                    $successUrl = route('custom_orders.payment.maya.success', $order->id) . $tokenQuery;
                    $failureUrl = route('custom_orders.payment.maya.failed', $order->id) . $tokenQuery;

                    $payload = [
                        'totalAmount' => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                        'requestReferenceNumber' => 'ORD-CO-' . $order->id,
                        'redirectUrl' => ['success' => $successUrl, 'failure' => $failureUrl, 'cancel' => $failureUrl],
                        'buyer' => [
                            'firstName' => $buyer ? explode(' ', $buyer->name)[0] : 'Customer',
                            'lastName'  => $buyer ? (explode(' ', $buyer->name, 2)[1] ?? '-') : '-',
                            'contact'   => ['email' => $buyer->email ?? ''],
                        ],
                        'items' => [[
                            'name'        => 'Custom Order #' . $order->id,
                            'quantity'    => 1,
                            'totalAmount' => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                        ]],
                    ];

                    $response = \Illuminate\Support\Facades\Http::withBasicAuth($publicKey, '')
                        ->acceptJson()
                        ->post($baseUrl . '/checkout/v1/checkouts', $payload);

                    if ($response->successful()) {
                        $data        = $response->json() ?? [];
                        $checkoutId  = $data['checkoutId'] ?? $data['id'] ?? null;
                        $checkoutUrl = $data['redirectUrl'] ?? $data['checkoutUrl'] ?? $data['url'] ?? null;

                        if ($checkoutId && $checkoutUrl) {
                            foreach ($paymentOrders as $paymentOrder) {
                                $paymentOrder->payment_method = 'online_banking';
                                $paymentOrder->transaction_id = $checkoutId;
                                $paymentOrder->payment_status = 'pending';
                                if ($request->filled('shipping_fee') && $paymentOrder->id === $order->id) {
                                    $paymentOrder->shipping_fee = (float) $request->shipping_fee;
                                }
                                $paymentOrder->save();
                            }
                            return redirect($checkoutUrl);
                        }
                    }

                    \Log::error('Maya checkout failed for custom order', [
                        'order_id' => $order->id,
                        'status'   => $response->status(),
                        'body'     => $response->body(),
                    ]);
                } catch (\Throwable $e) {
                    \Log::error('Maya checkout exception for custom order: ' . $e->getMessage());
                }
                // Fall through to bank transfer instructions on failure
                $selectedPaymentMethod = 'bank_transfer';
            }

            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->payment_method = $selectedPaymentMethod;

                // Delivery/shipping details are written to the anchor order,
                // while other batch items reuse their existing delivery data.
                if ($paymentOrder->id === $order->id) {
                    $paymentOrder->shipping_fee = $shippingFee;
                    if ($request->filled('delivery_city')) {
                        $paymentOrder->delivery_city = $request->delivery_city;
                    }
                    if ($request->filled('delivery_province')) {
                        $paymentOrder->delivery_province = $request->delivery_province;
                    }
                }

                if ($generatedTransactionId) {
                    $paymentOrder->transaction_id = $generatedTransactionId;
                }

                $paymentOrder->save();
            }

            $order->refresh();
            
            return $this->redirectToRouteWithToken('custom_orders.payment.instructions', $order);

            return back()->with('error', 'Payment initialization failed');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Custom Order not found');
        } catch (\Exception $e) {
            \Log::error('Payment processing failed: ' . $e->getMessage());
            return back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    /**
     * AJAX endpoint to initiate payment and return redirect URL as JSON
     */
    public function initiatePaymentAjax(Request $request, $id)
    {
        try {
            \Log::info('=== INITIATE PAYMENT AJAX ===', [
                'order_id' => $id,
                'has_auth_token' => !empty($request->input('auth_token') ?? $request->header('X-Auth-Token'))
            ]);
            
            // Handle auth_token authentication
            if (!Auth::check()) {
                $token = $request->input('auth_token') 
                    ?? $request->header('X-Auth-Token')
                    ?? $request->query('auth_token') 
                    ?? session('auth_token');
                
                if ($token) {
                    $authToken = \DB::table('auth_tokens')
                        ->where('token', $token)
                        ->where('expires_at', '>', now())
                        ->first();
                    
                    if ($authToken) {
                        $user = User::find($authToken->user_id);
                        if ($user) {
                            Auth::login($user, true);
                            session(['auth_token' => $token]);
                            
                            \DB::table('auth_tokens')
                                ->where('token', $token)
                                ->update(['expires_at' => now()->addDays(30), 'updated_at' => now()]);
                        }
                    }
                }
            }
            
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Authentication required. Please login and try again.'
                ], 401);
            }
            
            $order = CustomOrder::findOrFail($id);
            
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to this order.'
                ], 403);
            }
            
            $paymentMethod = $request->input('payment_method');
            if (!in_array($paymentMethod, ['online_banking', 'bank_transfer'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid payment method selected.'
                ], 400);
            }
            
            $paymentOrders = $this->getUserBatchOrders($order, Auth::id())
                ->where('payment_status', '!=', 'paid')
                ->values();
                
            if ($paymentOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'This order is already paid.'
                ], 400);
            }
            
            $notApproved = $paymentOrders->where('status', '!=', 'approved')->values();
            if ($notApproved->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment is only available after admin approval.'
                ], 400);
            }
            
            $shippingFee = (float) ($request->input('shipping_fee') ?? $order->shipping_fee ?? 0);
            
            // If shipping fee not provided, calculate from user's address
            if ($shippingFee <= 0 && $order->delivery_type !== 'pickup') {
                $shippingFee = $this->resolveAddressBasedShippingFee(
                    $order->delivery_type ?? 'delivery',
                    $request->input('delivery_city') ?? $order->delivery_city ?? '',
                    $request->input('delivery_province') ?? $order->delivery_province ?? '',
                    $order->delivery_address ?? ''
                );
            }
            
            $authTokenForCallback = $request->input('auth_token') ?? $request->header('X-Auth-Token') ?? session('auth_token') ?? '';
            
            // Handle Maya checkout
            if ($paymentMethod === 'online_banking' && config('services.maya.enabled', false)) {
                try {
                    $publicKey  = config('services.maya.public_key');
                    $baseUrl    = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');
                    $itemsSubtotal = (float) $this->calculateOrdersTotal($paymentOrders);
                    $amount     = $itemsSubtotal + $shippingFee; // Include shipping in total
                    $buyer      = $order->user ?? User::find($order->user_id);
                    
                    $tokenQuery = $authTokenForCallback ? '?auth_token=' . urlencode($authTokenForCallback) : '';
                    $successUrl = route('custom_orders.payment.maya.success', $order->id) . $tokenQuery;
                    $failureUrl = route('custom_orders.payment.maya.failed', $order->id) . $tokenQuery;
                    
                    // Build items array
                    $items = [[
                        'name'        => 'Custom Order #' . $order->id . ($paymentOrders->count() > 1 ? ' (Batch)' : ''),
                        'quantity'    => 1,
                        'totalAmount' => ['value' => number_format($itemsSubtotal, 2, '.', ''), 'currency' => 'PHP'],
                    ]];
                    
                    // Add shipping as separate item if applicable
                    if ($shippingFee > 0) {
                        $items[] = [
                            'name'        => 'Shipping Fee',
                            'quantity'    => 1,
                            'totalAmount' => ['value' => number_format($shippingFee, 2, '.', ''), 'currency' => 'PHP'],
                        ];
                    }

                    $payload = [
                        'totalAmount' => ['value' => number_format($amount, 2, '.', ''), 'currency' => 'PHP'],
                        'requestReferenceNumber' => 'ORD-CO-' . $order->id . '-' . time(),
                        'redirectUrl' => ['success' => $successUrl, 'failure' => $failureUrl, 'cancel' => $failureUrl],
                        'buyer' => [
                            'firstName' => $buyer ? explode(' ', $buyer->name)[0] : 'Customer',
                            'lastName'  => $buyer ? (explode(' ', $buyer->name, 2)[1] ?? '-') : '-',
                            'contact'   => ['email' => $buyer->email ?? ''],
                        ],
                        'items' => $items,
                    ];

                    \Log::info('Maya checkout request', [
                        'order_id' => $order->id, 
                        'items_subtotal' => $itemsSubtotal,
                        'shipping_fee' => $shippingFee,
                        'total_amount' => $amount
                    ]);

                    $response = \Illuminate\Support\Facades\Http::withBasicAuth($publicKey, '')
                        ->acceptJson()
                        ->post($baseUrl . '/checkout/v1/checkouts', $payload);

                    if ($response->successful()) {
                        $data = $response->json() ?? [];
                        $checkoutId  = $data['checkoutId'] ?? $data['id'] ?? null;
                        $checkoutUrl = $data['redirectUrl'] ?? $data['checkoutUrl'] ?? $data['url'] ?? null;

                        if ($checkoutId && $checkoutUrl) {
                            // Update orders
                            foreach ($paymentOrders as $paymentOrder) {
                                $paymentOrder->payment_method = 'online_banking';
                                $paymentOrder->transaction_id = $checkoutId;
                                $paymentOrder->payment_status = 'pending';
                                if ($paymentOrder->id === $order->id) {
                                    $paymentOrder->shipping_fee = $shippingFee;
                                    if ($request->filled('delivery_city')) {
                                        $paymentOrder->delivery_city = $request->input('delivery_city');
                                    }
                                    if ($request->filled('delivery_province')) {
                                        $paymentOrder->delivery_province = $request->input('delivery_province');
                                    }
                                }
                                $paymentOrder->save();
                            }
                            
                            \Log::info('Maya checkout success', [
                                'order_id' => $order->id,
                                'checkout_id' => $checkoutId,
                                'redirect_url' => $checkoutUrl
                            ]);
                            
                            return response()->json([
                                'success' => true,
                                'redirect_url' => $checkoutUrl,
                                'checkout_id' => $checkoutId
                            ]);
                        }
                    }
                    
                    \Log::error('Maya checkout failed', [
                        'order_id' => $order->id,
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    
                    // Fall back to bank transfer on Maya failure
                    $paymentMethod = 'bank_transfer';
                    
                } catch (\Throwable $e) {
                    \Log::error('Maya checkout exception: ' . $e->getMessage());
                    $paymentMethod = 'bank_transfer';
                }
            }
            
            // Handle Bank Transfer
            foreach ($paymentOrders as $paymentOrder) {
                $paymentOrder->payment_method = $paymentMethod;
                if ($paymentOrder->id === $order->id) {
                    $paymentOrder->shipping_fee = $shippingFee;
                    if ($request->filled('delivery_city')) {
                        $paymentOrder->delivery_city = $request->input('delivery_city');
                    }
                    if ($request->filled('delivery_province')) {
                        $paymentOrder->delivery_province = $request->input('delivery_province');
                    }
                }
                $paymentOrder->save();
            }
            
            $tokenQuery = $authTokenForCallback ? '?auth_token=' . urlencode($authTokenForCallback) : '';
            $instructionsUrl = route('custom_orders.payment.instructions', $order->id) . $tokenQuery;
            
            return response()->json([
                'success' => true,
                'redirect_url' => $instructionsUrl,
                'payment_method' => $paymentMethod
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Order not found.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Payment initiation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Payment initialization failed. Please try again.'
            ], 500);
        }
    }

    public function showPaymentConfirm(CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->payment_status === 'paid') {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)->with('info', 'This order is already paid.');
        }

        if (!$order->payment_method) {
            return $this->redirectToRouteWithToken('custom_orders.payment', $order);
        }

        // For bank transfer, redirect to instructions page
        if ($order->payment_method === 'bank_transfer') {
            return $this->redirectToRouteWithToken('custom_orders.payment.instructions', $order);
        }

        $order->load('product');
        return view('custom_orders.payment_confirm', compact('order'));
    }

    public function mayaPaymentSuccess(Request $request, $id)
    {
        $order = CustomOrder::findOrFail($id);
        if ($order->user_id !== Auth::id()) abort(403);

        $checkoutId = $request->query('checkoutId') ?? $request->query('id') ?? $order->transaction_id;

        if ($checkoutId) {
            try {
                $secretKey = config('services.maya.secret_key');
                $baseUrl   = rtrim(config('services.maya.base_url', 'https://pg-sandbox.paymaya.com'), '/');
                $response  = \Illuminate\Support\Facades\Http::withBasicAuth($secretKey, '')
                    ->acceptJson()
                    ->get($baseUrl . '/checkout/v1/checkouts/' . urlencode($checkoutId));

                if ($response->successful()) {
                    $gatewayStatus = strtolower((string) ($response->json()['status'] ?? 'pending'));
                    if (str_contains($gatewayStatus, 'paid') || str_contains($gatewayStatus, 'success') || str_contains($gatewayStatus, 'complete')) {
                        $order->payment_status    = 'paid';
                        $order->payment_verified_at = now();
                        $order->status            = 'processing';
                        $order->transaction_id    = $checkoutId;
                        $order->save();
                        return $this->redirectToRouteWithToken('custom_orders.show', $order)
                            ->with('success', 'Maya payment confirmed! Your custom order is now being processed.');
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Maya status check failed for custom order: ' . $e->getMessage());
            }
        }

        return $this->redirectToRouteWithToken('custom_orders.show', $order)
            ->with('info', 'Maya checkout completed. Payment verification is in progress.');
    }

    public function mayaPaymentFailed(Request $request, $id)
    {
        $order = CustomOrder::findOrFail($id);
        if ($order->user_id !== Auth::id()) abort(403);

        return $this->redirectToRouteWithToken('custom_orders.payment', $order)
            ->with('error', 'Maya payment was not completed. Please try again or choose Bank Transfer.');
    }

    /**
     * Process payment confirmation with receipt/transaction ID
     */
    public function confirmPayment(Request $request, CustomOrder $order)
    {
        // Handle auth_token authentication if not already authenticated
        if (!Auth::check()) {
            $token = $request->input('auth_token') 
                ?? $request->query('auth_token') 
                ?? session('auth_token');
            
            if ($token) {
                $authToken = \DB::table('auth_tokens')
                    ->where('token', $token)
                    ->where('expires_at', '>', now())
                    ->first();
                
                if ($authToken) {
                    $user = User::find($authToken->user_id);
                    if ($user) {
                        Auth::login($user, true);
                        session(['auth_token' => $token]);
                        
                        // Keep token alive
                        \DB::table('auth_tokens')
                            ->where('token', $token)
                            ->update(['expires_at' => now()->addDays(30), 'updated_at' => now()]);
                    }
                }
            }
            
            // If still not authenticated after token check, redirect to login
            if (!Auth::check()) {
                return redirect()->route('login.user.form')
                    ->with('error', 'Please login to continue.')
                    ->with('redirect_to', route('custom_orders.payment.confirm', $order->id));
            }
        }
        
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $paymentOrders = $this->getUserBatchOrders($order, Auth::id())
            ->where('payment_status', '!=', 'paid')
            ->values();

        if ($paymentOrders->isEmpty()) {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('info', 'This order is already paid.');
        }

        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp,gif,bmp,heic,heif|max:10240',
            'payment_notes' => 'nullable|string|max:1000',
            'transfer_date' => 'nullable|date',
        ]);

        $storedPath = null;

        // Store receipt if uploaded
        if ($request->hasFile('receipt')) {
            $receiptFile = $request->file('receipt');
            $cloudinary = new CloudinaryService();
            
            // Try Cloudinary first (persistent storage)
            if ($cloudinary->isEnabled()) {
                $result = $cloudinary->uploadFile($receiptFile, 'custom-orders/receipts');
                if ($result) {
                    $storedPath = $result['url'];
                    \Log::info('Custom order payment receipt uploaded to Cloudinary', [
                        'url' => $storedPath,
                        'order_id' => $order->id,
                    ]);
                }
            }
            
            // Fallback to local storage
            if (!$storedPath) {
                $storedPath = $receiptFile->store('payment_receipts', 'public');
                \Log::info('Custom order payment receipt uploaded to local storage', [
                    'path' => $storedPath,
                    'order_id' => $order->id,
                ]);
            }
        }

        foreach ($paymentOrders as $paymentOrder) {
            if ($storedPath) {
                $paymentOrder->payment_receipt = $storedPath;
            }

            $paymentOrder->transaction_id = $request->transaction_id;
            $paymentOrder->payment_notes = $request->payment_notes;

            if ($request->transfer_date) {
                $paymentOrder->transfer_date = $request->transfer_date;
            }

            if ($request->hasFile('receipt')) {
                $paymentOrder->payment_status = 'paid';
            } else {
                $paymentOrder->payment_status = 'pending';
            }

            if (empty($paymentOrder->payment_method) && !empty($order->payment_method)) {
                $paymentOrder->payment_method = $order->payment_method;
            }

            $paymentOrder->save();
        }

        $paidCount = $paymentOrders->count();
        $successMessage = $paidCount > 1
            ? "Payment confirmation submitted for {$paidCount} custom items! We will verify your payment shortly."
            : 'Payment confirmation submitted! We will verify your payment shortly.';

        return $this->redirectToRouteWithToken('custom_orders.show', $order)->with('success', $successMessage);
    }

    /**
     * Show payment instructions for bank transfer
     */
    public function showPaymentInstructions(CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (!$order->payment_method) {
            return $this->redirectToRouteWithToken('custom_orders.payment', $order);
        }

        $paymentOrders = $this->getUserBatchOrders($order, Auth::id())
            ->where('payment_status', '!=', 'paid')
            ->values();

        if ($paymentOrders->isEmpty()) {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('info', 'This order is already paid.');
        }

        $isBatchPayment = $paymentOrders->count() > 1;
        $paymentTotal = $this->calculateOrdersTotal($paymentOrders);
        $orderIdList = '#' . $paymentOrders->pluck('id')->implode(', #');
        $referenceCode = $order->transaction_id
            ?? ($isBatchPayment && !empty($order->batch_order_number)
                ? 'BATCH-' . $order->batch_order_number
                : 'REF_' . $order->id);

        // Create simple payment instructions based on payment method
        $baseInstructions = [
            'amount' => $paymentTotal,
            'reference_code' => $referenceCode,
            'notes' => $isBatchPayment
                ? 'This single payment covers order IDs: ' . $orderIdList . '. Please include your batch reference in the transfer details.'
                : 'Please include your order ID (' . $order->id . ') in the payment reference.'
        ];

        $gcashNumber    = \App\Models\SystemSetting::get('gcash_number', '');
        $gcashName      = \App\Models\SystemSetting::get('gcash_name', 'Tuwas Yakan');
        $bankName       = \App\Models\SystemSetting::get('bank_name', '');
        $bankAcctName   = \App\Models\SystemSetting::get('bank_account_name', 'Tuwas Yakan');
        $bankAcctNumber = \App\Models\SystemSetting::get('bank_account_number', '');
        $bankBranch     = \App\Models\SystemSetting::get('bank_branch', '');

        switch ($order->payment_method) {
            case 'gcash':
                $instructions = array_merge($baseInstructions, [
                    'title' => 'Maya Payment Instructions',
                    'steps' => [
                        '1. Open your Maya app',
                        '2. Select "Send Money" or "Pay Bills"',
                        '3. Enter the Maya number: ' . $gcashNumber,
                        '4. Enter the amount: ₱' . number_format($paymentTotal, 2),
                        '5. Save the transaction reference number',
                        '6. Come back to this page to confirm payment'
                    ],
                    'gcash_number' => $gcashNumber,
                    'account_name' => $gcashName,
                ]);
                break;

            case 'online_banking':
                // This method is labeled as "Payment Center / E-wallet" in the UI.
                // Show Maya-style details so the user clearly sees Maya as an option.
                $instructions = array_merge($baseInstructions, [
                    'title' => 'Maya / E-wallet Instructions',
                    'steps' => [
                        '1. Open Maya or your preferred e-wallet app',
                        '2. Choose Send Money / Pay Bills or similar option',
                        '3. Use the Maya details below to send the payment',
                        '4. Enter the amount: ₱' . number_format($paymentTotal, 2),
                        '5. Save the transaction reference number',
                        '6. Come back to this page to confirm payment'
                    ],
                    // Reuse the e-wallet layout in the instructions view.
                    'gcash_number' => $gcashNumber,
                    'account_name' => $gcashName,
                ]);
                break;

            case 'bank_transfer':
            default:
                $instructions = array_merge($baseInstructions, [
                    'title' => 'Bank Transfer Instructions',
                    'steps' => [
                        '1. Go to your bank or use online banking',
                        '2. Transfer funds to the account below',
                        '3. Enter the amount: ₱' . number_format($paymentTotal, 2),
                        '4. Save the deposit slip or transaction reference',
                        '5. Come back to this page to confirm payment'
                    ],
                    'bank_name'      => $bankName,
                    'account_name'   => $bankAcctName,
                    'account_number' => $bankAcctNumber,
                    'branch'         => $bankBranch,
                ]);
                break;
        }
        
        return view('custom_orders.payment_instructions', compact('order', 'instructions', 'paymentOrders', 'isBatchPayment', 'paymentTotal'));
    }

    /**
     * Get custom order statistics for admin dashboard
     */
    public function adminStatistics()
    {
        $stats = [
            'total_orders' => CustomOrder::count(),
            'pending_orders' => CustomOrder::where('status', 'pending')->count(),
            'approved_orders' => CustomOrder::where('status', 'approved')->count(),
            'in_progress_orders' => CustomOrder::where('status', 'in_progress')->count(),
            'completed_orders' => CustomOrder::where('status', 'completed')->count(),
            'cancelled_orders' => CustomOrder::where('status', 'cancelled')->count(),
            'total_revenue' => CustomOrder::where('payment_status', 'paid')->sum('final_price'),
            'pending_revenue' => CustomOrder::where('payment_status', 'pending')->sum('estimated_price'),
            'average_order_value' => CustomOrder::where('payment_status', 'paid')->avg('final_price'),
            'orders_this_month' => CustomOrder::whereMonth('created_at', now()->month)->count(),
            'orders_this_year' => CustomOrder::whereYear('created_at', now()->year)->count(),
            'most_common_product_type' => $this->getMostCommonProductType(),
            'average_production_time' => $this->getAverageProductionTime(),
        ];

        return response()->json($stats);
    }

    /**
     * Export custom orders to CSV
     */
    public function export(Request $request)
    {
        $orders = CustomOrder::with(['user', 'product'])
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->orderByDesc('created_at')
            ->get();

        $filename = 'custom_orders_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // CSV Header
            fputcsv($file, [
                'ID', 'Customer', 'Email', 'Phone', 'Product Type', 'Quantity',
                'Specifications', 'Status', 'Payment Status', 'Estimated Price',
                'Final Price', 'Created Date', 'Expected Date'
            ]);
            
            // CSV Data
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    $order->user->name ?? 'N/A',
                    $order->user->email ?? 'N/A',
                    $order->phone,
                    $order->product_type,
                    $order->quantity,
                    $order->specifications,
                    $order->status,
                    $order->payment_status,
                    $order->estimated_price,
                    $order->final_price,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->expected_date ? $order->expected_date->format('Y-m-d') : 'N/A'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk update custom orders
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:custom_orders,id',
            'action' => 'required|in:approve,reject,cancel,delete',
            'reason' => 'nullable|string|max:500'
        ]);

        $orderIds = $request->order_ids;
        $action = $request->action;
        $reason = $request->reason;

        $updatedCount = 0;

        switch ($action) {
            case 'approve':
                CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'admin_notes' => $reason
                    ]);
                $updatedCount = CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'approved')
                    ->count();
                break;

            case 'reject':
                CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'rejected',
                        'rejected_at' => now(),
                        'rejection_reason' => $reason
                    ]);
                $updatedCount = CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'rejected')
                    ->count();
                break;

            case 'cancel':
                CustomOrder::whereIn('id', $orderIds)
                    ->whereIn('status', ['pending', 'approved'])
                    ->update([
                        'status' => 'cancelled',
                        'rejection_reason' => $reason ?? 'Bulk cancelled by admin'
                    ]);
                $updatedCount = CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'cancelled')
                    ->count();
                break;

            case 'delete':
                $updatedCount = CustomOrder::whereIn('id', $orderIds)
                    ->where('status', 'cancelled')
                    ->delete();
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully {$action}ed {$updatedCount} orders",
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Get most common product type
     */
    private function getMostCommonProductType()
    {
        return CustomOrder::select('product_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('product_type')
            ->orderByDesc('count')
            ->first();
    }

    /**
     * Get average production time in days
     */
    private function getAverageProductionTime()
    {
        return CustomOrder::whereNotNull('approved_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(DATEDIFF(completed_at, approved_at)) as avg_days')
            ->value('avg_days');
    }

    /**
     * Store visual design submission
     */
    private function storeVisualDesign(Request $request)
    {
        $request->validate([
            'design_image' => 'required|string',
            'design_metadata' => 'required|string',
            'order_name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'size' => 'required|string|max:50',
            'priority' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:1000',
        ]);

        try {
            // Decode design data
            $designImage = $request->design_image; // Base64 image data
            $designMetadata = json_decode($request->design_metadata, true);
            
            // Calculate base price dynamically (visual design)
            // Using default complexity for visual designs
            $patternFeeSimple = \App\Models\SystemSetting::get('pattern_fee_simple', 1200);
            $patternFeeMedium = \App\Models\SystemSetting::get('pattern_fee_medium', 1900);
            $patternFeeComplex = \App\Models\SystemSetting::get('pattern_fee_complex', 2500);
            
            // Default to medium complexity for visual designs
            $basePrice = $patternFeeMedium;
            
            // If complexity is detected from metadata, use that
            $complexity = $this->calculateComplexityFromMetadata($designMetadata);
            if ($complexity === 'complex') {
                $basePrice = $patternFeeComplex;
            } elseif ($complexity === 'simple') {
                $basePrice = $patternFeeSimple;
            }

            // Save design image
            $imagePath = $this->saveDesignImage($designImage);

            // Create custom order with visual design
            $customOrder = CustomOrder::create([
                'user_id' => $userId,
                'product_id' => 1, // Default product ID for visual designs
                'specifications' => $request->description . "\n\n" . $request->special_instructions,
                'patterns' => json_encode($this->extractPatternsFromMetadata($designMetadata)),
                'complexity' => $this->calculateComplexityFromMetadata($designMetadata),
                'quantity' => 1,
                'estimated_price' => $basePrice,
                'final_price' => $basePrice,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'design_upload' => $imagePath,
                'design_method' => 'visual',
                'design_metadata' => $designMetadata,
                'order_name' => $request->order_name,
                'category' => $request->category,
                'size' => $request->size,
                'priority' => $request->priority,
                'description' => $request->description,
                'special_instructions' => $request->special_instructions,
            ]);

            // Log the creation
            \Log::info('Visual Custom Order Created:', [
                'order_id' => $customOrder->id,
                'user_id' => auth()->id(),
                'base_price' => $basePrice,
                'pattern_count' => count($designMetadata['patterns'] ?? []),
                'order_name' => $request->order_name,
            ]);

            return $this->redirectToRouteWithToken('custom_orders.payment', $customOrder->id)
                ->with('success', 'Visual design submitted successfully! Please complete payment to proceed.');

        } catch (\Exception $e) {
            \Log::error('Error storing visual design:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save your design. Please try again.']);
        }
    }

    /**
     * Save base64 design image to file
     */
    private function saveDesignImage($base64Image)
    {
        // Extract the base64 image data
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches)) {
            $imageType = $matches[1];
            $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
            
            $imageData = base64_decode($base64Data);
            $filename = 'design_' . time() . '_' . uniqid() . '.' . $imageType;
            $path = 'custom_designs/' . $filename;
            
            // Save to storage
            Storage::disk('public')->put($path, $imageData);
            
            return $path;
        }
        
        throw new \Exception('Invalid image data format');
    }

    /**
     * Persist a preview image data URI to public storage and return the path
     */
    private function savePreviewImage(string $dataUri): ?string
    {
        if (!str_starts_with($dataUri, 'data:image')) {
            return null;
        }

        if (!preg_match('/^data:image\/(\w+);base64,/', $dataUri, $matches)) {
            return null;
        }

        $imageType = strtolower($matches[1]);
        $allowedTypes = ['png', 'jpg', 'jpeg', 'webp'];
        if (!in_array($imageType, $allowedTypes, true)) {
            $imageType = 'png';
        }

        $base64Data = substr($dataUri, strpos($dataUri, ',') + 1);
        $imageData = base64_decode($base64Data, true);

        if ($imageData === false) {
            return null;
        }

        $filename = 'pattern_preview_' . Str::uuid() . '.' . $imageType;
        $path = 'custom_orders/pattern_previews/' . $filename;

        Storage::disk('public')->put($path, $imageData);

        return $path;
    }

    /**
     * Extract pattern information from design metadata
     */
    private function extractPatternsFromMetadata($metadata)
    {
        $patterns = [];
        
        if (isset($metadata['patterns']) && is_array($metadata['patterns'])) {
            foreach ($metadata['patterns'] as $pattern) {
                $patterns[] = $pattern['type'] ?? 'custom';
            }
        }
        
        return array_unique($patterns);
    }

    /**
     * Remove large/base64 blobs from design metadata before persisting
     */
    private function sanitizeDesignMetadata($metadata): array
    {
        $stripKeys = ['preview', 'thumbnail', 'image', 'data', 'blob', 'base64', 'svg'];
        $maxStringLength = 1000; // prevent multi-MB strings from being stored

        $walker = function ($value) use (&$walker, $stripKeys, $maxStringLength) {
            if (is_array($value)) {
                $result = [];
                foreach ($value as $key => $val) {
                    $lowerKey = strtolower((string) $key);
                    if (in_array($lowerKey, $stripKeys, true)) {
                        continue;
                    }
                    $result[$key] = $walker($val);
                }
                return $result;
            }

            if (is_string($value)) {
                $trimmed = trim($value);
                if (str_starts_with($trimmed, 'data:image')) {
                    return null;
                }
                if (strlen($value) > $maxStringLength) {
                    return substr($value, 0, $maxStringLength) . '... [truncated]';
                }
                return $value;
            }

            return $value;
        };

        $cleaned = $walker($metadata ?? []);

        // Drop nulls produced by stripping
        if (is_array($cleaned)) {
            $cleaned = array_filter($cleaned, fn($v) => $v !== null);
        }

        return $cleaned;
    }

    /**
     * Calculate complexity based on design metadata
     */
    private function calculateComplexityFromMetadata($metadata)
    {
        $patternCount = count($metadata['patterns'] ?? []);
        
        if ($patternCount <= 2) {
            return 'low';
        } elseif ($patternCount <= 5) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    /**
     * Save design progress
     */
    public function saveProgress(Request $request)
    {
        $designData = $request->all();
        
        // Save to user session or database
        session(['custom_order_progress' => $designData]);
        
        return response()->json([
            'success' => true,
            'message' => 'Progress saved successfully'
        ]);
    }

    /**
     * Load saved design progress
     */
    public function loadProgress(Request $request)
    {
        $progress = session('custom_order_progress', []);
        
        return response()->json([
            'success' => true,
            'progress' => $progress
        ]);
    }

    /**
     * Show user analytics dashboard
     */
    public function userAnalytics(Request $request)
    {
        $user = auth()->user();
        
        $orders = CustomOrder::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        $analytics = [
            'total_orders' => $orders->count(),
            'total_spent' => $orders->sum('final_price'),
            'favorite_patterns' => $this->getUserFavoritePatterns($orders),
            'design_methods' => $orders->groupBy('design_method')->map->count(),
            'completion_rate' => $this->getUserCompletionRate($orders),
        ];

        return view('custom_orders.analytics', compact('orders', 'analytics'));
    }

    /**
     * Get user's favorite patterns
     */
    private function getUserFavoritePatterns($orders)
    {
        $patterns = [];
        
        foreach ($orders as $order) {
            $orderPatterns = json_decode($order->patterns, true) ?? [];
            foreach ($orderPatterns as $pattern) {
                $patterns[$pattern] = ($patterns[$pattern] ?? 0) + 1;
            }
        }
        
        arsort($patterns);
        return array_slice($patterns, 0, 5, true);
    }

    /**
     * Calculate user's order completion rate
     */
    private function getUserCompletionRate($orders)
    {
        if ($orders->isEmpty()) {
            return 0;
        }
        
        $completedOrders = $orders->whereIn('status', ['completed', 'delivered'])->count();
        
        return ($completedOrders / $orders->count()) * 100;
    }

    /**
     * Show payment page for a custom order
     */
    public function payment($id)
    {
        try {
            \Log::info('Payment method called', [
                'order_id' => $id,
                'auth_check_before' => auth()->check(),
                'auth_id_before' => auth()->id()
            ]);
            
            $order = CustomOrder::findOrFail($id);
            
            \Log::info('Order found, checking authentication', [
                'order_id' => $order->id,
                'order_user_id' => $order->user_id,
                'current_user_id' => auth()->id(),
                'auth_check' => auth()->check(),
                'user_authenticated' => auth()->check()
            ]);

            if (!auth()->check()) {
                \Log::error('User not authenticated for payment', [
                    'order_id' => $order->id,
                    'redirect_to_login' => true
                ]);
                return redirect()->route('login')
                    ->with('error', 'Please login to access payment page.');
            }

            if ($order->user_id !== auth()->id()) {
                \Log::error('Unauthorized payment access attempt', [
                    'order_id' => $order->id,
                    'order_user_id' => $order->user_id,
                    'current_user_id' => auth()->id(),
                    'order_owner_check' => $order->user_id !== auth()->id()
                ]);
                abort(403, 'Unauthorized');
            }

            \Log::info('Authentication passed, calling showPayment', [
                'order_id' => $order->id
            ]);

            return $this->showPayment($order);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Payment page - Order not found', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            return $this->redirectToRouteWithToken('custom_orders.index')
                ->with('error', 'Order not found.');
        } catch (\Exception $e) {
            \Log::error('Payment page error', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Server error: ' . $e->getMessage());
        }
    }

    /**
     * Show payment instructions
     */
    public function paymentInstructions($id)
    {
        try {
            $order = CustomOrder::findOrFail($id);
            return $this->showPaymentInstructions($order);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Custom Order not found');
        } catch (\Exception $e) {
            abort(500, 'Server error: ' . $e->getMessage());
        }
    }

    /**
     * Show payment confirmation
     */
    public function paymentConfirm($id)
    {
        try {
            $order = CustomOrder::findOrFail($id);
            return $this->showPaymentConfirm($order);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Custom Order not found');
        } catch (\Exception $e) {
            abort(500, 'Server error: ' . $e->getMessage());
        }
    }

    /**
     * Process payment confirmation
     */
    public function paymentConfirmProcess(Request $request, CustomOrder $order)
    {
        return $this->confirmPayment($request, $order);
    }

    /**
     * Cancel a custom order
     */
    public function cancel(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $order->status = 'cancelled';
        $order->rejection_reason = $request->reason;
        $order->save();

        return $this->redirectToRouteWithToken('custom_orders.show', $order)
            ->with('success', 'Order cancelled successfully.');
    }

    /**
     * User accepts the quoted price
     */
    public function acceptQuote(CustomOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if order is in the right status
        if (!$order->isAwaitingUserDecision()) {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'This order is not awaiting your decision.');
        }
        
        try {
            // Use model method to accept price
            $success = $order->acceptPrice();
            
            if ($success) {
                \Log::info('User accepted custom order quote', [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'price' => $order->final_price
                ]);
                
            return $this->redirectToRouteWithToken('custom_orders.payment', $order)
                    ->with('success', 'Quote accepted! Please complete your payment to start production.');
            }
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'Unable to accept quote. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('Accept quote error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'An error occurred. Please try again.');
        }
    }
    
    /**
     * User rejects the quoted price
     */
    public function rejectQuote(Request $request, CustomOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if order is in the right status
        if (!$order->isAwaitingUserDecision()) {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'This order is not awaiting your decision.');
        }
        
        try {
            // Use model method to reject price
            $success = $order->rejectPrice();
            
            if ($success) {
                // Optionally save user's reason
                if ($request->filled('reason')) {
                    $order->rejection_reason = 'User rejected: ' . $request->reason;
                    $order->save();
                }
                
                \Log::info('User rejected custom order quote', [
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'reason' => $request->reason
                ]);
                
                return $this->redirectToRouteWithToken('custom_orders.index')
                    ->with('info', 'Quote rejected. The order has been cancelled.');
            }
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'Unable to reject quote. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('Reject quote error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'An error occurred. Please try again.');
        }
    }
    
    /**
     * Customer confirms order has been received
     */
    public function confirmReceived(CustomOrder $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Check if order is delivered
        if ($order->status !== 'delivered') {
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'This order is not marked as delivered yet.');
        }
        
        try {
            // Update status to completed
            $order->status = 'completed';
            // Note: delivered_at was already set when admin marked it as delivered
            // We could add a separate completed_at field, but for now we'll just update status
            $order->save();
            
            \Log::info('Customer confirmed order received', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'delivered_at' => $order->delivered_at
            ]);
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('success', 'Thank you for confirming! Your order is now complete.');
                
        } catch (\Exception $e) {
            \Log::error('Confirm order received error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->redirectToRouteWithToken('custom_orders.show', $order)
                ->with('error', 'An error occurred. Please try again.');
        }
    }

}
