<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Location;
use App\Models\Specialization;
use App\Models\LawyerProfile;
use App\Models\LawyerSpecialization;
use App\Models\CustomerProfile;
use App\Models\AvailabilitySlot;
use App\Models\Faq;
use App\Models\News;

class LegalEaseSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ROLES
        | Quyền người dùng
        |--------------------------------------------------------------------------
        */

        $adminRole = Role::firstOrCreate([
            'name' => 'super_admin',
        ]);

        $lawyerRole = Role::firstOrCreate([
            'name' => 'lawyer',
        ]);

        $customerRole = Role::firstOrCreate([
            'name' => 'customer',
        ]);


        /*
        |--------------------------------------------------------------------------
        | LOCATIONS
        | Địa điểm
        |--------------------------------------------------------------------------
        */

     $hanoi = Location::create([
    'province' => 'Hà Nội',
    'district' => 'Cầu Giấy',
    'address' => 'Hà Nội',
    'description' => 'Khu vực Hà Nội',
]);

$hcm = Location::create([
    'province' => 'Thành phố Hồ Chí Minh',
    'district' => 'Quận 1',
    'address' => 'Thành phố Hồ Chí Minh',
    'description' => 'Khu vực Thành phố Hồ Chí Minh',
]);

$danang = Location::create([
    'province' => 'Đà Nẵng',
    'district' => 'Hải Châu',
    'address' => 'Đà Nẵng',
    'description' => 'Khu vực Đà Nẵng',
]);

        /*
        |--------------------------------------------------------------------------
        | SPECIALIZATIONS
        | Lĩnh vực pháp luật
        |--------------------------------------------------------------------------
        */

        $civil = Specialization::create([
            'name' => 'Luật dân sự',
            'slug' => 'luat-dan-su',
            'description' => 'Tư vấn các vấn đề pháp luật dân sự.',
            'is_active' => true,
        ]);

        $business = Specialization::create([
            'name' => 'Luật doanh nghiệp',
            'slug' => 'luat-doanh-nghiep',
            'description' => 'Tư vấn thành lập và hoạt động doanh nghiệp.',
            'is_active' => true,
        ]);

        $family = Specialization::create([
            'name' => 'Luật hôn nhân và gia đình',
            'slug' => 'luat-hon-nhan-gia-dinh',
            'description' => 'Tư vấn các vấn đề hôn nhân và gia đình.',
            'is_active' => true,
        ]);

        $labor = Specialization::create([
            'name' => 'Luật lao động',
            'slug' => 'luat-lao-dong',
            'description' => 'Tư vấn quan hệ lao động.',
            'is_active' => true,
        ]);

        $criminal = Specialization::create([
            'name' => 'Luật hình sự',
            'slug' => 'luat-hinh-su',
            'description' => 'Tư vấn các vấn đề pháp luật hình sự.',
            'is_active' => true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | LAWYER 1
        | Luật sư 1
        |--------------------------------------------------------------------------
        */

        $user1 = User::create([
            'name' => 'Nguyễn Minh Anh',
            'email' => 'lawyer1@legalease.test',
            'password' => Hash::make('12345678'),
            'phone' => '0901000001',
            'role_id' => $lawyerRole->id,
            'is_active' => true,
        ]);

        $lawyer1 = LawyerProfile::create([
            'user_id' => $user1->id,
            'location_id' => $hanoi->id,
            'license_number' => 'LS-HN-0001',
            'title' => 'Luật sư tư vấn dân sự',
            'bio' => 'Luật sư có nhiều năm kinh nghiệm tư vấn pháp luật dân sự.',
            'education' => 'Đại học Luật Hà Nội',
            'experience' => 8,
            'consultation_fee' => 500000,
            'verification_status' => 'verified',
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer1->id,
            'specialization_id' => $civil->id,
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer1->id,
            'specialization_id' => $family->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | LAWYER 2
        |--------------------------------------------------------------------------
        */

        $user2 = User::create([
            'name' => 'Trần Quốc Huy',
            'email' => 'lawyer2@legalease.test',
            'password' => Hash::make('12345678'),
            'phone' => '0901000002',
            'role_id' => $lawyerRole->id,
            'is_active' => true,
        ]);

        $lawyer2 = LawyerProfile::create([
            'user_id' => $user2->id,
            'location_id' => $hcm->id,
            'license_number' => 'LS-HCM-0002',
            'title' => 'Luật sư doanh nghiệp',
            'bio' => 'Chuyên tư vấn pháp lý cho doanh nghiệp và hợp đồng thương mại.',
            'education' => 'Đại học Luật Thành phố Hồ Chí Minh',
            'experience' => 10,
            'consultation_fee' => 700000,
            'verification_status' => 'verified',
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer2->id,
            'specialization_id' => $business->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | LAWYER 3
        |--------------------------------------------------------------------------
        */

        $user3 = User::create([
            'name' => 'Lê Thu Hà',
            'email' => 'lawyer3@legalease.test',
            'password' => Hash::make('12345678'),
            'phone' => '0901000003',
            'role_id' => $lawyerRole->id,
            'is_active' => true,
        ]);

        $lawyer3 = LawyerProfile::create([
            'user_id' => $user3->id,
            'location_id' => $danang->id,
            'license_number' => 'LS-DN-0003',
            'title' => 'Luật sư hôn nhân gia đình',
            'bio' => 'Tư vấn và hỗ trợ giải quyết các vấn đề hôn nhân và gia đình.',
            'education' => 'Đại học Luật Hà Nội',
            'experience' => 7,
            'consultation_fee' => 450000,
            'verification_status' => 'verified',
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer3->id,
            'specialization_id' => $family->id,
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer3->id,
            'specialization_id' => $civil->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | LAWYER 4
        |--------------------------------------------------------------------------
        */

        $user4 = User::create([
            'name' => 'Phạm Văn Nam',
            'email' => 'lawyer4@legalease.test',
            'password' => Hash::make('12345678'),
            'phone' => '0901000004',
            'role_id' => $lawyerRole->id,
            'is_active' => true,
        ]);

        $lawyer4 = LawyerProfile::create([
            'user_id' => $user4->id,
            'location_id' => $hanoi->id,
            'license_number' => 'LS-HN-0004',
            'title' => 'Luật sư lao động',
            'bio' => 'Chuyên tư vấn hợp đồng lao động và tranh chấp lao động.',
            'education' => 'Đại học Luật Hà Nội',
            'experience' => 6,
            'consultation_fee' => 400000,
            'verification_status' => 'verified',
        ]);

        LawyerSpecialization::create([
            'lawyer_profile_id' => $lawyer4->id,
            'specialization_id' => $labor->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        | Khách hàng
        |--------------------------------------------------------------------------
        */

        $customer = User::create([
            'name' => 'Nguyễn Văn Minh',
            'email' => 'customer@legalease.test',
            'password' => Hash::make('12345678'),
            'phone' => '0902000001',
            'role_id' => $customerRole->id,
            'is_active' => true,
        ]);

        CustomerProfile::create([
            'user_id' => $customer->id,
            'location_id' => $hanoi->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | AVAILABILITY
        | Lịch làm việc mẫu
        |--------------------------------------------------------------------------
        */

        foreach ([$lawyer1, $lawyer2, $lawyer3, $lawyer4] as $lawyer) {

            AvailabilitySlot::create([
                'lawyer_profile_id' => $lawyer->id,
                'available_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '10:00:00',
                'status' => 'available',
            ]);

            AvailabilitySlot::create([
                'lawyer_profile_id' => $lawyer->id,
                'available_date' => now()->addDay()->toDateString(),
                'start_time' => '14:00:00',
                'end_time' => '15:00:00',
                'status' => 'available',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FAQ
        | Câu hỏi thường gặp
        |--------------------------------------------------------------------------
        */

        Faq::create([
            'question' => 'LegalEase là gì?',
            'answer' => 'LegalEase là nền tảng kết nối khách hàng với luật sư để tư vấn pháp luật.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Làm thế nào để đặt lịch tư vấn?',
            'answer' => 'Bạn chọn luật sư, chọn thời gian phù hợp và gửi yêu cầu đặt lịch.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Faq::create([
            'question' => 'Tôi có thể tìm luật sư theo lĩnh vực không?',
            'answer' => 'Có. LegalEase hỗ trợ tìm kiếm luật sư theo lĩnh vực pháp luật và địa điểm.',
            'sort_order' => 3,
            'is_active' => true,
        ]);


        /*
        |--------------------------------------------------------------------------
        | NEWS
        | Tin tức pháp luật
        |--------------------------------------------------------------------------
        */

        News::create([
            'author_id' => $user1->id,
            'title' => 'Những điều cần biết khi ký hợp đồng',
            'slug' => 'nhung-dieu-can-biet-khi-ky-hop-dong',
            'thumbnail' => null,
            'excerpt' => 'Một số lưu ý quan trọng trước khi ký hợp đồng.',
            'content' => 'Khách hàng nên đọc kỹ các điều khoản, quyền và nghĩa vụ trước khi ký hợp đồng.',
            'status' => 'published',
            'published_at' => now(),
        ]);

        News::create([
            'author_id' => $user2->id,
            'title' => 'Quyền lợi cơ bản của người lao động',
            'slug' => 'quyen-loi-co-ban-cua-nguoi-lao-dong',
            'thumbnail' => null,
            'excerpt' => 'Tìm hiểu những quyền lợi cơ bản của người lao động.',
            'content' => 'Người lao động cần nắm rõ quyền và nghĩa vụ của mình trong quan hệ lao động.',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }
}