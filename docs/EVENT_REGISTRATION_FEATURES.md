# Event Registration Features

This document outlines the new event registration features implemented in Task 6.

## New Event Fields

### Basic Information
- **Fest Association**: Events can now be associated with a parent fest
- **Event Type**: Quiz, Lecture, Donation, Competition, Workshop
- **Location**: Physical location of the event

### Registration Settings
- **Registration Type**: 
  - Individual Only
  - Team Only
  - Both Individual & Team
  - On-Spot Registration
- **Max Participants**: Optional capacity limit
- **Registration Fee**: Fee amount in BDT (0 for free events)
- **Registration Deadline**: Optional deadline for registrations
- **Status**: Draft, Published, Completed

## Registration Status Logic

### Registration Open Conditions
An event registration is open when ALL of the following are true:
1. Event status is "published"
2. Registration type is NOT "on_spot"
3. Registration deadline has not passed (if set)
4. Event is not at maximum capacity (if max_participants is set)

### Registration Status Display
- **Registration Open**: Green badge, shows registration buttons
- **On-Spot Registration**: Blue badge, shows venue registration message
- **Event Full**: Red badge when maximum capacity reached
- **Registration Closed**: Yellow badge when deadline passed
- **Registration Unavailable**: Gray badge for other cases

## Event Display Features

### Event Index Page
- Grid layout with event cards
- Registration status badges
- Capacity progress bars
- Fee information display
- Event type and location

### Event Detail Page
- Comprehensive registration information panel
- Registration buttons (Individual/Team) when applicable
- Capacity tracking with progress bar
- Fee and deadline information
- User registration status display

### Event Forms
- Enhanced create/edit forms with all new fields
- Fest association dropdown
- Registration type selection
- Capacity and fee settings
- JavaScript form enhancements for better UX

## Event Model Methods

### Registration Status Methods
- `isRegistrationOpen()`: Check if registration is currently open
- `getRegisteredCount()`: Get count of approved registrations
- `getAvailableSpots()`: Get remaining capacity
- `isFull()`: Check if event is at capacity
- `requiresPayment()`: Check if event has a fee

### Registration Type Methods
- `allowsIndividualRegistration()`: Check if individual registration allowed
- `allowsTeamRegistration()`: Check if team registration allowed

### Date/Status Methods
- `isUpcoming()`: Check if event is in the future
- `isToday()`: Check if event is today
- `isCompleted()`: Check if event is completed

## Form Validation

### Server-Side Validation
- All required fields validated
- Date validation (event date must be in future)
- Enum validation for type, registration_type, status
- Numeric validation for capacity and fee
- Registration deadline must be before event date

### Client-Side Enhancements
- Dynamic form field enabling/disabling
- Date picker constraints
- Fee amount formatting
- Real-time validation feedback

## Authorization

Events use Laravel policies for authorization:
- View: Public for published events
- Create: Requires appropriate admin role
- Update: Event author or admin
- Delete: Event author or admin

## Testing

Comprehensive test suite covers:
- Event creation with new fields
- Event display with registration information
- Form validation rules
- Registration status logic
- Authorization policies

## Future Integration

This implementation provides the foundation for:
- Individual registration system (Task 7)
- Team registration system (Task 8)
- Payment workflow (Task 9)
- Admin registration management (Task 10)

The event system is now fully prepared to handle complex registration workflows while maintaining a clean, user-friendly interface.