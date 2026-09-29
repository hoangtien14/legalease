# LegalEase v4 – Business Logic

## Core principle
Every important action follows: Actor → Preconditions → Validation → Data change → State transition → Notification → Next allowed action.

## Actors
- **Customer:** register/login, profile, search verified lawyers, view availability, book, reschedule/cancel, view appointments, review completed appointments.
- **Lawyer:** register, maintain profile/specializations, wait for verification, create availability, confirm/cancel/complete appointments, reschedule.
- **Super Admin:** manage users/lawyers/master data/content, verify lawyers, monitor appointments and reports.

## Lawyer lifecycle
`pending → verified` or `pending → rejected`.
Only `verified + active` lawyers are publicly bookable and may create availability.

## Availability lifecycle
`available → booked` when a booking succeeds.
`booked → available` only when the related appointment is cancelled.
Blocked slots are not bookable. Overlapping slots for the same lawyer/date are rejected.

## Appointment lifecycle
Allowed transitions:
- `pending → confirmed | cancelled`
- `confirmed → completed | cancelled | rescheduled`
- `rescheduled → confirmed | completed | cancelled`
- `completed` and `cancelled` are terminal.

Booking rules:
1. Lawyer must be verified and active.
2. Slot must belong to that lawyer and be available.
3. Slot start must not be in the past.
4. Selected specialization must belong to the lawyer.
5. Customer cannot have another active appointment at the same time.
6. Booking and slot locking occur in one database transaction.

Reschedule rules:
- Only pending/confirmed/rescheduled appointments can be rescheduled.
- New slot must belong to the same lawyer, be available, and be in the future.
- New slot is booked and old slot is released atomically.

Cancellation rules:
- Only active appointments can be cancelled.
- Cancellation releases the booked slot.

Review rules:
- Only the customer who owns the appointment can review it.
- Appointment must be `completed`.
- One appointment can have at most one review.
- Rating is 1–5.

Authorization rules:
- Customer can only read/change their own appointments.
- Lawyer can only operate on appointments and slots belonging to their own lawyer profile.
- Super Admin can monitor/manage system-wide data.

Notification rules:
- New booking → lawyer + customer confirmation.
- Confirmation → customer.
- Cancellation → affected party.
- Reschedule → affected party.
- Completion/review → relevant users.
- Reminder command handles upcoming appointments.

## Data consistency
Do not delete completed/cancelled appointment history through normal admin CRUD. Business actions should change state instead of silently deleting records.
