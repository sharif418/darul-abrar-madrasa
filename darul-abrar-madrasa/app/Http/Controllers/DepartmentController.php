<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments.
     */
    public function index(Request $request)
    {
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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified department.
     */
    public function show(Department $department)
    {
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
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department from storage.
     */
    public function destroy(Department $department)
    {
        // Check if department has classes or teachers
        if ($department->classes()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete department with existing classes. Please reassign or delete classes first.');
        }

        if ($department->teachers()->count() > 0) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete department with existing teachers. Please reassign or delete teachers first.');
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
