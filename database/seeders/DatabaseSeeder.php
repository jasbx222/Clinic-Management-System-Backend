<?php

namespace Database\Seeders;

use App\Models\ClinicSetting;
use App\Models\DoctorProfile;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        User::factory()->create([
            'name' => 'أحمد علي',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        ClinicSetting::create([
            'name' => 'ديفاميد الطبية',
            'description' => 'رعاية صحية حديثة وموثوقة',
            'phone' => '0501234567',
            'whatsapp' => '0501234567',
            'email' => 'info@divamed.com',
            'address' => 'الرياض، المملكة العربية السعودية',
            'working_hours' => 'السبت - الخميس, 9:00 ص - 9:00 م',
            'hero_title' => 'رعاية صحية بكل راحة واهتمام',
            'hero_subtitle' => 'احجز موعدك بسهولة مع نخبة من أفضل الأطباء والطبيبات في المملكة.',
        ]);

        $services = [
            ['name' => 'كشف عام', 'price' => 150, 'duration_minutes' => 30],
            ['name' => 'استشارة جلدية', 'price' => 300, 'duration_minutes' => 45],
            ['name' => 'متابعة حمل', 'price' => 250, 'duration_minutes' => 30],
        ];

        foreach ($services as $srv) {
            Service::create($srv);
        }

        $doctor1 = User::factory()->create(['name' => 'د. أمل حسن', 'role' => 'doctor', 'is_active' => true]);
        DoctorProfile::create(['user_id' => $doctor1->id, 'specialization' => 'جلدية وتجميل']);

        $doctor2 = User::factory()->create(['name' => 'د. سارة محمد', 'role' => 'doctor', 'is_active' => true]);
        DoctorProfile::create(['user_id' => $doctor2->id, 'specialization' => 'نساء وولادة']);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
        foreach ($days as $day) {
            DoctorSchedule::create([
                'doctor_id' => $doctor1->id,
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
            ]);
            DoctorSchedule::create([
                'doctor_id' => $doctor2->id,
                'day_of_week' => $day,
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
            ]);
        }
    }
}
