<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روشتة {{ $prescription['number'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1d4ed8;
            --border: #cbd5e1;
            --muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Cairo', sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            line-height: 1.6;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 12px 20px;
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: center;
        }

        .toolbar button, .toolbar a {
            border: none;
            border-radius: 8px;
            padding: 10px 16px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-primary { background: var(--primary); color: white; }
        .btn-secondary { background: #e2e8f0; color: #0f172a; }

        .page-wrap {
            display: flex;
            justify-content: center;
            padding: 24px;
        }

        .rx-paper {
            background: white;
            width: 210mm;
            min-height: 297mm;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 18mm 16mm 14mm;
            position: relative;
        }

        .rx-paper.half-a4 {
            width: 210mm;
            min-height: 148mm;
            padding: 12mm 14mm 10mm;
        }

        .rx-header {
            display: grid;
            grid-template-columns: 90px 1fr 90px;
            gap: 12px;
            align-items: center;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 12px;
            margin-bottom: 14px;
        }

        .rx-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .rx-logo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            border: 1px dashed var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 12px;
        }

        .clinic-title {
            text-align: center;
        }

        .clinic-title h1 {
            font-size: 22px;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .clinic-title p {
            font-size: 13px;
            color: var(--muted);
        }

        .rx-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 14px;
            font-size: 13px;
        }

        .meta-box {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px 12px;
        }

        .meta-box strong { color: var(--primary); }

        .doctor-box {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .doctor-box h2 { font-size: 16px; margin-bottom: 4px; }
        .doctor-box p { font-size: 13px; color: var(--muted); }

        .stamp-signature {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .stamp-signature img {
            max-height: 60px;
            max-width: 90px;
            object-fit: contain;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            margin: 14px 0 8px;
            border-right: 4px solid var(--primary);
            padding-right: 8px;
        }

        .diagnosis {
            border: 1px dashed var(--border);
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 14px;
        }

        table.meds {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin-bottom: 12px;
        }

        table.meds th, table.meds td {
            border: 1px solid var(--border);
            padding: 8px 10px;
            text-align: right;
        }

        table.meds th {
            background: #eff6ff;
            color: var(--primary);
        }

        .notes {
            border-radius: 8px;
            background: #f8fafc;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .rx-footer {
            margin-top: 20px;
            border-top: 1px solid var(--border);
            padding-top: 12px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .company-footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed var(--border);
            text-align: center;
            font-size: 11px;
            color: var(--muted);
        }

        .qr-box {
            width: 72px;
            height: 72px;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: var(--muted);
            text-align: center;
            padding: 4px;
        }

        @media print {
            body { background: white; }
            .toolbar { display: none !important; }
            .page-wrap { padding: 0; }
            .rx-paper {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                margin: 0;
            }
            .rx-paper.half-a4 {
                width: 100%;
                min-height: auto;
                page-break-after: avoid;
            }
            @page {
                size: {{ $paper === 'half_a4' ? 'A5 landscape' : 'A4' }};
                margin: 8mm;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button class="btn-primary" onclick="window.print()">طباعة</button>
        <a class="btn-secondary" href="{{ route('doctor.prescriptions.show', $prescription['id']) }}">رجوع</a>
        <a class="btn-secondary" href="{{ route('doctor.prescriptions.print', ['id' => $prescription['id'], 'paper' => 'a4']) }}">A4</a>
        <a class="btn-secondary" href="{{ route('doctor.prescriptions.print', ['id' => $prescription['id'], 'paper' => 'half_a4']) }}">نصف A4</a>
    </div>

    <div class="page-wrap">
        <article class="rx-paper {{ $paper === 'half_a4' ? 'half-a4' : '' }}">
            <header class="rx-header">
                <div>
                    @if($prescription['doctor']['logo_url'])
                        <img src="{{ $prescription['doctor']['logo_url'] }}" alt="شعار العيادة" class="rx-logo">
                    @else
                        <div class="rx-logo-placeholder">شعار</div>
                    @endif
                </div>
                <div class="clinic-title">
                    <h1>{{ $prescription['clinic']['name'] }}</h1>
                    @if($prescription['clinic']['branch_name'])
                        <p>{{ $prescription['clinic']['branch_name'] }}</p>
                    @endif
                    <p>{{ $prescription['clinic']['governorate'] }} — {{ $prescription['clinic']['address'] }}</p>
                    @if($prescription['clinic']['phone'])
                        <p>هاتف: {{ $prescription['clinic']['phone'] }}</p>
                    @endif
                </div>
                <div class="qr-box">
                    QR<br>{{ $prescription['number'] }}
                </div>
            </header>

            <section class="rx-meta">
                <div class="meta-box">
                    <div><strong>المريض:</strong> {{ $prescription['patient']['name'] }}</div>
                    <div><strong>الهاتف:</strong> {{ $prescription['patient']['phone'] }}</div>
                </div>
                <div class="meta-box">
                    <div><strong>التاريخ:</strong> {{ $prescription['created_at']?->timezone(config('clinic.timezone'))->format('Y-m-d h:i A') }}</div>
                    <div><strong>رقم الروشتة:</strong> {{ $prescription['number'] }}</div>
                    @if($prescription['patient']['age'])
                        <div><strong>العمر:</strong> {{ $prescription['patient']['age'] }} سنة</div>
                    @endif
                </div>
            </section>

            <section class="doctor-box">
                <div>
                    <h2>د. {{ $prescription['doctor']['name'] }}</h2>
                    <p>{{ $prescription['doctor']['speciality'] }}</p>
                    @if($prescription['doctor']['syndicate_number'])
                        <p>رقم النقابة: {{ $prescription['doctor']['syndicate_number'] }}</p>
                    @endif
                    @if($prescription['doctor']['experience_years'])
                        <p>خبرة: {{ $prescription['doctor']['experience_years'] }} سنة</p>
                    @endif
                </div>
                <div class="stamp-signature">
                    @if($prescription['doctor']['stamp_url'])
                        <img src="{{ $prescription['doctor']['stamp_url'] }}" alt="ختم">
                    @endif
                    @if($prescription['doctor']['signature_url'])
                        <img src="{{ $prescription['doctor']['signature_url'] }}" alt="توقيع">
                    @endif
                </div>
            </section>

            @if($prescription['diagnosis'])
                <h3 class="section-title">التشخيص</h3>
                <div class="diagnosis">{{ $prescription['diagnosis'] }}</div>
            @endif

            <h3 class="section-title">الأدوية</h3>
            <table class="meds">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الدواء</th>
                        <th>الجرعة</th>
                        <th>التكرار</th>
                        <th>المدة</th>
                        <th>تعليمات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prescription['medicines'] as $index => $medicine)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $medicine['name'] }}</td>
                            <td>{{ $medicine['dosage'] ?: '—' }}</td>
                            <td>{{ $medicine['frequency'] ?: '—' }}</td>
                            <td>{{ $medicine['duration'] ?: '—' }}</td>
                            <td>{{ $medicine['instructions'] ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">لا توجد أدوية</td></tr>
                    @endforelse
                </tbody>
            </table>

            @if($prescription['notes'])
                <h3 class="section-title">ملاحظات</h3>
                <div class="notes">{{ $prescription['notes'] }}</div>
            @endif

            <footer class="rx-footer">
                <div>
                    <p style="font-size:13px;color:var(--muted);">توقيع الطبيب</p>
                    <p style="margin-top:28px;border-top:1px solid var(--border);width:180px;text-align:center;padding-top:4px;">د. {{ $prescription['doctor']['name'] }}</p>
                </div>
                <div style="font-size:12px;color:var(--muted);text-align:left;">
                    {{ config('clinic.currency_symbol') }} — {{ config('clinic.country_name_ar') }}
                </div>
            </footer>

            <div class="company-footer">
                نظام {{ $company['name_ar'] ?? 'شلال تك' }} — {{ $company['website'] ?? '' }}
                @if(!empty($company['phone'])) | هاتف: {{ $company['phone'] }} @endif
                @if(!empty($company['email'])) | {{ $company['email'] }} @endif
            </div>
        </article>
    </div>
</body>
</html>
