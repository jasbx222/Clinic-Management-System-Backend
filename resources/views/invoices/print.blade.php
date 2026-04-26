<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فاتورة #{{ $invoice->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 40px; background: #fff; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); border-radius: 10px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #7c3aed; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: 800; color: #7c3aed; }
        .invoice-details { margin-bottom: 40px; }
        .invoice-details h2 { color: #7c3aed; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8fafc; border-bottom: 2px solid #7c3aed; padding: 12px; text-align: right; font-weight: bold; }
        td { border-bottom: 1px solid #eee; padding: 12px; }
        .totals { float: left; width: 250px; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .total-row.grand-total { border-top: 2px solid #7c3aed; margin-top: 10px; padding-top: 10px; font-weight: 800; font-size: 1.2rem; color: #7c3aed; }
        .footer { margin-top: 50px; text-align: center; color: #64748b; font-size: 0.9rem; border-top: 1px solid #eee; padding-top: 20px; }
        @media print {
            body { padding: 0; }
            .invoice-box { border: none; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #7c3aed; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold;">طباعة الفاتورة</button>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="logo">ديفاميد.</div>
            <div style="text-align: left;">
                <p style="margin: 0;">التاريخ: {{ $invoice->created_at->format('Y-m-d') }}</p>
                <p style="margin: 0;">رقم الفاتورة: #{{ $invoice->id }}</p>
            </div>
        </div>

        <div class="invoice-details">
            <h2>بيانات المريضة</h2>
            <p><strong>الاسم:</strong> {{ $invoice->patient->user->name }}</p>
            <p><strong>الهاتف:</strong> {{ $invoice->patient->user->phone ?? '---' }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>الوصف</th>
                    <th>السعر</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>خدمة طبية - {{ $invoice->appointment->service->name ?? 'كشف طبي' }}</td>
                    <td>{{ number_format($invoice->subtotal, 2) }} ريال</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <div class="total-row">
                <span>المجموع الفرعي:</span>
                <span>{{ number_format($invoice->subtotal, 2) }} ريال</span>
            </div>
            @if($invoice->tax > 0)
            <div class="total-row">
                <span>الضريبة:</span>
                <span>{{ number_format($invoice->tax, 2) }} ريال</span>
            </div>
            @endif
            @if($invoice->discount > 0)
            <div class="total-row">
                <span>الخصم:</span>
                <span>-{{ number_format($invoice->discount, 2) }} ريال</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>الإجمالي:</span>
                <span>{{ number_format($invoice->total, 2) }} ريال</span>
            </div>
        </div>
        
        <div style="clear: both;"></div>

        <div class="footer">
            <p>شكراً لزيارتكم عيادات ديفاميد</p>
            <p>هذه الفاتورة تم إنشاؤها آلياً ولا تتطلب توقيع</p>
        </div>
    </div>
</body>
</html>
