<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        ClinicSetting::updateOrCreate(
            ['email' => 'info@divamed.com'],
            [
                'name' => 'ديفاميد الطبية',
                'description' => 'رعاية صحية حديثة وموثوقة',
                'phone' => '0501234567',
                'whatsapp' => '0501234567',
                'address' => 'الرياض، المملكة العربية السعودية',
                'working_hours' => 'السبت - الخميس, 9:00 ص - 9:00 م',
                'hero_title' => 'رعاية صحية بكل راحة واهتمام',
                'hero_subtitle' => 'احجز موعدك بسهولة مع نخبة من أفضل الأطباء والطبيبات في المملكة.',
            ]
        );

        $services = [
            ['name' => 'كشف عام', 'price' => 150, 'duration_minutes' => 30],
            ['name' => 'استشارة جلدية', 'price' => 300, 'duration_minutes' => 45],
            ['name' => 'متابعة حمل', 'price' => 250, 'duration_minutes' => 30],
            ['name' => 'كشف أطفال', 'price' => 180, 'duration_minutes' => 30],
            ['name' => 'استشارة أسنان', 'price' => 220, 'duration_minutes' => 45],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['name' => $service['name']],
                $service
            );
        }

        $password = Hash::make('password');

        $admins = [
            ['name' => 'أحمد علي', 'email' => 'admin@gmail.com', 'phone' => '0500000001'],
            ['name' => 'محمد خالد', 'email' => 'admin2@gmail.com', 'phone' => '0500000002'],
            ['name' => 'سارة أحمد', 'email' => 'admin3@gmail.com', 'phone' => '0500000003'],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'phone' => $admin['phone'],
                    'password' => $password,
                    'role' => 'admin',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $staff = [
            ['name' => 'نور الهدى', 'email' => 'receptionist@gmail.com', 'phone' => '0500000004', 'role' => 'receptionist'],
            ['name' => 'مريم حسن', 'email' => 'nurse@gmail.com', 'phone' => '0500000005', 'role' => 'nurse'],
            ['name' => 'علي جاسم', 'email' => 'accountant@gmail.com', 'phone' => '0500000006', 'role' => 'accountant'],
            ['name' => 'حسين كريم', 'email' => 'patient@gmail.com', 'phone' => '0500000007', 'role' => 'patient'],
        ];

        foreach ($staff as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'phone' => $user['phone'],
                    'password' => $password,
                    'role' => $user['role'],
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $doctors = [
            ['name' => 'د. أمل حسن', 'email' => 'doctor1@gmail.com', 'phone' => '0500000011', 'specialization' => 'جلدية وتجميل'],
            ['name' => 'د. سارة محمد', 'email' => 'doctor2@gmail.com', 'phone' => '0500000012', 'specialization' => 'نساء وولادة'],
            ['name' => 'د. خالد عمر', 'email' => 'doctor3@gmail.com', 'phone' => '0500000013', 'specialization' => 'طب عام'],
            ['name' => 'د. ليلى أحمد', 'email' => 'doctor4@gmail.com', 'phone' => '0500000014', 'specialization' => 'أطفال'],
            ['name' => 'د. مازن علي', 'email' => 'doctor5@gmail.com', 'phone' => '0500000015', 'specialization' => 'أسنان'],
            ['name' => 'د. ندى كريم', 'email' => 'doctor6@gmail.com', 'phone' => '0500000016', 'specialization' => 'قلب'],
            ['name' => 'د. عمر ياسين', 'email' => 'doctor7@gmail.com', 'phone' => '0500000017', 'specialization' => 'عظام'],
            ['name' => 'د. زينب حسين', 'email' => 'doctor8@gmail.com', 'phone' => '0500000018', 'specialization' => 'عيون'],
            ['name' => 'د. مصطفى سالم', 'email' => 'doctor9@gmail.com', 'phone' => '0500000019', 'specialization' => 'أنف وأذن وحنجرة'],
            ['name' => 'د. ريم عبد الله', 'email' => 'doctor10@gmail.com', 'phone' => '0500000020', 'specialization' => 'تغذية علاجية'],
            ['name' => 'د. يوسف فاضل', 'email' => 'doctor11@gmail.com', 'phone' => '0500000021', 'specialization' => 'مسالك بولية'],
            ['name' => 'د. هند ناصر', 'email' => 'doctor12@gmail.com', 'phone' => '0500000022', 'specialization' => 'باطنية'],
        ];

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        foreach ($doctors as $index => $doctorData) {
            $doctor = User::updateOrCreate(
                ['email' => $doctorData['email']],
                [
                    'name' => $doctorData['name'],
                    'phone' => $doctorData['phone'],
                    'password' => $password,
                    'role' => 'doctor',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            DoctorProfile::updateOrCreate(
                ['user_id' => $doctor->id],
                ['specialization' => $doctorData['specialization']]
            );

            foreach ($days as $day) {
                DoctorSchedule::updateOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => $index % 2 === 0 ? '09:00:00' : '10:00:00',
                        'end_time' => $index % 2 === 0 ? '17:00:00' : '18:00:00',
                    ]
                );
            }
        }
    }
}