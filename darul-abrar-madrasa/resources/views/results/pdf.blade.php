<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Result Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .school-address {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            text-decoration: underline;
        }
        .student-info {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .marks-table th, .marks-table td {
            border: 1px solid #333;
            padding: 5px;
            text-align: center;
        }
        .marks-table th {
            background-color: #f2f2f2;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #333;
            padding: 5px;
        }
        .summary-table th {
            background-color: #f2f2f2;
            text-align: left;
        }
        .result-box {
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 20px;
        }
        .pass {
            color: green;
        }
        .fail {
            color: red;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature {
            width: 30%;
            text-align: center;
            border-top: 1px solid #333;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="school-name">Darul Abrar Model Kamil Madrasa</div>
            <div class="school-address">Dhaka, Bangladesh</div>
            <div class="document-title">RESULT SHEET</div>
        </div>
        
        <div class="student-info">
            <table class="info-table">
                <tr>
                    <td width="25%"><strong>Student Name:</strong></td>
                    <td width="25%">{{ $student->user->name }}</td>
                    <td width="25%"><strong>Class:</strong></td>
                    <td width="25%">{{ $student->class->name }}</td>
                </tr>
                <tr>
                    <td><strong>Roll Number:</strong></td>
                    <td>{{ $student->roll_number }}</td>
                    <td><strong>Admission Number:</strong></td>
                    <td>{{ $student->admission_number }}</td>
                </tr>
                <tr>
                    <td><strong>Exam:</strong></td>
                    <td>{{ $exam->name }}</td>
                    <td><strong>Exam Period:</strong></td>
                    <td>{{ $exam->start_date->format('d M, Y') }} to {{ $exam->end_date->format('d M, Y') }}</td>
                </tr>
            </table>
        </div>
        
        <table class="marks-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Full Mark</th>
                    <th>Pass Mark</th>
                    <th>Obtained Mark</th>
                    <th>Grade</th>
                    <th>GPA Point</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                <tr>
                    <td>{{ $result->subject->name }}</td>
                    <td>{{ $result->subject->full_mark }}</td>
                    <td>{{ $result->subject->pass_mark }}</td>
                    <td>{{ $result->marks_obtained }}</td>
                    <td>{{ $result->grade }}</td>
                    <td>{{ $result->gpa_point }}</td>
                    <td>{{ $result->is_passed ? 'Passed' : 'Failed' }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="1"><strong>Total</strong></td>
                    <td><strong>{{ $totalFullMarks }}</strong></td>
                    <td></td>
                    <td><strong>{{ $totalObtainedMarks }}</strong></td>
                    <td colspan="3"></td>
                </tr>
            </tbody>
        </table>
        
        <table class="summary-table">
            <tr>
                <th width="25%">Total Marks</th>
                <td width="25%">{{ $totalObtainedMarks }} out of {{ $totalFullMarks }}</td>
                <th width="25%">Percentage</th>
                <td width="25%">{{ $percentage }}%</td>
            </tr>
            <tr>
                <th>Position in Class</th>
                <td>{{ $position }}</td>
                <th>Failed Subjects</th>
                <td>{{ $failedSubjects }}</td>
            </tr>
            <tr>
                <th>GPA</th>
                <td>{{ $results->avg('gpa_point') ? number_format($results->avg('gpa_point'), 2) : 'N/A' }}</td>
                <th>Overall Grade</th>
                <td>
                    @php
                        $avgGpa = $results->avg('gpa_point');
                        $grade = 'F';
                        
                        if ($failedSubjects == 0) {
                            if ($avgGpa >= 5.0) $grade = 'A+';
                            elseif ($avgGpa >= 4.0) $grade = 'A';
                            elseif ($avgGpa >= 3.5) $grade = 'A-';
                            elseif ($avgGpa >= 3.0) $grade = 'B+';
                            elseif ($avgGpa >= 2.5) $grade = 'B';
                            elseif ($avgGpa >= 2.0) $grade = 'C+';
                            elseif ($avgGpa >= 1.5) $grade = 'C';
                            elseif ($avgGpa >= 1.0) $grade = 'D';
                        }
                        
                        echo $grade;
                    @endphp
                </td>
            </tr>
        </table>
        
        <div class="result-box {{ $failedSubjects > 0 ? 'fail' : 'pass' }}">
            {{ $failedSubjects > 0 ? 'FAILED' : 'PASSED' }}
        </div>
        
        <div class="signature-section">
            <div class="signature">
                Class Teacher
            </div>
            <div class="signature">
                Exam Controller
            </div>
            <div class="signature">
                Principal
            </div>
        </div>
    </div>
</body>
</html>