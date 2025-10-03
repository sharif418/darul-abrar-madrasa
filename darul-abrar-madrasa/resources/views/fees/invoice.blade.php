<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Invoice #{{ $fee->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        .invoice-header {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .invoice-header-content {
            display: flex;
            justify-content: space-between;
        }
        .logo-container {
            width: 30%;
        }
        .logo {
            max-width: 150px;
            height: auto;
        }
        .invoice-title {
            width: 70%;
            text-align: right;
        }
        .invoice-title h1 {
            color: #3b82f6;
            margin: 0;
            font-size: 28px;
        }
        .invoice-title p {
            margin: 5px 0 0;
            color: #666;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info-left, .invoice-info-right {
            width: 48%;
        }
        .invoice-info h2 {
            font-size: 16px;
            margin: 0 0 10px;
            color: #3b82f6;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .invoice-info p {
            margin: 5px 0;
        }
        .invoice-details {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 12px;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-row td {
            border-top: 2px solid #e5e7eb;
            border-bottom: none;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .payment-status {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .paid {
            background-color: #dcfce7;
            color: #166534;
        }
        .partial {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .unpaid {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
        .footer p {
            margin: 5px 0;
        }
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 40%;
            border-top: 1px solid #333;
            padding-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="invoice-header-content">
                <div class="logo-container">
                    <h2>Darul Abrar Model Kamil Madrasa</h2>
                </div>
                <div class="invoice-title">
                    <h1>INVOICE</h1>
                    <p>Invoice #: {{ $fee->id }}</p>
                    <p>Date: {{ now()->format('F d, Y') }}</p>
                </div>
            </div>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-info-left">
                <h2>Billed To:</h2>
                <p><strong>{{ $fee->student->name }}</strong></p>
                <p>Student ID: {{ $fee->student->student_id }}</p>
                <p>Class: {{ $fee->student->class->name }}</p>
                <p>Roll: {{ $fee->student->roll_number }}</p>
                <p>Phone: {{ $fee->student->phone }}</p>
            </div>
            <div class="invoice-info-right">
                <h2>Payment Information:</h2>
                <p><strong>Fee Type:</strong> {{ ucfirst($fee->fee_type) }}</p>
                <p><strong>Due Date:</strong> {{ $fee->due_date->format('F d, Y') }}</p>
                <p><strong>Status:</strong> {{ ucfirst($fee->status) }}</p>
                @if($fee->status != 'unpaid')
                    <p><strong>Payment Date:</strong> {{ $fee->payment_date ? $fee->payment_date->format('F d, Y') : 'N/A' }}</p>
                    <p><strong>Payment Method:</strong> {{ $fee->payment_method ? ucfirst($fee->payment_method) : 'N/A' }}</p>
                    @if($fee->transaction_id)
                        <p><strong>Transaction ID:</strong> {{ $fee->transaction_id }}</p>
                    @endif
                @endif
            </div>
        </div>
        
        <div class="invoice-details">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="amount">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ ucfirst($fee->fee_type) }} Fee</td>
                        <td class="amount">{{ number_format($fee->amount, 2) }}</td>
                    </tr>
                    @if($fee->status != 'unpaid')
                        <tr>
                            <td>Amount Paid</td>
                            <td class="amount">{{ number_format($fee->paid_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($fee->status == 'partial')
                        <tr class="total-row">
                            <td>Balance Due</td>
                            <td class="amount">{{ number_format($fee->remainingAmount, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div class="payment-status {{ $fee->status }}">
                @if($fee->status == 'paid')
                    PAID IN FULL
                @elseif($fee->status == 'partial')
                    PARTIALLY PAID - {{ number_format($fee->remainingAmount, 2) }} REMAINING
                @else
                    UNPAID
                @endif
            </div>
        </div>
        
        <div class="signature">
            <div class="signature-box">
                <p>Authorized Signature</p>
            </div>
            <div class="signature-box">
                <p>Received By</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Darul Abrar Model Kamil Madrasa</p>
            <p>Address: 123 Education Street, City, Country</p>
            <p>Phone: (123) 456-7890 | Email: info@darulabrar.edu</p>
            <p>Thank you for your payment!</p>
        </div>
    </div>
</body>
</html>