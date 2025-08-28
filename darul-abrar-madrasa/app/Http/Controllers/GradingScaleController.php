<?php

namespace App\Http\Controllers;

use App\Models\GradingScale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradingScaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gradingScales = GradingScale::orderBy('min_mark', 'desc')->get();
        return view('academic.grading_scales.index', compact('gradingScales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('academic.grading_scales.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'grade_name' => 'required|string|max:10|unique:grading_scales',
            'gpa_point' => 'required|numeric|min:0|max:5',
            'min_mark' => 'required|numeric|min:0|max:100',
            'max_mark' => 'required|numeric|min:0|max:100|gte:min_mark',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check for overlapping mark ranges
        $overlapping = GradingScale::where(function($query) use ($request) {
            $query->where(function($q) use ($request) {
                $q->where('min_mark', '<=', $request->min_mark)
                  ->where('max_mark', '>=', $request->min_mark);
            })->orWhere(function($q) use ($request) {
                $q->where('min_mark', '<=', $request->max_mark)
                  ->where('max_mark', '>=', $request->max_mark);
            })->orWhere(function($q) use ($request) {
                $q->where('min_mark', '>=', $request->min_mark)
                  ->where('max_mark', '<=', $request->max_mark);
            });
        })->exists();

        if ($overlapping) {
            return back()->withInput()->withErrors(['min_mark' => 'The mark range overlaps with an existing grade.']);
        }

        GradingScale::create($request->all());

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GradingScale $gradingScale)
    {
        return view('academic.grading_scales.show', compact('gradingScale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GradingScale $gradingScale)
    {
        return view('academic.grading_scales.edit', compact('gradingScale'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GradingScale $gradingScale)
    {
        $request->validate([
            'grade_name' => [
                'required',
                'string',
                'max:10',
                Rule::unique('grading_scales')->ignore($gradingScale->id),
            ],
            'gpa_point' => 'required|numeric|min:0|max:5',
            'min_mark' => 'required|numeric|min:0|max:100',
            'max_mark' => 'required|numeric|min:0|max:100|gte:min_mark',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Check for overlapping mark ranges
        $overlapping = GradingScale::where('id', '!=', $gradingScale->id)
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                    $q->where('min_mark', '<=', $request->min_mark)
                      ->where('max_mark', '>=', $request->min_mark);
                })->orWhere(function($q) use ($request) {
                    $q->where('min_mark', '<=', $request->max_mark)
                      ->where('max_mark', '>=', $request->max_mark);
                })->orWhere(function($q) use ($request) {
                    $q->where('min_mark', '>=', $request->min_mark)
                      ->where('max_mark', '<=', $request->max_mark);
                });
            })->exists();

        if ($overlapping) {
            return back()->withInput()->withErrors(['min_mark' => 'The mark range overlaps with an existing grade.']);
        }

        $gradingScale->update($request->all());

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GradingScale $gradingScale)
    {
        $gradingScale->delete();

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale deleted successfully.');
    }
    
    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleActive(GradingScale $gradingScale)
    {
        $gradingScale->update([
            'is_active' => !$gradingScale->is_active
        ]);

        return redirect()->route('grading-scales.index')
            ->with('success', 'Grading scale status updated successfully.');
    }
}