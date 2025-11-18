<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Approved</title>
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
            background-color: #76C990;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .success-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #c3e6cb;
        }
        .details {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #ddd;
        }
        .details h3 {
            margin-top: 0;
            color: #76C990;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background-color: #76C990;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Approved!</h1>
        <p>Your payment has been verified successfully</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $user_name }},</p>
        
        <div class="success-badge">
            <strong>✓ Payment Verified</strong> - Your payment for {{ $event_title }} has been approved by our admin team.
        </div>
        
        <p>We're pleased to inform you that your payment has been successfully verified. Your registration is now being processed for final approval.</p>
        
        <div class="details">
            <h3>Payment Details</h3>
            <div class="detail-row">
                <span class="label">Event:</span>
                <span class="value">{{ $event_title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Registration ID:</span>
                <span class="value">#{{ $registration_id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Amount Paid:</span>
                <span class="value">৳{{ number_format($payment_amount, 2) }}</span>
            </div>
            @if($transaction_id)
            <div class="detail-row">
                <span class="label">Transaction ID:</span>
                <span class="value">{{ $transaction_id }}</span>
            </div>
            @endif
            @if($registration_type === 'team' && $team_name)
            <div class="detail-row">
                <span class="label">Team Name:</span>
                <span class="value">{{ $team_name }}</span>
            </div>
            @endif
        </div>
        
        <div class="details">
            <h3>Event Information</h3>
            <div class="detail-row">
                <span class="label">Date:</span>
                <span class="value">{{ $event_date }}</span>
            </div>
            @if($event_time !== 'TBA')
            <div class="detail-row">
                <span class="label">Time:</span>
                <span class="value">{{ $event_time }}</span>
            </div>
            @endif
        </div>
        
        <p><strong>What's Next?</strong></p>
        <ul>
            <li>Your registration is now under final review</li>
            <li>You will receive another email once your registration is fully approved</li>
            <li>Keep an eye out for event updates and reminders</li>
        </ul>
        
        <p>If you have any questions about your registration or payment, please don't hesitate to contact us.</p>
        
        <p>Thank you for your participation!</p>
        
        <p>Best regards,<br>
        <strong>Islamic University of Technology Islamic Society (SIKS)</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you need assistance, please contact us through our official channels.</p>
    </div>
</body>
</html>