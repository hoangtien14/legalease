<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User, Role, Location, Specialization, LawyerProfile, CustomerProfile, AvailabilitySlot, Appointment};
use App\Services\AppointmentService;

class LegalEaseBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_book_only_an_available_slot_owned_by_the_lawyer(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();

        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'location' => null,
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
            'customer_note' => null,
        ]);

        $this->assertSame('pending', $appointment->status);
        $this->assertDatabaseHas('availability_slots', ['id' => $slot->id, 'status' => 'booked']);
    }

    public function test_customer_cannot_book_the_same_time_twice(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $service = app(AppointmentService::class);
        $payload = [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
        ];

        $service->book($customer, $lawyer, $payload);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->book($customer, $lawyer, $payload);
    }

    public function test_completed_appointment_cannot_be_cancelled_again(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
        ]);
        $service = app(AppointmentService::class);
        $appointment = $service->transition($appointment, 'confirmed');
        $appointment->update(['appointment_at' => now()->subMinute()]);
        $appointment = $service->transition($appointment->fresh(), 'completed');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $service->transition($appointment->fresh(), 'cancelled');
    }

    public function test_future_appointment_cannot_be_marked_completed(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
        ]);
        $appointment = app(AppointmentService::class)->transition($appointment, 'confirmed');

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AppointmentService::class)->transition($appointment, 'completed');
    }

    public function test_cancelled_appointment_releases_its_slot(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $appointment = app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
        ]);
        app(AppointmentService::class)->transition($appointment, 'cancelled');
        $this->assertDatabaseHas('availability_slots', ['id' => $slot->id, 'status' => 'available']);
    }

    public function test_verified_status_and_active_account_are_required_for_booking(): void
    {
        [$customer, $lawyer, $specialization, $slot] = $this->makeScenario();
        $lawyer->update(['verification_status' => 'pending']);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(AppointmentService::class)->book($customer, $lawyer, [
            'availability_slot_id' => $slot->id,
            'specialization_id' => $specialization->id,
            'consultation_type' => 'online',
            'problem_description' => 'Tôi cần tư vấn về một hợp đồng dân sự.',
        ]);
    }

    private function makeScenario(): array
    {
        $roleLawyer = Role::create(['name' => 'lawyer']);
        $roleCustomer = Role::create(['name' => 'customer']);
        $location = Location::create(['province' => 'Hà Nội', 'district' => 'Cầu Giấy']);
        $specialization = Specialization::create(['name' => 'Luật Dân sự', 'slug' => 'luat-dan-su', 'is_active' => true]);
        $lawyerUser = User::create(['role_id' => $roleLawyer->id, 'name' => 'Lawyer', 'email' => 'lawyer@test.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $customerUser = User::create(['role_id' => $roleCustomer->id, 'name' => 'Customer', 'email' => 'customer@test.local', 'password' => bcrypt('password'), 'is_active' => true]);
        $lawyer = LawyerProfile::create(['user_id' => $lawyerUser->id, 'location_id' => $location->id, 'license_number' => 'LS-TEST-01', 'verification_status' => 'verified']);
        $lawyer->specializations()->attach($specialization->id);
        $customer = CustomerProfile::create(['user_id' => $customerUser->id]);
        $slot = AvailabilitySlot::create(['lawyer_profile_id' => $lawyer->id, 'available_date' => now()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'status' => 'available']);
        return [$customer, $lawyer, $specialization, $slot];
    }
}
