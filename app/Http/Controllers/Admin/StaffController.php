<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of staff users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::whereIn('role', ['staff', 'admin']);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role') && in_array($request->role, ['staff', 'admin'])) {
            $query->where('role', $request->role);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $staff = $query->paginate(20)->withQueryString();

        return view('admin.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StaffRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'เพิ่มพนักงานเรียบร้อยแล้ว');
    }

    /**
     * Show the form for editing the specified staff member.
     *
     * @return \Illuminate\View\View
     */
    public function edit(User $staff)
    {
        // Only allow editing staff and admin users
        if (! in_array($staff->role, ['staff', 'admin'])) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'ไม่สามารถแก้ไขผู้ใช้ประเภทนี้ได้');
        }

        return view('admin.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(StaffRequest $request, User $staff)
    {
        // Only allow updating staff and admin users
        if (! in_array($staff->role, ['staff', 'admin'])) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'ไม่สามารถแก้ไขผู้ใช้ประเภทนี้ได้');
        }

        // Prevent changing own role
        if ($staff->id === auth()->id() && $request->role !== $staff->role) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'ไม่สามารถเปลี่ยน Role ของตัวเองได้');
        }

        $data = $request->validated();

        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $staff->update($data);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'อัพเดทข้อมูลพนักงานเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $staff)
    {
        // Only allow deleting staff and admin users
        if (! in_array($staff->role, ['staff', 'admin'])) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'ไม่สามารถลบผู้ใช้ประเภทนี้ได้');
        }

        // Prevent deleting yourself
        if ($staff->id === auth()->id()) {
            return redirect()
                ->route('admin.staff.index')
                ->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        // Check if this is the last admin
        if ($staff->role === 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()
                    ->route('admin.staff.index')
                    ->with('error', 'ไม่สามารถลบผู้ดูแลระบบคนสุดท้ายได้');
            }
        }

        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'ลบพนักงานเรียบร้อยแล้ว');
    }
}
