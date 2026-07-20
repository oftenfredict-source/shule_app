<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 14mm 12mm 18mm 12mm; }
        body {
            font-family: "century gothic", "Century Gothic", CenturyGothic, AppleGothic, Helvetica, sans-serif;
            font-size: 10.5px;
            color: #2c2c2c;
            line-height: 1.5;
            background: #fff;
            margin: 0;
            padding: 0 0 30px 0;
        }
        .watermark {
            position: fixed;
            top: 42%;
            left: 0;
            width: 100%;
            text-align: center;
            opacity: 0.045;
            transform: rotate(-45deg);
            font-size: 92px;
            font-weight: bold;
            color: #940000;
            z-index: -1;
            text-transform: uppercase;
        }
        .header {
            text-align: center;
            margin-bottom: 14px;
            border-bottom: 2.5px solid #940000;
            padding-bottom: 10px;
        }
        .brand { font-size: 18px; font-weight: bold; color: #940000; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 8px;
            background: #f8f9fa;
            padding: 8px 10px;
            border: 1.5px solid #ccc;
            color: #222;
        }
        .meta-line { font-size: 9px; color: #666; margin-top: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; table-layout: fixed; }
        .info-table td { padding: 7px 8px; border: 1px solid #ccc; vertical-align: middle; }
        .label { font-weight: bold; color: #444; background: #f8f9fa; width: 28%; }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #940000;
            border-bottom: 1.5px solid #940000;
            padding-bottom: 4px;
            margin-top: 16px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .sub-title {
            font-size: 11px;
            font-weight: bold;
            color: #5a0000;
            margin: 10px 0 6px;
        }
        .summary-box {
            background: #fdf2f2;
            border: 1px solid #f0dada;
            padding: 10px 12px;
            margin-bottom: 12px;
        }
        .note-box {
            background: #fff8e6;
            border: 1px solid #ffe0a3;
            padding: 9px 11px;
            margin: 8px 0 12px;
        }
        .ok-box {
            background: #f3faf5;
            border: 1px solid #c8e6d0;
            padding: 9px 11px;
            margin: 8px 0 12px;
        }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        table.data-table th, table.data-table td {
            border: 1px solid #ccc;
            padding: 6px 7px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }
        table.data-table th {
            background: #f0f0f0;
            color: #940000;
            font-weight: bold;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .api-badge {
            display: inline-block;
            background: #940000;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 7px;
            margin-right: 4px;
        }
        .owner-sx { color: #940000; font-weight: bold; }
        .owner-nbc { color: #0b5; font-weight: bold; }
        .mono {
            font-family: "Courier New", Courier, monospace;
            font-size: 9px;
            background: #f5f5f5;
            border: 1px solid #e5e5e5;
            padding: 8px 10px;
            white-space: pre-wrap;
            line-height: 1.35;
            margin: 6px 0 10px;
        }
        .flow {
            font-family: "Courier New", Courier, monospace;
            font-size: 9px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            white-space: pre-wrap;
            line-height: 1.4;
            margin-bottom: 10px;
        }
        ol, ul { margin: 4px 0 8px 18px; padding: 0; }
        li { margin-bottom: 4px; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 8.5px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 5px;
            background: #fff;
        }
        .page-break { page-break-before: always; }
        .sig-table { width: 100%; margin-top: 22px; border-collapse: collapse; page-break-inside: avoid; }
        .sig-table td { width: 50%; border: none; padding: 8px 12px; vertical-align: top; }
        .sig-line { border-top: 1.5px solid #940000; margin-top: 40px; padding-top: 4px; font-size: 9px; color: #940000; font-weight: bold; }
        p { margin: 0 0 7px 0; }
        .muted { color: #666; font-size: 9px; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>

    @php
        if (!function_exists('sxPdfLogoBase64Sw')) {
            function sxPdfLogoBase64Sw($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                foreach ([public_path($path), base_path('public/' . $path)] as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'png');
                            $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'jpeg' : ($ext === 'gif' ? 'gif' : 'png');
                            return 'data:image/' . $mime . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }
                return null;
            }
        }
        $logo = sxPdfLogoBase64Sw('images/shuleXpert.jpg') ?: sxPdfLogoBase64Sw('images/logo.png');
        $docDate = $generated_at ?? date('d M Y');
        $docRef = $document_ref ?? ('SX-NBC-PAY-' . date('Y'));
    @endphp

    <div class="header">
        @if($logo)
            <img src="{{ $logo }}" style="max-height: 58px; margin-bottom: 4px;">
        @endif
        <div class="brand">ShuleXpert</div>
        <div style="font-size: 10px; color: #666;">Jukwaa la Usimamizi wa Shule &mdash; Inaendeshwa na EmCa Techonology</div>
        <div class="doc-title">{{ $title }}</div>
        <div class="meta-line">Namba ya Hati: {{ $docRef }} &nbsp;|&nbsp; Toleo {{ $version ?? '1.0' }} &nbsp;|&nbsp; {{ $docDate }} &nbsp;|&nbsp; Siri — Kwa Matumizi ya Uunganishaji na NBC</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Imetayarishwa Kwa</td>
            <td>National Bank of Commerce (NBC) — Timu ya Uunganishaji wa Malipo</td>
            <td class="label">Imetayarishwa Na</td>
            <td>ShuleXpert / EmCa Techonology</td>
        </tr>
        <tr>
            <td class="label">Mada</td>
            <td colspan="3">Pendekezo la Modality ya Ukusanyaji wa Ada za Shule (Kulingana na Control Number)</td>
        </tr>
        <tr>
            <td class="label">Lugha</td>
            <td>Kiswahili (hati hii) / Kiingereza (PDF mwenzake)</td>
            <td class="label">Hali</td>
            <td>Pendekezo la Ushirikiano wa Usanifu wa API</td>
        </tr>
    </table>

    <div class="summary-box">
        <strong>Madhumuni.</strong>
        Hati hii inaeleza jinsi ShuleXpert inavyosimamia muundo wa ada na control number kwa sasa,
        na inapendekeza modality wazi ya kuunganishwa na NBC ili wazazi/walezi waweze kulipa ada
        kupitia njia za NBC (benki, mitandao ya simu, na mifumo mingine iliyounganishwa na NBC),
        huku fedha za shule zikiingia vizuri na ShuleXpert ikisasishwa kwa wakati halisi.
        <br><br>
        <strong>Muhimu:</strong> Uunganishaji wa API wa moja kwa moja na benki bado haujatekelezwa.
        Hati hii ni ramani ya modality ili NBC iunde API zinazohitajika na ShuleXpert iunde endpoints zake.
    </div>

    <div class="section-title">1. Muundo wa Sasa wa Ada na Malipo kwenye ShuleXpert</div>
    <p>
        Shule zinaweka <strong>Fee Structure</strong> rasmi kwenye ShuleXpert (kwa darasa / mwaka wa masomo),
        ikijumuisha majina ya ada, kiasi, na <strong>namba za akaunti za benki zilizothibitishwa</strong>.
        Kisha mfumo unatengeneza <strong>Control Number</strong> kwa kila bili ya mwanafunzi. Mzazi anatumia
        control number hiyo kama rejea ya malipo anapolipa.
    </p>
    <table class="data-table">
        <tr>
            <th style="width:28%;">Sehemu</th>
            <th>Maelezo</th>
        </tr>
        <tr>
            <td><strong>Fee Structure</strong></td>
            <td>Inaeleza kinachopaswa kulipwa (aina/jina la ada, kiasi, darasa, mwaka) na kuunganisha ada na akaunti ya benki ya shule iliyothibitishwa.</td>
        </tr>
        <tr>
            <td><strong>Akaunti ya Benki</strong></td>
            <td>Akaunti ya shule iliyosajiliwa kwenye ShuleXpert na kuidhinishwa kwa ukusanyaji wa ada. Ni akaunti lengwa ya malipo ya bili hiyo.</td>
        </tr>
        <tr>
            <td><strong>Control Number</strong></td>
            <td>Kitambulisho pepe cha tarakimu 9 (muundo <span style="font-family:Courier New,monospace;">3345XXXXX</span>) kinachotolewa na ShuleXpert kwa mzazi. Kinaunganisha bili ya mwanafunzi na akaunti lengwa.</td>
        </tr>
        <tr>
            <td><strong>Daftari la Malipo</strong></td>
            <td>ShuleXpert inarekodi malipo kwa control number, inagawanya kwenye mistari ya ada, na inasasisha mizani / hali ya malipo.</td>
        </tr>
    </table>
    <div class="note-box">
        <strong>Akaunti Moja vs Akaunti Nyingi:</strong>
        Darasa linaweza kutumia akaunti moja ya shule kwa ada zote (control number moja), au akaunti tofauti kwa kila ada
        (control number tofauti). Katika hali zote, Name Lookup inarudisha akaunti sahihi iliyothibitishwa kwa control number hiyo.
    </div>

    <div class="section-title">2. Mtiririko wa Malipo Unaopendekezwa</div>
    <div class="flow">Mzazi / Mlipaji
   │
   │  Anaingiza Control Number kwenye njia ya NBC
   │  (App ya NBC / Tawi / Wakala / Mitandao ya simu iliyounganishwa na NBC)
   ▼
NBC Core  ──GET──►  ShuleXpert Name Lookup API
   │                 (inarudisha mwanafunzi, aina ya ada, namba ya akaunti ya shule)
   │
   │  Inamuonyesha mlipaji: Jina la Mwanafunzi + Aina ya Ada/Bili
   │  Inatafuta Jina la Akaunti kutoka Namba ya Akaunti (upande wa NBC)
   │  Inamuonyesha mlipaji: Unalipa kwenda [Jina la Akaunti]
   │
   │  Mlipaji anathibitisha na kulipa
   ▼
Fedha zinaingia kwenye Namba ya Akaunti ya Shule (kutoka jibu la Name Lookup)
   │
   │  Makato/asilimia ya jukwaa yanatumika wakati wa malipo (angalia API 4)
   │  Sehemu ya jukwaa inaingia kwenye akaunti ya kampuni ya ShuleXpert
   ▼
NBC  ──POST Webhook──►  ShuleXpert Payment Notification API
   │                     (control number, kiasi, reference, hali)
   ▼
ShuleXpert inasasisha bili kama Imelipwa / Imeshindikana

Ikiwa upatanishaji utahitajika baadaye:
ShuleXpert  ──Uchunguzi──►  NBC Transaction Status API (kwa reference number)</div>

    <div class="section-title">3. Muhtasari wa Moduli za API</div>
    <table class="data-table">
        <tr>
            <th style="width:8%;">#</th>
            <th style="width:28%;">API</th>
            <th style="width:22%;">Mmiliki</th>
            <th>Kazi</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Name Lookup API</td>
            <td class="owner-sx">Inajengwa na ShuleXpert</td>
            <td>Kuthibitisha control number na kurudisha bili + akaunti lengwa.</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Payment Notification (Webhook)</td>
            <td class="owner-sx">Inajengwa na ShuleXpert</td>
            <td>Kupokea matokeo ya malipo kutoka NBC na kuyarekodi kwenye daftari la shule.</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Transaction Status Inquiry</td>
            <td class="owner-nbc">Inatolewa na NBC</td>
            <td>Kuruhusu ShuleXpert kuthibitisha malipo kwa kutumia reference number ya benki.</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Deduction / Makato ya Jukwaa</td>
            <td class="owner-nbc">Inatolewa na NBC</td>
            <td>Kutumia asilimia iliyokubaliwa wakati wa malipo; sehemu ya jukwaa iingie akaunti ya kampuni.</td>
        </tr>
    </table>

    <div class="page-break"></div>
    <div class="header" style="border-bottom: 1.5px solid #940000; margin-bottom: 10px; padding-bottom: 6px;">
        <div class="brand" style="font-size: 14px;">ShuleXpert × NBC — Maelezo ya API</div>
        <div class="meta-line">{{ $docRef }} &nbsp;|&nbsp; Endelezo</div>
    </div>

    <div class="section-title">4. API 1 — Name Lookup API <span class="api-badge">SHULEXPERT</span></div>
    <p>
        Mlipaji anapoingiza control number kwenye njia ya NBC, NBC hupiga ShuleXpert ili kuthibitisha bili
        na kupata taarifa za kuonyesha mteja pamoja na akaunti ya kuingizia fedha.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Kipengele</th><th>Pendekezo</th></tr>
        <tr><td>Method</td><td><strong>GET</strong></td></tr>
        <tr><td>Anayeita</td><td>Mifumo / njia za malipo za NBC</td></tr>
        <tr><td>Mtoa huduma</td><td>ShuleXpert</td></tr>
        <tr><td>Kigezo kikuu</td><td><code>control_number</code> (lazima)</td></tr>
        <tr><td>Uthibitishaji (utakubaliwa)</td><td>API key / mutual TLS / IP allow-list (itakamilishwa wakati wa utekelezaji)</td></tr>
    </table>
    <div class="sub-title">Mfano wa JSON ya majibu yenye mafanikio</div>
    <div class="mono">{
  "success": true,
  "control_number": "334512345",
  "student_id": "STU-000123",
  "student_full_name": "Amina Juma Hassan",
  "fee_type": "Ada za Shule — Kidato cha Kwanza (Masomo)",
  "account_number": "01110300XXXX",
  "bank_code": "NBC",
  "currency": "TZS",
  "amount_due": 450000.00,
  "school_name": "Example Secondary School"
}</div>
    <div class="ok-box">
        <strong>Uzoefu wa mteja baada ya jibu hili:</strong>
        NBC inamuonyesha mlipaji jina kamili la mwanafunzi na aina ya ada/bili kwa uthibitisho.
        Kwa kutumia <strong>account_number</strong> iliyorudishwa, NBC inatafuta na kuonyesha <strong>jina la akaunti</strong>
        (mmiliki) kabla ya malipo. Baada ya kuthibitisha, fedha zinaingia <strong>moja kwa moja kwenye akaunti hiyo ya shule</strong>.
    </div>
    <div class="note-box">
        <strong>Lookup imeshindikana:</strong> Ikiwa control number haijulikani, siyo hai, au haiwezi kulipwa,
        ShuleXpert inarudisha kosa wazi (mf. <code>CONTROL_NUMBER_NOT_FOUND</code>) ili NBC isimamishe safari ya malipo.
    </div>

    <div class="section-title">5. Uthibitisho wa Malipo kwenye NBC (Tabia ya Settlement)</div>
    <ol>
        <li>Mlipaji anathibitisha utambulisho wa bili (jina + aina ya ada) kutoka Name Lookup.</li>
        <li>NBC inaonyesha jina la akaunti lengwa lililotafutwa kutoka namba ya akaunti iliyorudishwa.</li>
        <li>Baada ya kutoa fedha kwa mlipaji kwa mafanikio, NBC inaingiza fedha kwenye <strong>akaunti ya shule</strong> iliyorudishwa na ShuleXpert.</li>
        <li>Sambamba (au kwa mpangilio wa NBC), moduli ya makato (API 4) inatumia asilimia ya jukwaa na kuingiza sehemu ya ShuleXpert kwenye akaunti ya kampuni.</li>
        <li>Kisha NBC inaarifu ShuleXpert kupitia Payment Webhook (API 2).</li>
    </ol>

    <div class="section-title">6. API 2 — Payment Notification Webhook <span class="api-badge">SHULEXPERT</span></div>
    <p>
        Baada ya jaribio la malipo, NBC inatuma matokeo kwa ShuleXpert. ShuleXpert inarekodi majaribio yaliyofanikiwa
        na yaliyoshindikana kwa ukaguzi na huduma kwa wateja.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Kipengele</th><th>Pendekezo</th></tr>
        <tr><td>Method</td><td><strong>POST</strong> (webhook / callback)</td></tr>
        <tr><td>Anayeita</td><td>NBC</td></tr>
        <tr><td>Mtoa huduma</td><td>Endpoint ya ShuleXpert (URL itawasilishwa kwa NBC)</td></tr>
        <tr><td>Idempotency</td><td>Lazima iwe salama kurudiwa kwa kutumia <code>reference_number</code></td></tr>
    </table>
    <div class="sub-title">Mfano wa JSON ya webhook</div>
    <div class="mono">{
  "control_number": "334512345",
  "amount": 450000.00,
  "currency": "TZS",
  "reference_number": "NBC202607171234567",
  "payment_status": "SUCCESS",
  "paid_at": "2026-07-17T14:22:10+03:00",
  "channel": "MOBILE_MONEY",
  "payer_msisdn": "2557XXXXXXXX",
  "narration": "Malipo ya ada za shule"
}</div>
    <table class="data-table">
        <tr>
            <th style="width:28%;">payment_status</th>
            <th>Hatua ya ShuleXpert</th>
        </tr>
        <tr>
            <td><strong>SUCCESS</strong> (au sawa)</td>
            <td>Unda rekodi ya malipo iliyothibitishwa kwa control number; gawanya kwenye mistari ya ada; sasisha mizani na hali (Imelipwa / Sehemu kulingana na kiasi).</td>
        </tr>
        <tr>
            <td><strong>FAILED</strong> (au sawa)</td>
            <td>Rekodi jaribio lililoshindikana pamoja na reference kwa usaidizi/ukaguzi; usiongeze salio la mwanafunzi.</td>
        </tr>
    </table>

    <div class="section-title">7. API 3 — Transaction Status Inquiry <span class="api-badge" style="background:#0a7a45;">NBC</span></div>
    <p>
        Inatumika mlipaji anaporipoti amelipa lakini ShuleXpert haijapokea (au imechelewa) webhook.
        ShuleXpert hutuma <strong>reference number</strong> ya benki; NBC inarudisha kama muamala umefanikiwa.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Kipengele</th><th>Pendekezo</th></tr>
        <tr><td>Method</td><td>GET au POST (kama NBC itakavyopendelea)</td></tr>
        <tr><td>Anayeita</td><td>ShuleXpert</td></tr>
        <tr><td>Mtoa huduma</td><td>NBC</td></tr>
        <tr><td>Kigezo muhimu</td><td><code>reference_number</code> (na hiari <code>control_number</code>)</td></tr>
        <tr><td>Matokeo yanayotarajiwa</td><td>Hali ya muamala, kiasi, nyakati, na kiashiria cha ukamilifu</td></tr>
    </table>
    <div class="mono">{
  "reference_number": "NBC202607171234567",
  "control_number": "334512345",
  "status": "SUCCESS",
  "amount": 450000.00,
  "currency": "TZS",
  "paid_at": "2026-07-17T14:22:10+03:00"
}</div>

    <div class="page-break"></div>
    <div class="header" style="border-bottom: 1.5px solid #940000; margin-bottom: 10px; padding-bottom: 6px;">
        <div class="brand" style="font-size: 14px;">ShuleXpert × NBC — Makato na Hatua Zijazo</div>
        <div class="meta-line">{{ $docRef }} &nbsp;|&nbsp; Endelezo</div>
    </div>

    <div class="section-title">8. API 4 — Deduction / Makato ya Jukwaa <span class="api-badge" style="background:#0a7a45;">NBC</span></div>
    <p>
        ShuleXpert inaomba makato yafanyike <strong>wakati mteja anapolipa</strong>, si kwa kutoa baadaye
        kutoka akaunti ya shule. Shule inapokea ukusanyaji wake kwenye akaunti yake kulingana na mpango,
        huku asilimia iliyokubaliwa ya jukwaa ikipelekwa kwenye akaunti za kampuni ya ShuleXpert
        kama chanzo cha mapato ya jukwaa.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Kipengele</th><th>Pendekezo</th></tr>
        <tr><td>Chanzo cha usanidi</td><td>ShuleXpert inapeleka asilimia ya makato na JSON ya akaunti za kampuni</td></tr>
        <tr><td>Mtoa huduma</td><td>NBC inatumia makato wakati wa kuchakata malipo</td></tr>
        <tr><td>Msingi</td><td>Asilimia ya kiasi anacholipa mteja</td></tr>
        <tr><td>Muda</td><td>Wakati wa malipo (point of collection)</td></tr>
    </table>
    <div class="sub-title">Mfano wa payload ya usanidi kutoka ShuleXpert kwenda NBC</div>
    <div class="mono">{
  "deduction_percent": 1.5,
  "currency": "TZS",
  "company_accounts": [
    {
      "account_number": "01110COMPANYXXXX",
      "bank_code": "NBC",
      "account_name": "EmCa Techonology / ShuleXpert",
      "share_percent": 100
    }
  ],
  "notes": "Kata ada ya jukwaa wakati wa malipo; weka sehemu ya shule kwenye akaunti ya shule"
}</div>
    <div class="note-box">
        Maelezo kamili ya mgawanyo (onyesho la gross vs net kwa mlipaji, VAT/kodi ikiwa ipo, na narration ya taarifa)
        yatakamilishwa na timu za bidhaa/fedha za NBC. Nia ya biashara ni wazi:
        <strong>kata mara moja wakati wa malipo</strong>, weka shule + jukwaa vizuri, na sasisha daftari la ShuleXpert kupitia webhook.
    </div>

    <div class="section-title">9. Jedwali la Majukumu</div>
    <table class="data-table">
        <tr>
            <th style="width:34%;">Jukumu</th>
            <th style="width:33%;">ShuleXpert</th>
            <th>NBC</th>
        </tr>
        <tr>
            <td>Fee structure na control numbers</td>
            <td>Ndiyo</td>
            <td>—</td>
        </tr>
        <tr>
            <td>Rejista ya akaunti za shule zilizothibitishwa</td>
            <td>Ndiyo (chanzo cha ukweli kwa lookup)</td>
            <td>Kutafuta jina la akaunti na settlement</td>
        </tr>
        <tr>
            <td>Name Lookup API</td>
            <td>Jenga na hositi</td>
            <td>Tumia</td>
        </tr>
        <tr>
            <td>UI / njia za malipo</td>
            <td>—</td>
            <td>Ndiyo</td>
        </tr>
        <tr>
            <td>Kuweka fedha akaunti ya shule</td>
            <td>—</td>
            <td>Ndiyo</td>
        </tr>
        <tr>
            <td>Payment webhook</td>
            <td>Jenga na hositi</td>
            <td>Piga kwa SUCCESS/FAILED</td>
        </tr>
        <tr>
            <td>Status inquiry API</td>
            <td>Tumia</td>
            <td>Jenga na hositi</td>
        </tr>
        <tr>
            <td>Injini ya makato</td>
            <td>Tuma % + akaunti za kampuni</td>
            <td>Tumia wakati wa malipo</td>
        </tr>
        <tr>
            <td>Usasishaji wa daftari la shule</td>
            <td>Ndiyo</td>
            <td>—</td>
        </tr>
    </table>

    <div class="section-title">10. Usalama, Uaminifu na Mahitaji Yasiyo ya Kiutendaji</div>
    <ul>
        <li>Uthibitishaji wa pande zote (API keys / vyeti) na IP allow-listing inapohitajika.</li>
        <li>HTTPS pekee; hakuna data ya control number kupitia HTTP isiyo salama.</li>
        <li>Sahihi ya webhook / HMAC inapendekezwa ili ShuleXpert ithibitishe uhalali wa NBC.</li>
        <li>Ushughulikiaji wa webhook usiorudia makosa (idempotent) ukitumia <code>reference_number</code>.</li>
        <li>Sera ya kurudia (retry) kutoka NBC ikiwa webhook imeshindikana.</li>
        <li>Kumbukumbu za ukaguzi zihifadhiwe pande zote kwa upatanishaji.</li>
        <li>Mazingira ya UAT kabla ya uzinduzi wa production.</li>
    </ul>

    <div class="section-title">11. Hatua Zijazo za Ushirikiano</div>
    <ol>
        <li>NBC ipitie na kuthibitisha modality hii (au kupendekeza marekebisho ya uga).</li>
        <li>Makubaliano ya pamoja kuhusu JSON schemas, status enums, auth, na error codes.</li>
        <li>NBC ishiriki mikataba ya Transaction Status Inquiry API na Deduction API.</li>
        <li>ShuleXpert itekeleze Name Lookup + Payment Webhook kwenye UAT.</li>
        <li>UAT kamili: lookup → lipa → akaunti ya shule → webhook → daftari → inquiry ya dharura.</li>
        <li>Orodha ya uzinduzi wa production (ufuatiliaji, mawasiliano ya support, mpango wa kurudisha nyuma).</li>
    </ol>

    <div class="ok-box">
        <strong>Mawasiliano.</strong>
        Kwa ufafanuzi wa kiufundi kuhusu modality hii, wasiliana na timu ya uunganishaji ya
        ShuleXpert / EmCa Techonology kupitia kituo cha benki cha Super Admin.
        Hati mwenzake inapatikana kwa Kiingereza.
    </div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">Kwa niaba ya ShuleXpert / EmCa Techonology<br><span class="muted">Jina / Cheo / Tarehe</span></div>
            </td>
            <td>
                <div class="sig-line">Kwa niaba ya National Bank of Commerce (NBC)<br><span class="muted">Jina / Cheo / Tarehe</span></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        ShuleXpert Modality ya Uunganishaji wa Malipo — Siri &nbsp;|&nbsp; {{ $docRef }} &nbsp;|&nbsp; Imetengenezwa {{ $docDate }} &nbsp;|&nbsp; Powered by EmCa Techonology
    </div>
</body>
</html>
