<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report - {{ $student->user->name ?? 'Student' }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 portrait;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 20pt;
            color: #333;
        }
        
        .header p {
            margin: 5px 0 0 0;
            font-size: 12pt;
            color: #666;
        }
        
        .report-meta {
            text-align: right;
            font-size: 9pt;
            color: #666;
            margin-bottom: 15px;
        }
        
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 6px 10px;
            border: 1px solid #ddd;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            background-color: #f5f5f5;
            width: 35%;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        
        .data-table th {
            background-color: #333;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
        }
        
        .data-table td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        
        .summary-value {
            font-size: 18pt;
            font-weight: bold;
            color: #333;
        }
        
        .summary-label {
            font-size: 9pt;
            color: #666;
            margin-top: 5px;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .remark-box {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }
        
        .remark-header {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .recommendation {
            border-left: 4px solid #666;
            padding: 8px 12px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            page-break-inside: avoid;
        }
        
        .recommendation.high {
            border-left-color: #dc3545;
        }
        
        .recommendation.medium {
            border-left-color: #ffc107;
        }
        
        .recommendation.low {
            border-left-color: #28a745;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #ddd;
            font-size: 9pt;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin-top: 50px;
            display: inline-block;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .mt-10 {
            margin-top: 10px;
        }
        
        .mb-10 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Darul Abrar Madrasa</h1>
        <p>Student Performance Report</p>
    </div>

    <!-- Report Metadata -->
    <div class="report-meta">
        <strong>Report Period:</strong> {{ $dateRange['start']->format('F d, Y') }} to {{ $dateRange['end']->format('F d, Y') }}<br>
        <strong>Report Type:</strong> {{ ucfirst($reportType) }} Report<br>
        <strong>Generated:</strong> {{ now()->format('F d, Y \a\t h:i A') }}
    </div>

    <!-- Student Information -->
    <div class="section">
        <div class="section-title">Student Information</div>
        <table class="info-table">
            <tr>
                <td>Student Name</td>
                <td>{{ $student->user->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Roll Number</td>
                <td>{{ $student->roll_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Class & Section</td>
                <td>{{ $student->class->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Department</td>
                <td>{{ $student->class->department->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td>Guardian Name</td>
                <td>{{ $guardian->user->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Academic Performance Summary -->
    <div class="section">
        <div class="section-title">Academic Performance Summary</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $academicPerformance['totalExams'] }}</div>
                <div class="summary-label">Total Exams</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($academicPerformance['averageGpa'], 2) }}</div>
                <div class="summary-label">Average GPA</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($academicPerformance['passRate'], 2) }}%</div>
                <div class="summary-label">Pass Rate</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    @if($academicPerformance['averageGpa'] >= 3.5)
                        A
                    @elseif($academicPerformance['averageGpa'] >= 3.0)
                        B
                    @elseif($academicPerformance['averageGpa'] >= 2.5)
                        C
                    @else
                        D
                    @endif
                </div>
                <div class="summary-label">Overall Grade</div>
            </div>
        </div>
    </div>

    <!-- Exam-wise Results -->
    @if($academicPerformance['examResults']->count() > 0)
    <div class="section">
        <div class="section-title">Detailed Exam Results</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Exam Name</th>
                    <th>Date</th>
                    <th>Total Marks</th>
                    <th>Full Marks</th>
                    <th>Percentage</th>
                    <th>GPA</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicPerformance['examResults'] as $examData)
                <tr>
                    <td>{{ $examData['exam']->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($examData['exam']->end_date ?? $examData['exam']->start_date)->format('M d, Y') }}</td>
                    <td>{{ number_format($examData['summary']['totalMarks'], 2) }}</td>
                    <td>{{ number_format($examData['summary']['totalFullMarks'], 2) }}</td>
                    <td>{{ number_format($examData['summary']['percentage'], 2) }}%</td>
                    <td>{{ number_format($examData['summary']['averageGpa'], 2) }}</td>
                    <td>
                        <span class="badge {{ $examData['summary']['status'] === 'Passed' ? 'badge-success' : 'badge-danger' }}">
                            {{ $examData['summary']['status'] }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Subject-wise Performance -->
    @foreach($academicPerformance['examResults'] as $examData)
    <div class="section">
        <div class="section-title">{{ $examData['exam']->name }} - Subject-wise Performance</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Marks Obtained</th>
                    <th>Full Marks</th>
                    <th>Percentage</th>
                    <th>Grade</th>
                    <th>GPA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($examData['results'] as $result)
                <tr>
                    <td>{{ $result->subject->name ?? 'N/A' }}</td>
                    <td>{{ number_format($result->marks_obtained, 2) }}</td>
                    <td>{{ number_format($result->subject->full_mark ?? 100, 2) }}</td>
                    <td>{{ number_format(($result->marks_obtained / ($result->subject->full_mark ?? 100)) * 100, 2) }}%</td>
                    <td>{{ $result->grade ?? 'N/A' }}</td>
                    <td>{{ number_format($result->gpa_point ?? 0, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    @endif

    <!-- Attendance Summary -->
    <div class="section">
        <div class="section-title">Attendance Summary</div>
        <table class="info-table">
            <tr>
                <td>Total School Days</td>
                <td>{{ $attendanceSummary['term']['totalDays'] }}</td>
            </tr>
            <tr>
                <td>Days Present</td>
                <td>{{ $attendanceSummary['term']['presentDays'] }}</td>
            </tr>
            <tr>
                <td>Days Absent</td>
                <td>{{ $attendanceSummary['term']['absentDays'] }}</td>
            </tr>
            <tr>
                <td>Days Late</td>
                <td>{{ $attendanceSummary['term']['lateDays'] }}</td>
            </tr>
            <tr>
                <td>Days on Leave</td>
                <td>{{ $attendanceSummary['term']['leaveDays'] }}</td>
            </tr>
            <tr>
                <td>Half Days</td>
                <td>{{ $attendanceSummary['term']['halfDays'] }}</td>
            </tr>
            <tr>
                <td><strong>Attendance Rate</strong></td>
                <td><strong>{{ number_format($attendanceSummary['term']['attendanceRate'], 2) }}%</strong></td>
            </tr>
        </table>
    </div>

    <!-- Fee Status -->
    <div class="section">
        <div class="section-title">Fee Status</div>
        <table class="info-table">
            <tr>
                <td>Total Fees</td>
                <td>{{ number_format($feeStatus['totalFees'], 2) }} BDT</td>
            </tr>
            <tr>
                <td>Amount Paid</td>
                <td>{{ number_format($feeStatus['paidAmount'], 2) }} BDT</td>
            </tr>
            <tr>
                <td>Amount Pending</td>
                <td style="{{ $feeStatus['pendingAmount'] > 0 ? 'color: #dc3545; font-weight: bold;' : '' }}">
                    {{ number_format($feeStatus['pendingAmount'], 2) }} BDT
                </td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td>
                    <span class="badge {{ $feeStatus['paymentStatus'] === 'Paid' ? 'badge-success' : 'badge-warning' }}">
                        {{ $feeStatus['paymentStatus'] }}
                    </span>
                </td>
            </tr>
        </table>

        @if($feeStatus['fees']->count() > 0)
        <table class="data-table mt-10">
            <thead>
                <tr>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Paid</th>
                    <th>Pending</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($feeStatus['fees'] as $fee)
                <tr>
                    <td>{{ $fee->fee_type ?? 'General Fee' }}</td>
                    <td>{{ number_format($fee->net_amount ?? $fee->amount, 2) }}</td>
                    <td>{{ number_format($fee->paid_amount ?? 0, 2) }}</td>
                    <td>{{ number_format(max(0, ($fee->net_amount ?? $fee->amount) - ($fee->paid_amount ?? 0)), 2) }}</td>
                    <td>{{ \Carbon\Carbon::parse($fee->due_date)->format('M d, Y') }}</td>
                    <td>
                        <span class="badge badge-{{ $fee->status === 'paid' ? 'success' : ($fee->status === 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($fee->status) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Teacher Remarks -->
    @if($teacherRemarks->count() > 0)
    <div class="section">
        <div class="section-title">Teacher Remarks</div>
        @foreach($teacherRemarks as $remark)
        <div class="remark-box">
            <div class="remark-header">{{ $remark['subject'] }} - {{ $remark['exam'] }}</div>
            <div>{{ $remark['remarks'] }}</div>
            <div style="font-size: 9pt; color: #666; margin-top: 5px;">
                {{ \Carbon\Carbon::parse($remark['date'])->format('F d, Y') }}
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="section">
        <div class="section-title">Teacher Remarks</div>
        <p style="color: #666; font-style: italic;">No teacher remarks available for this period.</p>
    </div>
    @endif

    <!-- Recommendations -->
    @if(count($recommendations) > 0)
    <div class="section">
        <div class="section-title">Recommendations</div>
        @foreach($recommendations as $index => $recommendation)
        <div class="recommendation {{ strtolower($recommendation['priority']) }}">
            <div style="font-weight: bold; margin-bottom: 5px;">
                {{ $index + 1 }}. {{ $recommendation['title'] }}
                <span class="badge badge-{{ $recommendation['priority'] === 'High' ? 'danger' : ($recommendation['priority'] === 'Medium' ? 'warning' : 'info') }}">
                    {{ $recommendation['priority'] }} Priority
                </span>
            </div>
            <div style="margin-bottom: 5px;">{{ $recommendation['description'] }}</div>
            <div style="font-style: italic; color: #666;">Action: {{ $recommendation['action'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 10pt;">Class Teacher Signature</div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 10pt;">Guardian Signature</div>
                </td>
            </tr>
        </table>
        <div class="text-center mt-10">
            <div class="signature-line" style="width: 250px;"></div>
            <div style="margin-top: 5px; font-size: 10pt;">Date: _________________</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="text-center">
            <p style="margin: 0;">This is a computer-generated report.</p>
            <p style="margin: 5px 0 0 0;">Darul Abrar Madrasa | Email: info@darulabrar.edu.bd | Phone: +880-XXX-XXXXXX</p>
        </div>
    </div>
</body>
</html>
