<?php

namespace Tests\Feature;

use App\Models\{Appointment, AvailabilitySlot, CustomerProfile, LawyerProfile, Location, Role, Specialization, User};
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LegalEaseAccessAndLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_is_denied_from_authenticated_area(): void
    {
        $user = $this->makeUser('customer', false);
        $this->actingAs($user)->get('/profile')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_book_a_slot_belonging_to_another_lawyer(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $otherLawyer = $this->makeLawyer('other@test.local', 'LS-OTHER');

        $this->expectException(ValidationException::class);
        app(AppointmentService::class)->book($customer, $otherLawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về hợp đồng dân sự.',
        ]);
    }

    public function test_offline_booking_requires_a_location(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $this->expectException(ValidationException::class);
        app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'offline',
            'problem_description' => 'Tôi cần tư vấn về hợp đồng dân sự.',
        ]);
    }

    public function test_customer_cannot_review_an_uncompleted_appointment(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về hợp đồng dân sự.',
        ]);

        $this->actingAs($customer->user)->post(route('customer.appointments.review', $appointment), [
            'rating' => 5,
            'comment' => 'Tốt',
        ])->assertSessionHasErrors('review');
    }

    public function test_reschedule_keeps_the_same_lawyer_and_releases_the_old_slot(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $newSlot = AvailabilitySlot::create([
            'lawyer_profile_id' => $lawyer->id,
            'available_date' => now()->addDays(2)->toDateString(),
            'start_time' => '11:00',
            'end_time' => '12:00',
            'status' => 'available',
        ]);
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về hợp đồng dân sự.',
        ]);

        $appointment = app(AppointmentService::class)->reschedule($appointment, $newSlot, 'customer');
        $this->assertSame('rescheduled', $appointment->status);
        $this->assertSame($lawyer->id, $appointment->lawyer_profile_id);
        $this->assertDatabaseHas('availability_slots', ['id' => $slot->id, 'status' => 'available']);
        $this->assertDatabaseHas('availability_slots', ['id' => $newSlot->id, 'status' => 'booked']);
    }

    public function test_pending_appointment_can_be_cancelled_but_completed_cannot(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về hợp đồng dân sự.',
        ]);
        app(AppointmentService::class)->transition($appointment, 'cancelled');
        $this->assertDatabaseHas('appointments', ['id' => $appointment->id, 'status' => 'cancelled']);

        $this->expectException(ValidationException::class);
        app(AppointmentService::class)->transition($appointment->fresh(), 'confirmed');
    }

    private function makeScenario(): array
    {
        $lawyerRole = Role::firstOrCreate(['name' => 'lawyer']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);
        $location = Location::create(['province' => 'Hà Nội', 'district' => 'Cầu Giấy', 'is_active' => true]);
        $specialization = Specialization::create(['name' => 'Luật Dân sự', 'slug' => 'luat-dan-su-' . uniqid(), 'is_active' => true]);
        $lawyerUser = User::create(['role_id' => $lawyerRole->id, 'name' => 'Lawyer', 'email' => uniqid() . '@test.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $customerUser = User::create(['role_id' => $customerRole->id, 'name' => 'Customer', 'email' => uniqid() . '@test.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $lawyer = LawyerProfile::create(['user_id' => $lawyerUser->id, 'location_id' => $location->id, 'license_number' => 'LS-' . uniqid(), 'verification_status' => 'verified']);
        $lawyer->specializations()->attach($specialization->id);
        $customer = CustomerProfile::create(['user_id' => $customerUser->id]);
        $slot = AvailabilitySlot::create(['lawyer_profile_id' => $lawyer->id, 'available_date' => now()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'status' => 'available']);
        return [$customer, $lawyer, $specialization, $slot];
    }

    private function makeUser(string $role, bool $active): User
    {
        $roleModel = Role::firstOrCreate(['name' => $role]);
        return User::create(['role_id' => $roleModel->id, 'name' => ucfirst($role), 'email' => uniqid() . '@test.local', 'password' => bcrypt('password'), 'is_active' => $active]);
    }

    private function makeLawyer(string $email, string $license): LawyerProfile
    {
        $role = Role::firstOrCreate(['name' => 'lawyer']);
        $location = Location::firstOrCreate(['province' => 'Hà Nội', 'district' => 'Ba Đình'], ['is_active' => true]);
        $user = User::create(['role_id' => $role->id, 'name' => 'Other Lawyer', 'email' => $email, 'password' => bcrypt('password'), 'is_active' => true]);
        return LawyerProfile::create(['user_id' => $user->id, 'location_id' => $location->id, 'license_number' => $license, 'verification_status' => 'verified']);
    }
}
