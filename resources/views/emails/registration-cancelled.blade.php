<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Cancelled</title>
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
        .event-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
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
        <h1>Registration Cancelled</h1>
        <p>Islamic University of Technology - Islamic Society (SIKS)</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $user_name }},</p>
        
        <p>This email confirms that your registration for <strong>{{ $event_title }}</strong> has been cancelled.</p>
        
        <div class="event-details">
            <h3>Cancelled Registration Details</h3>
            <p><strong>Event:</strong> {{ $event_title }}</p>
            <p><strong>Event Date:</strong> {{ $event_date }}</p>
            <p><strong>Registration ID:</strong> #{{ $registration_id }}</p>
            <p><strong>Registration Type:</strong> {{ ucfirst($registration_type) }}</p>
            @if($team_name)
                <p><strong>Team Name:</strong> {{ $team_name }}</p>
            @endif
            <p><strong>Cancelled At:</strong> {{ $cancelled_at }}</p>
        </div>
        
        <p>Your registration has been successfully cancelled. If this was done in error or if you have any questions, please contact us immediately.</p>
        
        <p>You can still browse and register for other upcoming events on our website.</p>
        
        <p>Thank you for your interest in SIKS events.</p>
        
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