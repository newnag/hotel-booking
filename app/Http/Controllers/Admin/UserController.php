<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of guest users.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'guest');

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Line integration status
        if ($request->filled('line_status')) {
            if ($request->line_status === 'connected') {
                $query->whereNotNull('line_user_id');
            } elseif ($request->line_status === 'not_connected') {
                $query->whereNull('line_user_id');
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for editing the specified user.
     *
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        // Only allow editing guest users
        if ($user->role !== 'guest') {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'ไม่สามารถแก้ไขผู้ใช้ประเภทนี้ได้');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRequest $request, User $user)
    {
        // Only allow updating guest users
        if ($user->role !== 'guest') {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'ไม่สามารถแก้ไขผู้ใช้ประเภทนี้ได้');
        }

        $data = $request->validated();

        // Only update password if provided
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'อัพเดทข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified user from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        // Only allow deleting guest users
        if ($user->role !== 'guest') {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'ไม่สามารถลบผู้ใช้ประเภทนี้ได้');
        }

        // Check if user has future bookings
        $futureBookingsCount = $user->bookings()
            ->where('start_datetime', '>', now())
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($futureBookingsCount > 0) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', "ไม่สามารถลบผู้ใช้ได้ เนื่องจากมีการจองในอนาคต {$futureBookingsCount} รายการ");
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้เรียบร้อยแล้ว');
    }
}
