<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
        try {
            $query = Department::withCount(['classes', 'teachers']);

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            $departments = $query->latest()->paginate(15);

            return view('departments.index', compact('departments'));
        } catch (\Exception $e) {
            Log::error('Failed to load departments list', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load departments. Please try again.');
        }
    }

    /**
     * Show the form for creating a new department.
     */
    public function create()
    {
        return view('departments.create');
    }

    /**
     * Store a newly created department in storage.
     */
    public function store(StoreDepartmentRequest $request)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $department = Department::create($data);

            Log::info('Department created successfully', [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create department', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to create department. Please try again.');
        }
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
        try {
            $department->loadCount(['classes', 'teachers']);
            
            // Get classes with student count
            $classes = $department->classes()
                ->withCount('students')
                ->with('department')
                ->get();
            
            // Get teachers
            $teachers = $department->teachers()
                ->with('user')
                ->get();

            return view('departments.show', compact('department', 'classes', 'teachers'));
        } catch (\Exception $e) {
            Log::error('Failed to load department details', [
                'department_id' => $department->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to load department details. Please try again.');
        }
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    /**
     * Update the specified department in storage.
     */
    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        try {
            $data = $request->validated();
            $data['is_active'] = $request->has('is_active') ? 1 : 0;

            $department->update($data);

            Log::info('Department updated successfully', [
                'department_id' => $department->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update department', [
                'department_id' => $department->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'data' => $request->validated(),
            ]);

            return back()->withInput()->with('error', 'Failed to update department. Please try again.');
        }
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        try {
            // Check if department has classes
            if ($department->classes()->count() > 0) {
                Log::warning('Attempted to delete department with existing classes', [
                    'department_id' => $department->id,
                    'classes_count' => $department->classes()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('departments.index')
                    ->with('error', 'Cannot delete department with existing classes. Please reassign or delete classes first.');
            }

            // Check if department has teachers
            if ($department->teachers()->count() > 0) {
                Log::warning('Attempted to delete department with existing teachers', [
                    'department_id' => $department->id,
                    'teachers_count' => $department->teachers()->count(),
                    'user_id' => auth()->id(),
                ]);

                return redirect()->route('departments.index')
                    ->with('error', 'Cannot delete department with existing teachers. Please reassign or delete teachers first.');
            }

            $departmentId = $department->id;
            $department->delete();

            Log::info('Department deleted successfully', [
                'department_id' => $departmentId,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to delete department', [
                'department_id' => $department->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Failed to delete department. Please try again.');
        }
    }
}
