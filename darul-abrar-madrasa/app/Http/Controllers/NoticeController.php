<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoticeRequest;
use App\Http\Requests\UpdateNoticeRequest;
use App\Models\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Notice::with('publishedBy');

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('notice_for')) {
                $query->where('notice_for', $request->notice_for);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('publish_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('publish_date', '<=', $request->date_to);
            }

            $notices = $query->latest('publish_date')->paginate(15);

            return view('notices.index', compact('notices'));
        } catch (\Exception $e) {
            Log::error('Failed to load notices list', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notices. Please try again.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoticeRequest $request)
    {
        try {
            $data = $request->validated();
            $data['published_by'] = Auth::id();
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

            $notice = Notice::create($data);

            Log::info('Notice created successfully', [
                'notice_id' => $notice->id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('notices.index')
                ->with('success', 'Notice created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create notice', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create notice. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $notice = Notice::with('publishedBy')->findOrFail($id);
            return view('notices.show', compact('notice'));
        } catch (\Exception $e) {
            Log::error('Failed to load notice details', [
                'notice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notice details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $notice = Notice::findOrFail($id);
            return view('notices.edit', compact('notice'));
        } catch (\Exception $e) {
            Log::error('Failed to load notice edit form', [
                'notice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notice. Please try again.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoticeRequest $request, string $id)
    {
        try {
            $notice = Notice::findOrFail($id);
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

            $notice->update($data);

            Log::info('Notice updated successfully', [
                'notice_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('notices.index')
                ->with('success', 'Notice updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update notice', [
                'notice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update notice. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $notice = Notice::findOrFail($id);
            $notice->delete();

            Log::info('Notice deleted successfully', [
                'notice_id' => $id,
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('notices.index')
                ->with('success', 'Notice deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete notice', [
                'notice_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to delete notice. Please try again.');
        }
    }

    /**
     * Display public notices for students and teachers.
     */
    public function publicNotices()
    {
        try {
            $user = Auth::user();
            
            // Get user role
            $userRole = $user->role;
            
            // Query active, published, and not expired notices
            $query = Notice::active()
                ->published()
                ->notExpired()
                ->with('publishedBy');
            
            // Filter by user role
            if ($userRole === 'student') {
                $query->where(function ($q) {
                    $q->where('notice_for', 'all')
                      ->orWhere('notice_for', 'students');
                });
            } elseif ($userRole === 'teacher') {
                $query->where(function ($q) {
                    $q->where('notice_for', 'all')
                      ->orWhere('notice_for', 'teachers');
                });
            } elseif ($userRole === 'staff') {
                $query->where(function ($q) {
                    $q->where('notice_for', 'all')
                      ->orWhere('notice_for', 'staff');
                });
            } else {
                // Admin can see all notices
                $query->where('notice_for', 'all');
            }
            
            $notices = $query->latest('publish_date')->paginate(15);
            
            return view('notices.public', compact('notices'));
        } catch (\Exception $e) {
            Log::error('Failed to load public notices', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()->with('error', 'Failed to load notices. Please try again.');
        }
    }
}
