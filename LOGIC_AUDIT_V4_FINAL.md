# LegalEase v4 – Final Logic Audit

## Scope
This audit hardens the v4 application before final testing and EProject documentation. The rules are enforced at controller/service/database boundaries rather than only in Blade UI.

## 1. Authentication & accounts
- Login accepts only active users with one of `customer`, `lawyer`, `super_admin` roles.
- Login is throttled to reduce repeated credential attacks.
- Active-user middleware protects authenticated profile/notification/dashboard routes.
- Password changes require the current password and a different new password.
- Customer registration can only create a customer role.
- Lawyer registration creates a pending verification profile.
- Email and lawyer license numbers are unique.

## 2. Public lawyer visibility
A lawyer is publicly searchable/bookable only when:
- the user account is active;
- the lawyer verification status is `verified`;
- the assigned location exists and is active.

Search supports specialization, location/province, minimum experience, keyword, sorting and pagination. Public lawyer pagination is 12 records per page and query parameters are preserved.

## 3. Availability rules
- Start time must be before end time.
- A slot cannot start in the past.
- A slot cannot overlap another slot for the same lawyer/date, including blocked slots.
- Slot creation/update is serialized on the lawyer row to reduce concurrent overlap races.
- Booked slots cannot be edited, blocked or deleted.
- Pending/unverified/inactive lawyers cannot manage availability.

## 4. Booking rules
Booking is executed inside a transaction and locks the customer, lawyer and selected slot as appropriate.

Required conditions:
- active customer account;
- active lawyer account;
- verified lawyer;
- active lawyer location;
- slot belongs to the selected lawyer;
- slot is available;
- slot is not in the past;
- selected specialization belongs to the lawyer and is active;
- customer has no other active appointment at the same exact appointment time;
- offline consultation must provide a location; online consultation clears the location.

Only after the appointment is created is the slot changed to `booked`. A failure rolls back the transaction; uploaded attachments are removed if booking fails.

## 5. Appointment state machine
Allowed transitions:
- `pending` → `confirmed`, `cancelled`, `rescheduled`
- `confirmed` → `completed`, `cancelled`, `rescheduled`
- `rescheduled` → `confirmed`, `completed`, `cancelled`, `rescheduled`
- `completed` → terminal
- `cancelled` → terminal

Additional rules:
- past appointments cannot be confirmed/rescheduled;
- future appointments cannot be completed;
- past appointments cannot be cancelled;
- cancelling a booked appointment releases its slot;
- completed appointments retain their slot/history and are never physically deleted through the business UI.

## 6. Rescheduling
- Only active appointment states can be rescheduled.
- The new slot must belong to the same lawyer.
- The new slot must be available and future.
- The new slot must differ from the current slot.
- The customer cannot already have another active appointment at the new time.
- Old slot becomes available; new slot becomes booked; appointment becomes `rescheduled`.
- Lawyer/customer ownership is checked before the service is called.

## 7. Reviews
- Only the appointment's customer can submit its review.
- Appointment must be `completed`.
- Each appointment can have at most one review.
- Rating is restricted to 1–5.
- Review creation is transactional and locks the appointment to reduce duplicate concurrent submissions.
- Public lawyer ratings count only visible reviews.

## 8. Notifications
- Notifications are scoped to their owning user.
- Read/read-all actions cannot access another user's notifications.
- Booking, confirmation/cancellation/rescheduling and review events create relevant notifications.
- Reminder command supports customer and lawyer reminders and prevents duplicate reminders within the appointment window.

## 9. Super Admin protection
- Self-delete/self-disable is blocked.
- The last Super Admin cannot be deleted, disabled or demoted.
- Users with appointment history cannot be physically deleted; they must be disabled to preserve history.
- Lawyers with appointment history cannot be physically deleted.
- Lawyer verification requires an active location and at least one active specialization.

## 10. Master data integrity
- Locations have an active/inactive flag.
- In-use locations cannot be physically deleted.
- In-use specializations cannot be physically deleted.
- Inactive locations/specializations are excluded from new public selection flows.
- Draft news is not public.
- Published news receives a publication time and future publication times are not exposed as already-published content.

## 11. Tests added
The test suite now covers:
- available-slot booking;
- wrong-lawyer slot rejection;
- duplicate same-time booking rejection;
- inactive account rejection;
- verified/active lawyer requirement;
- offline-location requirement;
- appointment transition rules;
- future-completion rejection;
- cancellation slot release;
- reschedule ownership/time/slot behavior;
- review eligibility;
- successful root-page response with `RefreshDatabase`.

## Environment note
Static PHP linting succeeds for the application source, routes, migrations, seeders and tests. Full PHPUnit/Artisan integration execution in this packaging environment is blocked by missing PHP extensions (`dom`, `mbstring`, `xmlwriter`, and database PDO drivers). The intended local XAMPP environment should run the final integration commands.
