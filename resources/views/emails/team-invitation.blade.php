<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Invitation - {{ $event_title }}</title>
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            text-align: center;
        }
        .accept-button {
            background-color: #76C990;
            color: white;
        }
        .decline-button {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .team-info {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Team Invitation</h1>
        <p>You've been invited to join a team!</p>
    </div>

    <div class="content">
        <p>Dear {{ $invitee_name }},</p>

        <p>You have been invited by <strong>{{ $team_leader_name }}</strong> to join their team for the upcoming event.</p>

        <div class="team-info">
            <h3 style="margin-top: 0; color: #2d5016;">Team: {{ $team_name }}</h3>
            <p style="margin-bottom: 0;"><strong>Team Leader:</strong> {{ $team_leader_name }}</p>
        </div>

        <div class="event-details">
            <h3 style="margin-top: 0; color: #76C990;">Event Details</h3>
            <p><strong>Event:</strong> {{ $event_title }}</p>
            <p><strong>Date:</strong> {{ $event_date }}</p>
            @if($event_time !== 'TBA')
            <p><strong>Time:</strong> {{ $event_time }}</p>
            @endif
            @if($event_location)
            <p><strong>Location:</strong> {{ $event_location }}</p>
            @endif
        </div>

        <p>By accepting this invitation, you will become a member of <strong>{{ $team_name }}</strong> and participate in the event as part of this team.</p>

        <div class="button-container">
            <a href="{{ $accept_url }}" class="button accept-button">Accept Invitation</a>
            <a href="{{ $decline_url }}" class="button decline-button">Decline Invitation</a>
        </div>

        <p><strong>Important Notes:</strong></p>
        <ul>
            <li>You must respond to this invitation before the registration deadline</li>
            <li>Once you accept, you will be part of the team registration</li>
            <li>If you decline, you can still register individually if the event allows it</li>
            <li>Contact the team leader if you have any questions about the team or event</li>
        </ul>

        <p>If you're unable to click the buttons above, you can copy and paste these links into your browser:</p>
        <p style="font-size: 12px; word-break: break-all;">
            <strong>Accept:</strong> {{ $accept_url }}<br>
            <strong>Decline:</strong> {{ $decline_url }}
        </p>
    </div>

    <div class="footer">
        <p>This invitation was sent by the Islamic Society of IUT (SIKS) event management system.</p>
        <p>If you believe you received this email in error, please contact the event organizers.</p>
    </div>
</body>
</html>