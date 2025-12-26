<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomOrder;
use App\Models\Product;
use App\Models\Category;
use App\Models\YakanPattern;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomOrderController extends Controller
{
    /**
     * Validate wizard session data with comprehensive logging
     */
    private function validateWizardSession(Request $request, string $step = 'unknown')
    {
        $sessionData = $request->session()->get('wizard', []);
        
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
            'session_data' => $request->session()->get('wizard'),
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
            $wizardData = $request->session()->get('wizard', []);

            if (!$wizardData) {
                return redirect()->route('custom_orders.create.choice')
                    ->with('error', 'Please start your custom order.');
            }

            if (isset($wizardData['product'])) {
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if (!$product) {
                    return redirect()->route('custom_orders.create.product')
                        ->with('error', 'Please select a product first.');
                }
                if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                    return redirect()->route('custom_orders.create.product.customize')
                        ->with('error', 'Please customize your design first.');
                }
                return view('custom_orders.wizard.step3', [
                    'product' => $product,
                    'isProductFlow' => true
                ]);
            }

            if (!isset($wizardData['fabric'])) {
                return redirect()->route('custom_orders.create.step1')
                    ->with('error', 'Please select a fabric first.');
            }
            if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                return redirect()->route('custom_orders.create.pattern')
                    ->with('error', 'Please select a pattern or create a design first.');
            }

            return view('custom_orders.wizard.step3', [
                'isProductFlow' => false
            ]);
        } catch (\Exception $e) {
            \Log::error('createStep3 error', ['error' => $e->getMessage()]);
            return redirect()->route('custom_orders.create.choice')
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

            $wizardData = $request->session()->get('wizard', []);

            // Get the selected address if delivery type is delivery
            $deliveryAddress = null;
            if ($validated['delivery_type'] === 'delivery' && $validated['address_id']) {
                $address = auth()->user()->addresses()->find($validated['address_id']);
                if ($address) {
                    $deliveryAddress = "{$address->street}, {$address->barangay}, {$address->city}, {$address->province} {$address->postal_code}";
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
                'special_instructions' => $validated['special_instructions'] ?? null,
                'addons' => $validated['addons'] ?? [],
                'updated_at' => now()->toISOString(),
            ];

            $wizardData['step'] = 'details_complete';
            $request->session()->put('wizard', $wizardData);
            $request->session()->save();
            
            \Log::info('storeStep3 - session saved', [
                'wizard_keys' => array_keys($wizardData),
                'has_pattern' => isset($wizardData['pattern']),
                'has_details' => isset($wizardData['details']),
                'session_id' => $request->session()->getId(),
            ]);

            // Also flash the wizard data as a backup in case session is lost
            $request->session()->flash('wizard_backup', $wizardData);

            return redirect()->route('custom_orders.create.step4');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            \Log::error('storeStep3 error', ['error' => $e->getMessage()]);
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
        $wizardData = $request->session()->get('wizard', []);
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
        $wizardData = $request->session()->get('wizard', []);
        
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

        // Update session with defaults
        $request->session()->put('wizard', $wizardData);
        return $wizardData;
    }

    /**
     * Backup wizard session data
     */
    private function backupWizardSession(Request $request, string $step)
    {
        $wizardData = $request->session()->get('wizard', []);
        
        if (!empty($wizardData)) {
            $backupKey = 'wizard_backup_' . auth()->id() . '_' . time();
            $request->session()->put($backupKey, [
                'data' => $wizardData,
                'step' => $step,
                'timestamp' => now(),
                'user_id' => auth()->id()
            ]);
            
            // Keep only last 3 backups per user
            $backups = $request->session()->all();
            $userBackups = [];
            
            foreach ($backups as $key => $value) {
                if (str_starts_with($key, 'wizard_backup_' . auth()->id())) {
                    $userBackups[$key] = $value['timestamp'] ?? 0;
                }
            }
            
            // Sort by timestamp and keep only 3 most recent
            asort($userBackups);
            $toRemove = array_slice(array_keys($userBackups), 0, -3);
            
            foreach ($toRemove as $removeKey) {
                $request->session()->forget($removeKey);
            }
            
            \Log::info("Wizard session backed up", [
                'backup_key' => $backupKey,
                'step' => $step,
                'data_keys' => array_keys($wizardData)
            ]);
        }
    }

    /**
     * Restore wizard session from backup
     */
    private function restoreWizardSession(Request $request, string $preferredStep = null)
    {
        $backups = $request->session()->all();
        $userBackups = [];
        
        foreach ($backups as $key => $value) {
            if (str_starts_with($key, 'wizard_backup_' . auth()->id())) {
                $userBackups[$key] = $value;
            }
        }
        
        if (empty($userBackups)) {
            return false;
        }
        
        // Find best backup (prefer preferred step, then most recent)
        $bestBackup = null;
        $bestTimestamp = 0;
        
        foreach ($userBackups as $key => $backup) {
            $timestamp = $backup['timestamp'] ?? 0;
            
            if ($preferredStep && ($backup['step'] ?? '') === $preferredStep) {
                $bestBackup = $backup;
                break;
            }
            
            if ($timestamp > $bestTimestamp) {
                $bestBackup = $backup;
                $bestTimestamp = $timestamp;
            }
        }
        
        if ($bestBackup && isset($bestBackup['data'])) {
            $request->session()->put('wizard', $bestBackup['data']);
            
            \Log::info("Wizard session restored from backup", [
                'restored_step' => $bestBackup['step'] ?? 'unknown',
                'restored_timestamp' => $bestBackup['timestamp'] ?? 'unknown',
                'data_keys' => array_keys($bestBackup['data'])
            ]);
            
            return $bestBackup['step'] ?? 'step1';
        }
        
        return false;
    }

    /**
     * Clear wizard session and backups
     */
    private function clearWizardSession(Request $request)
    {
        $request->session()->forget('wizard');
        
        // Clear user backups
        $backups = $request->session()->all();
        foreach ($backups as $key => $value) {
            if (str_starts_with($key, 'wizard_backup_' . auth()->id())) {
                $request->session()->forget($key);
            }
        }
        
        \Log::info("Wizard session cleared for user", ['user_id' => auth()->id()]);
    }
    /**
     * List custom orders for the logged-in user
     */
    public function userIndex()
    {
        $orders = CustomOrder::with('product')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('custom_orders.index', compact('orders'));
    }

    /**
     * Show form to create a new custom order
     * Redirects directly to fabric selection (Pattern/Fabric Design Flow)
     */
    public function create()
    {
        return redirect()->route('custom_orders.create.step1');
    }


    /**
     * Step 1: Fabric Selection
     */
    public function createStep1(Request $request)
    {
        try {
            \Log::info('createStep1 called', ['user_id' => auth()->id()]);
            
            // Test basic database connection
            try {
                \DB::connection()->getPdo();
                \Log::info('Database connection OK');
            } catch (\Exception $dbEx) {
                \Log::error('Database connection failed', ['error' => $dbEx->getMessage()]);
                throw $dbEx;
            }

            // For fabric orders, we don't need products or categories
            // Just return the fabric selection view
            \Log::info('Loading fabric selection step');
            
            return view('custom_orders.wizard.step1');
            
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
                return redirect()->route("custom_orders.create.{$restoredStep}")
                    ->with('success', 'Your previous progress has been restored.');
            } else {
                return redirect()->route('custom_orders.create.step1')
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
                'fabric_type' => 'required|string',
                'fabric_quantity_meters' => 'required|numeric|min:0.5|max:100',
                'intended_use' => 'required|string|in:clothing,home_decor,crafts',
            ]);

            // Get fabric type details (for now using string, can be updated to use FabricType model later)
            $fabricType = $request->fabric_type;
            
            // Backup current session before updating
            $this->backupWizardSession($request, 'step1');
            
            // Store fabric selection in session
            $request->session()->put('wizard.fabric', [
                'type' => $fabricType,
                'quantity_meters' => $request->fabric_quantity_meters,
                'intended_use' => $request->intended_use,
                'fabric_specifications' => $request->fabric_specifications,
                'special_requirements' => $request->special_requirements,
            ]);

            // Force session save and verify
            $request->session()->save();
            
            \Log::info("Step1 completed successfully", [
                'fabric_type' => $fabricType,
                'quantity_meters' => $request->fabric_quantity_meters,
                'intended_use' => $request->intended_use,
                'session_saved' => $request->session()->has('wizard.fabric'),
                'session_data_after_save' => $request->session()->get('wizard', []),
            ]);

            // Check if it's an AJAX request (improved detection)
            $isAjax = $request->ajax() || 
                     $request->wantsJson() || 
                     $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                     $request->header('Accept') === 'application/json';

            if ($isAjax) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fabric selection saved successfully',
                    'fabric' => [
                        'type' => $fabricType,
                        'quantity_meters' => $request->fabric_quantity_meters,
                        'intended_use' => $request->intended_use,
                    ]
                ]);
            }

            // Redirect to image upload (new step 2)
            return redirect()->route('custom_orders.create.image');
            
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
        if (!$request->session()->has('wizard.fabric')) {
            return redirect()->route('custom_orders.create.step1')
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
                $imagePath = $image->store('custom_orders/references', 'public');
            }

            // Store in session
            $request->session()->put('wizard.reference', [
                'image_path' => $imagePath,
                'description' => $request->description,
            ]);

            $request->session()->save();

            // Redirect to pattern selection
            return redirect()->route('custom_orders.create.pattern')
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
            //     return redirect()->route('custom_orders.create.step1')
            //         ->with('error', 'Please select a fabric first.');
            // }

            // Wizard data and flow detection
            $wizardData = $request->session()->get('wizard', []);
            $isProductFlow = isset($wizardData['product']);

            // Capture preselected pattern from query and store in session
            $patternId = $request->query('pattern_id');
            if ($patternId) {
                $wizardData['pattern_id'] = (int) $patternId;
                $request->session()->put('wizard', $wizardData);
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
                ->orderBy('popularity_score', 'desc')
                ->orderBy('name', 'asc')
                ->get();

            // Get fabric type from session if available
            $fabricType = $wizardData['fabric']['type'] ?? null;

            return view('custom_orders.wizard.pattern_selection', [
                'product' => $product,
                'isProductFlow' => $isProductFlow,
                'selectedPattern' => $selectedPattern,
                'patterns' => $patterns,
                'fabricType' => $fabricType,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('createPatternSelection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('custom_orders.create.step1')
                ->with('error', 'Unable to load design interface: ' . $e->getMessage());
        }
    }

    /**
     * Store pattern/design data from step2
     */
    public function storePattern(Request $request)
    {
        try {
            $wizardData = $request->session()->get('wizard', []);
            
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
            
            $request->session()->put('wizard', $wizardData);
            $request->session()->save();
            
            \Log::info('Pattern storage - saved to session:', [
                'pattern' => $wizardData['pattern'],
                'fabric' => $wizardData['fabric'] ?? null,
                'session_has_pattern' => $request->session()->has('wizard.pattern'),
                'session_id' => $request->session()->getId(),
            ]);
            
            // Return JSON response with review URL
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Patterns saved successfully!',
                    'review_url' => route('custom_orders.create.step3'),
                    'next_url' => route('custom_orders.create.step3'),
                ]);
            }
            
            // Fallback for non-AJAX requests - redirect to step 3 (Order Details) instead of step 4
            return redirect()->route('custom_orders.create.step3')->with('wizard_saved', true);
            
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
            $wizardData = $request->session()->get('wizard');
            
            if (!$wizardData || !isset($wizardData['fabric'])) {
                return redirect()->route('custom_orders.create.step1')
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
            return redirect()->route('custom_orders.create.step1')
                ->with('error', 'Unable to load design interface. Please try again.');
        }
    }

    /**
     * Store Step 2: Design Data
     */
    public function storeStep2(Request $request)
    {
        try {
            $wizardData = $request->session()->get('wizard', []);
            
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
            
            $request->session()->put('wizard', $wizardData);
            
            return redirect()->route('custom_orders.create.step3');
            
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
            $wizardData = $request->session()->get('wizard');
            
            // If main session is lost, try to restore from backup
            if (!$wizardData && $request->session()->has('wizard_backup')) {
                $wizardData = $request->session()->get('wizard_backup');
                $request->session()->put('wizard', $wizardData);
                \Log::info('createStep4 - restored wizard from backup');
            }
            
            \Log::info('createStep4 - wizard data check', [
                'has_wizard' => !empty($wizardData),
                'wizard_keys' => $wizardData ? array_keys($wizardData) : [],
                'has_pattern' => isset($wizardData['pattern']),
                'has_design' => isset($wizardData['design']),
                'has_product' => isset($wizardData['product']),
                'session_id' => $request->session()->getId(),
                'all_session_data' => $request->session()->all(),
            ]);
            
            if (!$wizardData) {
                return redirect()->route('custom_orders.create.choice')
                    ->with('error', 'Please start your custom order.');
            }

            // Get user's saved addresses
            $userAddresses = auth()->user()->addresses()->get();
            $defaultAddress = auth()->user()->addresses()->where('is_default', true)->first();

            if (isset($wizardData['product'])) {
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if (!$product) {
                    return redirect()->route('custom_orders.create.product')
                        ->with('error', 'Please select a product first.');
                }
                if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                    return redirect()->route('custom_orders.create.product.customize')
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
                ]);
            }

            if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                \Log::warning('createStep4 - no pattern or design found', [
                    'wizard_keys' => array_keys($wizardData),
                    'full_wizard' => $wizardData,
                ]);
                // If we have details but no pattern, go back to pattern selection
                if (isset($wizardData['details'])) {
                    return redirect()->route('custom_orders.create.pattern')
                        ->with('error', 'Please select a pattern first.');
                }
                // If we don't have details either, start from the beginning
                return redirect()->route('custom_orders.create.step1')
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
            ]);
        } catch (\Exception $e) {
            \Log::error('createStep4 error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'wizard_data' => $wizardData ?? null,
            ]);
            
            // If we have wizard data but just missing pattern, redirect to pattern selection
            if (!empty($wizardData) && !isset($wizardData['pattern']) && !isset($wizardData['design'])) {
                return redirect()->route('custom_orders.create.pattern')
                    ->with('error', 'Please select a pattern first.');
            }
            
            return redirect()->route('custom_orders.create.step1')
                ->with('error', 'Unable to load review page. Please try again.');
        }
    }

    
    /**
     * Complete wizard and create order
     */
    public function completeWizard(Request $request)
    {
        \Log::info('completeWizard method called', [
            'request_method' => $request->method(),
            'request_data' => $request->all(),
            'session_has_wizard' => $request->session()->has('wizard'),
            'session_data_keys' => array_keys($request->session()->get('wizard', [])),
            'full_session_data' => $request->session()->get('wizard')
        ]);
        
        // Add basic validation
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'specifications' => 'nullable|string|max:1000',
            'delivery_type' => 'required|in:delivery,pickup',
            'address_id' => 'required_if:delivery_type,delivery|nullable|integer|exists:user_addresses,id',
            'delivery_zip' => 'nullable|string|max:20',
            'delivery_landmark' => 'nullable|string|max:255',
        ]);
        
        $wizardData = $request->session()->get('wizard');

        if (!$wizardData) {
            \Log::error('No wizard data in session');
            return redirect()->route('custom_orders.create.step1')
                ->with('error', 'Session expired. Please start your custom order again.');
        }

        $isProductFlow = isset($wizardData['product']);
        $isFabricFlow = isset($wizardData['fabric']);
        $details      = $wizardData['details'] ?? [];

        if (!$isProductFlow && !$isFabricFlow) {
            \Log::error('Neither product nor fabric flow detected', ['wizard_data' => $wizardData]);
            return redirect()->route('custom_orders.create.step1')
                ->with('error', 'Please select a fabric first.');
        }

        // Require a design selection for both flows
        if (!isset($wizardData['pattern']) && !isset($wizardData['design'])) {
            \Log::error('No pattern or design in wizard data', ['wizard_data' => $wizardData]);
            return $isProductFlow
                ? redirect()->route('custom_orders.create.product.customize')->with('error', 'Please customize your design first.')
                : redirect()->route('custom_orders.create.pattern')->with('error', 'Please select a pattern first.');
        }

        try {
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

            // Calculate base price
            $basePrice = 1300;
            if ($isProductFlow) {
                $product = \App\Models\Product::find($wizardData['product']['id'] ?? null);
                if ($product) {
                    $basePrice = $product->price;
                }
            } else {
                // For fabric flow, base price calculation
                // Priority is optional since step3 might be skipped
                if (isset($details['priority'])) {
                    switch ($wizardData['details']['priority']) {
                        case 'priority':
                            $basePrice += 200;
                            break;
                        case 'express':
                            $basePrice += 500;
                            break;
                    }
                }
                
                // Add cost based on fabric quantity
                if (isset($wizardData['fabric']['quantity_meters'])) {
                    $meters = (float) $wizardData['fabric']['quantity_meters'];
                    // Add ₱500 per meter
                    $basePrice += ($meters * 500);
                }
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
                // Pattern-based flow (supports string or array)
                $patternName = null;
                $patternId = $wizardData['pattern_id'] ?? null;
                $patternDifficulty = 'medium';

                if (is_array($wizardData['pattern'])) {
                    $patternName = $wizardData['pattern']['name'] ?? null;
                    $patternId = $wizardData['pattern']['id'] ?? $patternId;
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

                // Try to resolve pattern details from DB if possible
                if ($patternId || $patternName) {
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

                $patternsArray = array_values(array_filter([$patternName]));
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
            // Structured delivery fields from step4
            $addrHouse     = $validated['delivery_house'] ?? null;
            $addrStreet    = $validated['delivery_street'] ?? null;
            $addrBarangay  = $validated['delivery_barangay'] ?? null;
            $addrCity      = $validated['delivery_city'] ?? null;
            $addrProvince  = $validated['delivery_province'] ?? null;
            $addrZip       = $validated['delivery_zip'] ?? null;
            $addrLandmark  = $validated['delivery_landmark'] ?? null;

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
            }

            // Create custom order
            if ($isProductFlow) {
                // Create a product-based custom order (safe property assignment)
                $order = new CustomOrder();
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
                $order->phone = $details['customer_phone'] ?? null;
                $order->email = $details['customer_email'] ?? null;
                if (!empty($patternsArray)) {
                    $order->patterns = $patternsArray;
                }
                if ($imagePath) {
                    $order->design_upload = $imagePath;
                }
                $order->save();
                $customOrder = $order;
            } else {
                // Fabric flow creation - use request input for specifications
                $specifications = $formSpecifications ?? '';
                
                // Add fabric details to specifications if empty
                if (empty($specifications)) {
                    $specifications = "Custom Fabric Order\n";
                    $specifications .= "Fabric Type: " . ($wizardData['fabric']['type'] ?? 'N/A') . "\n";
                    $specifications .= "Quantity: " . ($wizardData['fabric']['quantity_meters'] ?? 0) . " meters\n";
                    $specifications .= "Intended Use: " . ($wizardData['fabric']['intended_use'] ?? 'N/A');
                }
                
                $customOrder = CustomOrder::create([
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
                    
                    // Fabric-specific fields
                    'fabric_type' => $wizardData['fabric']['type'] ?? null,
                    'fabric_quantity_meters' => $wizardData['fabric']['quantity_meters'] ?? null,
                    'intended_use' => $wizardData['fabric']['intended_use'] ?? null,
                    'fabric_specifications' => $wizardData['fabric']['fabric_specifications'] ?? null,
                    'special_requirements' => $wizardData['fabric']['special_requirements'] ?? null,

                    // Contact and delivery info
                    'delivery_type' => $formDeliveryType ?: ($details['delivery_type'] ?? null),
                    'delivery_address' => $formDeliveryAddr ?: ($details['delivery_address'] ?? null),
                    'phone' => $details['customer_phone'] ?? null,
                    'email' => $details['customer_email'] ?? null,
                ]);
                
                \Log::info('Fabric order created successfully', [
                    'order_id' => $customOrder->id,
                    'fabric_type' => $customOrder->fabric_type,
                    'quantity' => $customOrder->quantity,
                ]);
            }

            // Clear wizard session and backups
            $this->clearWizardSession($request);

            // Create notification for user
            \App\Models\Notification::createNotification(
                $userId,
                'custom_order',
                'Custom Order Submitted',
                "Your custom order #{$customOrder->id} has been submitted successfully and is now pending admin review.",
                route('custom_orders.show', $customOrder->id),
                [
                    'order_id' => $customOrder->id,
                    'order_name' => 'Custom Order #' . $customOrder->id,
                    'estimated_price' => $customOrder->estimated_price
                ]
            );

            // Create notification for admins
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            foreach ($adminUsers as $admin) {
                \App\Models\Notification::createNotification(
                    $admin->id,
                    'custom_order',
                    'New Custom Order',
                    "A new custom order #{$customOrder->id} has been submitted by {$customOrder->user->name}.",
                    url('/admin/custom-orders'),
                    [
                        'order_id' => $customOrder->id,
                        'customer_name' => $customOrder->user->name,
                        'order_name' => 'Custom Order #' . $customOrder->id,
                        'estimated_price' => $customOrder->estimated_price
                    ]
                );
            }

            // Redirect to success page
            return redirect()->route('custom_orders.success', ['order' => $customOrder->id]);

        } catch (\Exception $e) {
            \Log::error('Error completing wizard:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
            
            return view('custom_orders.success', compact('order'));
            
        } catch (\Exception $e) {
            \Log::error('Success page error: ' . $e->getMessage());
            
            return redirect()->route('custom_orders.index')
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
                    $order->design_upload = $request->file('design_upload')->store('custom_designs', 'public');
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

            return redirect()->route('custom_orders.index')->with('success', $message);

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

            if ($order->user_id !== Auth::id()) {
                \Log::error('CustomOrder access denied', [
                    'order_id' => $order->id,
                    'order_user_id' => $order->user_id,
                    'authenticated_user_id' => Auth::id(),
                    'reason' => 'Order does not belong to authenticated user'
                ]);
                abort(403, 'Unauthorized - This order does not belong to you');
            }

            $order->load('product');
            return view('custom_orders.show', compact('order'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('CustomOrder not found', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Custom Order not found');
            
        } catch (\Exception $e) {
            \Log::error('CustomOrder show error', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Server error: ' . $e->getMessage());
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
            return redirect()->route('custom_orders.payment', $order)->with('success', $message);
        } else {
            $order->status = 'cancelled';
            $order->rejection_reason = $request->reason;
            $order->save();
            $message = 'You have cancelled the price quote.';
            return redirect()->route('custom_orders.show', $order)->with('success', $message);
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

            if ($order->payment_status === 'paid') {
                \Log::info('showPayment - Order already paid', [
                    'order_id' => $order->id,
                    'payment_status' => $order->payment_status
                ]);
                return redirect()->route('custom_orders.show', $order)->with('info', 'This order is already paid.');
            }

            // Check if order is approved by admin
            if ($order->status !== 'approved') {
                \Log::info('showPayment - Order not yet approved', [
                    'order_id' => $order->id,
                    'status' => $order->status
                ]);
                return redirect()->route('custom_orders.show', $order)->with('info', 'Payment is only available after admin approval. Your order is currently ' . $order->status . '.');
            }

            \Log::info('showPayment - Loading relationships', [
                'order_id' => $order->id,
                'product_id' => $order->product_id,
                'user_id' => $order->user_id
            ]);

            // Load related product and user so the payment page can show full summary
            $order->load(['product', 'user']);
            
            \Log::info('showPayment - Rendering view', [
                'order_id' => $order->id,
                'product_loaded' => isset($order->product),
                'product_name' => $order->product->name ?? 'NULL',
                'user_loaded' => isset($order->user),
                'user_name' => $order->user->name ?? 'NULL'
            ]);

            return view('custom_orders.payment', compact('order'));
            
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
        try {
            $order = CustomOrder::findOrFail($id);
            
            if ($order->user_id !== Auth::id()) {
                abort(403, 'Unauthorized');
            }

            $request->validate([
                'payment_method' => 'required|in:online_banking,gcash,bank_transfer',
            ]);

            // Save payment method to order
            $order->payment_method = $request->payment_method;
            $order->save();
            
            // Handle different payment methods
            switch ($request->payment_method) {
                case 'gcash':
                    // Generate a simple transaction ID for now
                    $order->transaction_id = 'GCASH_' . strtoupper(uniqid());
                    $order->save();
                    return redirect()->route('custom_orders.payment.instructions', $order);
                
                case 'online_banking':
                    // Generate a simple transaction ID for now
                    $order->transaction_id = 'BANK_' . strtoupper(uniqid());
                    $order->save();
                    return redirect()->route('custom_orders.payment.instructions', $order);
                
                case 'bank_transfer':
                    return redirect()->route('custom_orders.payment.instructions', $order);
            }

            return back()->with('error', 'Payment initialization failed');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Custom Order not found');
        } catch (\Exception $e) {
            \Log::error('Payment processing failed: ' . $e->getMessage());
            return back()->with('error', 'Payment processing failed. Please try again.');
        }
    }

    public function showPaymentConfirm(CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('custom_orders.show', $order)->with('info', 'This order is already paid.');
        }

        if (!$order->payment_method) {
            return redirect()->route('custom_orders.payment', $order);
        }

        // For bank transfer, redirect to instructions page
        if ($order->payment_method === 'bank_transfer') {
            return redirect()->route('custom_orders.payment.instructions', $order);
        }

        $order->load('product');
        return view('custom_orders.payment_confirm', compact('order'));
    }

    /**
     * Process payment confirmation with receipt/transaction ID
     */
    public function confirmPayment(Request $request, CustomOrder $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'transaction_id' => 'required|string|max:255',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'payment_notes' => 'nullable|string|max:1000',
            'transfer_date' => 'nullable|date',
        ]);

        // Store receipt if uploaded
        if ($request->hasFile('receipt')) {
            $order->payment_receipt = $request->file('receipt')->store('payment_receipts', 'uploads');
        }

        $order->transaction_id = $request->transaction_id;
        $order->payment_notes = $request->payment_notes;
        
        if ($request->transfer_date) {
            $order->transfer_date = $request->transfer_date;
        }

        // For all payment methods, mark as 'pending' in the DB (valid enum value).
        // The UI treats 'pending' as a payment submitted / pending verification state
        // for custom orders, so this avoids enum truncation errors.
        $order->payment_status = 'pending';
        
        $order->save();

        return redirect()->route('custom_orders.show', $order)->with('success', 'Payment confirmation submitted! We will verify your payment shortly.');
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
            return redirect()->route('custom_orders.payment', $order);
        }

        // Create simple payment instructions based on payment method
        $baseInstructions = [
            'amount' => $order->final_price,
            'reference_code' => $order->transaction_id ?? 'REF_' . $order->id,
            'notes' => 'Please include your order ID (' . $order->id . ') in the payment reference.'
        ];

        switch ($order->payment_method) {
            case 'gcash':
                $instructions = array_merge($baseInstructions, [
                    'title' => 'GCash Payment Instructions',
                    'steps' => [
                        '1. Open your GCash app',
                        '2. Select "Send Money" or "Pay Bills"',
                        '3. Enter the GCash number: 09123456789',
                        '4. Enter the amount: ₱' . number_format($order->final_price, 2),
                        '5. Save the transaction reference number',
                        '6. Come back to this page to confirm payment'
                    ],
                    'gcash_number' => '09123456789',
                    'account_name' => 'Tuwas Yakan'
                ]);
                break;
                
            case 'online_banking':
                // This method is labeled as "Payment Center / E-wallet" in the UI.
                // Show GCash-style details so the user clearly sees GCash as an option.
                $instructions = array_merge($baseInstructions, [
                    'title' => 'Payment Center / E-wallet Instructions',
                    'steps' => [
                        '1. Open your preferred payment center or e-wallet app (e.g. GCash)',
                        '2. Choose Send Money / Pay Bills or similar option',
                        '3. Use the GCash or e-wallet details below to send the payment',
                        '4. Enter the amount: ₱' . number_format($order->final_price, 2),
                        '5. Save the transaction reference number',
                        '6. Come back to this page to confirm payment'
                    ],
                    // Reuse the GCash layout in the instructions view so GCash is clearly visible
                    'gcash_number' => '09123456789',
                    'account_name' => 'Tuwas Yakan',
                ]);
                break;
                
            case 'bank_transfer':
            default:
                $instructions = array_merge($baseInstructions, [
                    'title' => 'Bank Transfer Instructions',
                    'steps' => [
                        '1. Go to your bank or use online banking',
                        '2. Transfer funds to the account below',
                        '3. Enter the amount: ₱' . number_format($order->final_price, 2),
                        '4. Save the deposit slip or transaction reference',
                        '5. Come back to this page to confirm payment'
                    ],
                    'bank_name' => 'Sample Bank',
                    'account_name' => 'Tuwas Yakan',
                    'account_number' => '1234567890',
                    'branch' => 'Main Branch',
                    'swift_code' => 'SAMPLEBNKPH'
                ]);
                break;
        }
        
        return view('custom_orders.payment_instructions', compact('order', 'instructions'));
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
            
            // Calculate base price based on priority
            $basePrice = 1300; // Base price
            switch ($request->priority) {
                case 'priority':
                    $basePrice += 200;
                    break;
                case 'express':
                    $basePrice += 500;
                    break;
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

            return redirect()->route('custom_orders.payment', $customOrder->id)
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
            return redirect()->route('custom_orders.index')
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

        return redirect()->route('custom_orders.show', $order)
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
            return redirect()->route('custom_orders.show', $order)
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
                
                return redirect()->route('custom_orders.payment', $order)
                    ->with('success', 'Quote accepted! Please complete your payment to start production.');
            }
            
            return redirect()->route('custom_orders.show', $order)
                ->with('error', 'Unable to accept quote. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('Accept quote error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('custom_orders.show', $order)
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
            return redirect()->route('custom_orders.show', $order)
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
                
                return redirect()->route('custom_orders.index')
                    ->with('info', 'Quote rejected. The order has been cancelled.');
            }
            
            return redirect()->route('custom_orders.show', $order)
                ->with('error', 'Unable to reject quote. Please try again.');
                
        } catch (\Exception $e) {
            \Log::error('Reject quote error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('custom_orders.show', $order)
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
            return redirect()->route('custom_orders.show', $order)
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
            
            return redirect()->route('custom_orders.show', $order)
                ->with('success', 'Thank you for confirming! Your order is now complete.');
                
        } catch (\Exception $e) {
            \Log::error('Confirm order received error', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('custom_orders.show', $order)
                ->with('error', 'An error occurred. Please try again.');
        }
    }

}
