<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Confirmation</title>
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
        .event-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #76C990;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3cd;
            color: #856404;
        }
        .payment-info {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Registration Confirmation</h1>
        <p>Islamic University of Technology - Islamic Society (SIKS)</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $user_name }},</p>
        
        <p>Thank you for registering for <strong>{{ $event_title }}</strong>! Your registration has been successfully submitted.</p>
        
        <div class="event-details">
            <h3>Event Details</h3>
            <p><strong>Event:</strong> {{ $event_title }}</p>
            <p><strong>Date:</strong> {{ $event_date }}</p>
            <p><strong>Time:</strong> {{ $event_time }}</p>
            <p><strong>Registration Type:</strong> {{ ucfirst($registration_type) }}</p>
            @if($team_name)
                <p><strong>Team Name:</strong> {{ $team_name }}</p>
            @endif
        </div>
        
        <div class="event-details">
            <h3>Registration Information</h3>
            <p><strong>Registration ID:</strong> #{{ $registration_id }}</p>
            <p><strong>Status:</strong> <span class="status-badge status-pending">{{ ucfirst($status) }}</span></p>
            <p><strong>Registered At:</strong> {{ now()->format('F j, Y g:i A') }}</p>
        </div>
        
        @if($payment_required)
        <div class="payment-info">
            <h3>Payment Information</h3>
            <p><strong>Registration Fee:</strong> ৳{{ number_format($payment_amount, 2) }}</p>
            <p>Your registration is currently pending payment verification. Our admin team will review your payment details and approve your registration once verified.</p>
        </div>
        @endif
        
        <h3>What's Next?</h3>
        <ul>
            @if($payment_required)
                <li>Our admin team will verify your payment details within 24-48 hours</li>
            @endif
            <li>You will receive an email notification once your registration is approved</li>
            <li>Event reminders and updates will be sent to your email</li>
            <li>You can check your registration status anytime by logging into the website</li>
        </ul>
        
        <p>If you have any questions or need to make changes to your registration, please contact us as soon as possible.</p>
        
        <p>We look forward to seeing you at the event!</p>
        
        <p>Best regards,<br>
        <strong>SIKS Admin Team</strong><br>
        Islamic University of Technology</p>
    </div>
    
    <div class="footer">
        <p>This is an automated email. Please do not reply to this message.</p>
        <p>© {{ date('Y') }} Islamic University of Technology - Islamic Society</p>
    </div>
</body>
</html>