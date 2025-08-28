<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClassRoom;
use App\Models\Exam;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Result;
use App\Models\GradingScale;
use Illuminate\Support\Facades\Auth;

class MarksEntry extends Component
{
    public $class_id = '';
    public $exam_id = '';
    public $students = [];
    public $subjects = [];
    public $marks = [];
    public $gradingScales = [];
    public $showTable = false;
    
    public function mount()
    {
        // Get grading scales for automatic grade calculation
        $this->gradingScales = GradingScale::orderBy('min_mark', 'desc')->get();
    }
    
    public function render()
    {
        $classes = ClassRoom::orderBy('name')->get();
        $exams = collect();
        
        if ($this->class_id) {
            $exams = Exam::where('class_id', $this->class_id)->orderBy('start_date', 'desc')->get();
        }
        
        return view('livewire.marks-entry', [
            'classes' => $classes,
            'exams' => $exams,
        ]);
    }
    
    public function updatedClassId()
    {
        $this->exam_id = '';
        $this->resetStudents();
    }
    
    public function updatedExamId()
    {
        $this->resetStudents();
        
        if ($this->class_id && $this->exam_id) {
            $this->loadStudentsAndSubjects();
        }
    }
    
    public function loadStudentsAndSubjects()
    {
        // Load students for the selected class
        $this->students = Student::where('class_id', $this->class_id)
            ->orderBy('roll_number')
            ->with('user')
            ->get();
            
        // Load subjects for the selected class
        $this->subjects = Subject::where('class_id', $this->class_id)
            ->orderBy('name')
            ->get();
            
        // Initialize marks array
        foreach ($this->students as $student) {
            foreach ($this->subjects as $subject) {
                // Check if marks already exist
                $result = Result::where('student_id', $student->id)
                    ->where('exam_id', $this->exam_id)
                    ->where('subject_id', $subject->id)
                    ->first();
                    
                if ($result) {
                    $this->marks[$student->id][$subject->id] = $result->marks_obtained;
                } else {
                    $this->marks[$student->id][$subject->id] = '';
                }
            }
        }
        
        $this->showTable = true;
    }
    
    public function calculateGrade($marks, $subjectId)
    {
        $subject = $this->subjects->firstWhere('id', $subjectId);
        $fullMark = $subject->full_mark;
        $passMark = $subject->pass_mark;
        
        // Calculate percentage
        $percentage = ($marks / $fullMark) * 100;
        
        // Find the appropriate grade
        $grade = 'F';
        $gpaPoint = 0;
        $isPassed = false;
        
        if ($marks >= $passMark) {
            $isPassed = true;
            
            foreach ($this->gradingScales as $scale) {
                if ($percentage >= $scale->min_mark) {
                    $grade = $scale->grade;
                    $gpaPoint = $scale->gpa;
                    break;
                }
            }
        }
        
        return [
            'grade' => $grade,
            'gpa_point' => $gpaPoint,
            'is_passed' => $isPassed
        ];
    }
    
    public function saveMarks()
    {
        // Validate marks
        $this->validate([
            'marks.*.*' => 'nullable|numeric|min:0',
        ], [
            'marks.*.*.numeric' => 'Marks must be a number',
            'marks.*.*.min' => 'Marks cannot be negative',
        ]);
        
        // Save marks for each student and subject
        foreach ($this->marks as $studentId => $subjectMarks) {
            foreach ($subjectMarks as $subjectId => $marks) {
                if ($marks === '') {
                    continue; // Skip empty marks
                }
                
                // Calculate grade, GPA, and pass/fail status
                $gradeInfo = $this->calculateGrade($marks, $subjectId);
                
                // Find existing result or create new one
                $result = Result::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'exam_id' => $this->exam_id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'marks_obtained' => $marks,
                        'grade' => $gradeInfo['grade'],
                        'gpa_point' => $gradeInfo['gpa_point'],
                        'is_passed' => $gradeInfo['is_passed'],
                        'remarks' => '',
                    ]
                );
            }
        }
        
        // Show success message
        session()->flash('success', 'Marks saved successfully!');
        
        // Reload students and subjects to refresh the data
        $this->loadStudentsAndSubjects();
    }
    
    private function resetStudents()
    {
        $this->students = [];
        $this->subjects = [];
        $this->marks = [];
        $this->showTable = false;
    }
}