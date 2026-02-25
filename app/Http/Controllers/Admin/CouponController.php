<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;

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
        $data = $request->validate([
            'code' => 'required|string|alpha_num:ascii|unique:coupons,code',
            'type' => 'required|in:percent,fixed',
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
        Coupon::create($data);
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $request->validate([
            'code' => 'required|string|alpha_num:ascii|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percent,fixed',
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
        $coupon->update($data);
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->active = !$coupon->active;
        $coupon->save();
        return redirect()->back()->with('success', 'Coupon status updated');
    }
}
