<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4F46E5;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
        }
        .summary-table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 10px;
            border: 1px solid #e5e7eb;
        }
        .summary-table td:first-child {
            font-weight: bold;
            background-color: #f3f4f6;
            width: 40%;
        }
        .footer {
            background-color: #f3f4f6;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 5px 5px;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .contact-info {
            margin-top: 15px;
            padding: 10px;
            background-color: white;
            border-left: 4px solid #4F46E5;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Darul Abrar Madrasa</h1>
        <p style="margin: 5px 0 0 0;">Student Performance Report</p>
    </div>

    <div class="content">
        <p>Dear {{ $guardian->user->name ?? 'Guardian' }},</p>

        <p>Please find attached the comprehensive performance report for <strong>{{ $student->user->name ?? 'Student' }}</strong> covering the period from <strong>{{ $dateRange['start']->format('F d, Y') }}</strong> to <strong>{{ $dateRange['end']->format('F d, Y') }}</strong>.</p>

        <p>This report includes:</p>
        <ul>
            <li>Academic performance and detailed exam results</li>
            <li>Attendance summary and statistics</li>
            <li>Fee status and payment information</li>
            <li>Teacher remarks and feedback</li>
            <li>Personalized recommendations for improvement</li>
        </ul>

        <h3 style="color: #4F46E5; margin-top: 20px;">Key Highlights</h3>
        <table class="summary-table">
            <tr>
                <td>Average GPA</td>
                <td>{{ number_format($academicPerformance['averageGpa'], 2) }}</td>
            </tr>
            <tr>
                <td>Attendance Rate</td>
                <td>{{ number_format($attendanceSummary['term']['attendanceRate'], 2) }}%</td>
            </tr>
            <tr>
                <td>Total Exams</td>
                <td>{{ $academicPerformance['totalExams'] }}</td>
            </tr>
            <tr>
                <td>Pass Rate</td>
                <td>{{ number_format($academicPerformance['passRate'], 2) }}%</td>
            </tr>
            @if($feeStatus['pendingAmount'] > 0)
            <tr>
                <td>Pending Fees</td>
                <td style="color: #dc2626; font-weight: bold;">{{ number_format($feeStatus['pendingAmount'], 2) }} BDT</td>
            </tr>
            @endif
        </table>

        <p><strong>For detailed information, please review the attached PDF report.</strong></p>

        <p>If you have any questions or concerns regarding your child's performance, please do not hesitate to contact the school administration.</p>

        <div class="contact-info">
            <strong>Contact Information:</strong><br>
            Darul Abrar Madrasa<br>
            Email: info@darulabrar.edu.bd<br>
            Phone: +880-XXX-XXXXXX
        </div>

        <p style="margin-top: 20px;">Best regards,<br>
        <strong>Darul Abrar Madrasa Administration</strong></p>
    </div>

    <div class="footer">
        <p style="margin: 0;">This is an automated email. Please do not reply to this email.</p>
        <p style="margin: 5px 0 0 0;">Generated on {{ now()->format('F d, Y \a\t h:i A') }}</p>
    </div>
</body>
</html>
