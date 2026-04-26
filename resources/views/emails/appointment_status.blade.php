<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>حالة الموعد</title>
    <style>
        body { font-family: 'Tahoma', sans-serif; background: #F9FAFB; padding: 20px; text-align: right; color: #111827; }
        .container { background: #FFFFFF; max-width: 600px; margin: 0 auto; border-radius: 8px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #F3F4F6; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #1E1E2F; margin: 0; }
        .header h1 span { color: #F8AFC2; }
        .content { font-size: 16px; line-height: 1.6; }
        .details { background: #F9FAFB; padding: 15px; border-radius: 6px; margin: 20px 0; border: 1px solid #E5E7EB; }
        .footer { text-align: center; font-size: 14px; color: #6B7280; margin-top: 30px; border-top: 1px solid #F3F4F6; padding-top: 20px; }
        .status-confirmed { color: #10B981; font-weight: bold; }
        .status-cancelled { color: #EF4444; font-weight: bold; }
        .status-completed { color: #3B82F6; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ديفاميد<span>.</span></h1>
            <p>عيادات ديفاميد الطبية</p>
        </div>
        
        <div class="content">
            <p>مرحباً <strong>{{ $appointment->patient->user->name }}</strong>،</p>
            
            <p>نود إعلامك بأنه تم تحديث حالة موعدك لدينا لتصبح: 
                @if($status === 'confirmed') <span class="status-confirmed">مؤكد</span>
                @elseif($status === 'cancelled') <span class="status-cancelled">ملغي</span>
                @elseif($status === 'completed') <span class="status-completed">مكتمل</span>
                @else <strong>{{ $status }}</strong>
                @endif
            </p>

            <div class="details">
                <p><strong>الدكتور:</strong> {{ $appointment->doctor->name }}</p>
                <p><strong>تاريخ الموعد:</strong> {{ $appointment->appointment_date }}</p>
                <p><strong>وقت الموعد:</strong> {{ $appointment->appointment_time }}</p>
            </div>

            @if($status === 'confirmed')
                <p>نرجو منك الحضور قبل الموعد بـ 15 دقيقة. نتمنى لك دوام الصحة والعافية.</p>
            @elseif($status === 'cancelled')
                <p>نعتذر عن إلغاء الموعد. يمكنك حجز موعد آخر عبر التطبيق أو بالاتصال بنا.</p>
            @elseif($status === 'completed')
                <p>شكراً لزيارتك عيادات ديفاميد. نتمنى أن نكون قد قدمنا لك الخدمة التي ترضيك.</p>
            @endif
        </div>

        <div class="footer">
            <p>هذه رسالة تلقائية، الرجاء عدم الرد عليها.</p>
            <p>&copy; {{ date('Y') }} عيادات ديفاميد. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
