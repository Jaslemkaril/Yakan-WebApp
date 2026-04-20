<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CouponController extends Controller
{
    public function index()
    {
        $coupons = Coupon::orderByDesc('created_at')->paginate(15);
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $this->ensureCouponColumnsExist();
        $supportsAppliesTo = Schema::hasColumn('coupons', 'applies_to');

        $data = $request->validate([
            'code' => 'required|string|alpha_num:ascii|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
            'applies_to' => ($supportsAppliesTo ? 'required' : 'nullable') . '|in:shipping,items',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'nullable|boolean',
        ]);
        $data['created_by'] = Auth::id();
        $data['active'] = (bool)($data['active'] ?? true);
        $data['min_spend'] = $data['min_spend'] ?? 0;

        if (!$supportsAppliesTo) {
            unset($data['applies_to']);
        }

        Coupon::create($data);
        $token = $request->input('auth_token') ?? $request->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('admin.coupons.index', $params)->with('success', 'Coupon created');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $this->ensureCouponColumnsExist();
        $supportsAppliesTo = Schema::hasColumn('coupons', 'applies_to');

        $data = $request->validate([
            'code' => 'required|string|alpha_num:ascii|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percent,fixed',
            'applies_to' => ($supportsAppliesTo ? 'required' : 'nullable') . '|in:shipping,items',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'min_spend' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'active' => 'nullable|boolean',
        ]);
        $data['active'] = (bool)($data['active'] ?? $coupon->active);
        $data['min_spend'] = $data['min_spend'] ?? 0;

        if (!$supportsAppliesTo) {
            unset($data['applies_to']);
        }

        $coupon->update($data);
        $token = $request->input('auth_token') ?? $request->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('admin.coupons.index', $params)->with('success', 'Coupon updated');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        $token = request()->input('auth_token') ?? request()->query('auth_token');
        $params = $token ? ['auth_token' => $token] : [];
        return redirect()->route('admin.coupons.index', $params)->with('success', 'Coupon deleted');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->active = !$coupon->active;
        $coupon->save();
        $token = request()->input('auth_token') ?? request()->query('auth_token');
        if ($token) {
            return redirect()->back()->withInput(['auth_token' => $token])->with('success', 'Coupon status updated');
        }
        return redirect()->back()->with('success', 'Coupon status updated');
    }

    private function ensureCouponColumnsExist(): void
    {
        if (!Schema::hasTable('coupons') || Schema::hasColumn('coupons', 'applies_to')) {
            return;
        }

        try {
            Schema::table('coupons', function (Blueprint $table) {
                if (!Schema::hasColumn('coupons', 'applies_to')) {
                    $table->enum('applies_to', ['shipping', 'items'])
                        ->default('shipping')
                        ->after('type');
                }
            });
        } catch (\Throwable $exception) {
            Log::warning('Unable to auto-add applies_to column on coupons table', [
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
