<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Verification Required</title>
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
            background-color: #dc3545;
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
        .warning-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #ffeaa7;
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
            color: #dc3545;
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
        .reason-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #f5c6cb;
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
            text-align: center;
        }
        .instructions {
            background-color: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Verification Required</h1>
        <p>Action needed for your registration</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $user_name }},</p>
        
        <div class="warning-badge">
            <strong>⚠ Payment Verification Issue</strong> - Your payment for {{ $event_title }} requires attention.
        </div>
        
        <p>We've reviewed your payment submission for the event registration, but unfortunately, we were unable to verify the payment details provided.</p>
        
        <div class="reason-box">
            <h4>Reason for Rejection:</h4>
            <p>{{ $rejection_reason }}</p>
        </div>
        
        <div class="details">
            <h3>Registration Details</h3>
            <div class="detail-row">
                <span class="label">Event:</span>
                <span class="value">{{ $event_title }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Registration ID:</span>
                <span class="value">#{{ $registration_id }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Required Amount:</span>
                <span class="value">৳{{ number_format($payment_amount, 2) }}</span>
            </div>
            @if($transaction_id)
            <div class="detail-row">
                <span class="label">Submitted Transaction ID:</span>
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
        
        <div class="instructions">
            <h4>What You Need to Do:</h4>
            <ol>
                <li><strong>Review the rejection reason</strong> mentioned above</li>
                <li><strong>Verify your payment details</strong> and ensure they are correct</li>
                <li><strong>Resubmit your payment information</strong> with the correct details</li>
                <li><strong>Contact us</strong> if you believe this is an error</li>
            </ol>
        </div>
        
        <div style="text-align: center;">
            <a href="{{ $resubmit_url }}" class="button">Resubmit Payment Details</a>
        </div>
        
        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>Your registration spot is still reserved while you resubmit payment details</li>
            <li>Please resubmit your payment information as soon as possible</li>
            <li>Make sure all payment details (transaction ID, amount, date) are accurate</li>
            <li>Contact our support team if you need assistance</li>
        </ul>
        
        <p><strong>Common Issues:</strong></p>
        <ul>
            <li>Incorrect transaction ID or reference number</li>
            <li>Payment amount doesn't match the required fee</li>
            <li>Payment date is outside the acceptable range</li>
            <li>Payment method details are unclear or incomplete</li>
        </ul>
        
        <p>We apologize for any inconvenience. Our team is here to help ensure your registration is processed smoothly.</p>
        
        <p>Best regards,<br>
        <strong>Islamic University of Technology Islamic Society (SIKS)</strong></p>
    </div>
    
    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>If you need assistance, please contact us through our official channels.</p>
    </div>
</body>
</html>