<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 10.5px; color: #333; line-height: 1.45; }
        .header { text-align: center; margin-bottom: 16px; border-bottom: 2px solid #940000; padding-bottom: 8px; }
        .school-name { font-size: 17px; font-weight: bold; color: #940000; text-transform: uppercase; }
        .report-title { font-size: 13px; font-weight: bold; margin-top: 8px; background: #f8f9fa; padding: 6px; color: #940000; }
        .section { margin-bottom: 14px; page-break-inside: avoid; }
        .section-title { font-size: 12px; font-weight: bold; color: #940000; border-bottom: 1px solid #f0d0d0; padding-bottom: 4px; margin-bottom: 8px; }
        .summary-box { background: #fdf2f2; border: 1px solid #fbd5d5; padding: 10px; margin-bottom: 12px; border-radius: 4px; }
        .warn-box { background: #fff8e6; border: 1px solid #ffe0a3; padding: 10px; margin-bottom: 12px; }
        .ok-box { background: #f0fff4; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 12px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.data-table th { background: #940000; color: #fff; font-weight: bold; }
        ol, ul { margin: 6px 0 6px 18px; padding: 0; }
        li { margin-bottom: 5px; }
        .mono { font-family: 'Courier New', monospace; font-size: 10px; background: #f5f5f5; padding: 1px 4px; }
        .diagram { font-family: 'Courier New', monospace; font-size: 9px; background: #f8f9fa; border: 1px solid #eee; padding: 10px; line-height: 1.35; white-space: pre-wrap; }
        .page-break { page-break-before: always; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #eee; padding-top: 5px; }
        .watermark { position: fixed; top: 45%; left: 5%; width: 100%; text-align: center; opacity: 0.04; transform: rotate(-45deg); font-size: 90px; font-weight: bold; color: #000; z-index: 1000; }
    </style>
</head>
<body>
    <div class="watermark">ShuleXpert</div>
    @php
        if (!function_exists('getSmartBase64Guide')) {
            function getSmartBase64Guide($path) {
                if (!$path) return null;
                $path = ltrim($path, '/');
                foreach ([public_path($path), public_path('uploads/' . $path), base_path('public/' . $path)] as $fullPath) {
                    if (@file_exists($fullPath) && is_file($fullPath)) {
                        try {
                            $data = base64_encode(file_get_contents($fullPath));
                            $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                            return 'data:image/' . ($ext ?: 'png') . ';base64,' . $data;
                        } catch (\Exception $e) {}
                    }
                }
                return null;
            }
        }
        $schoolLogoBase64 = $school ? getSmartBase64Guide($school->school_logo ?? '') : null;
        $liveUrl = $live_url ?? config('app.url', 'https://test.shulexpert.com');
    @endphp

    <div class="header">
        @if($schoolLogoBase64)
            <img src="{{ $schoolLogoBase64 }}" style="max-height: 55px; margin-bottom: 4px;">
        @endif
        <div class="school-name">{{ $school->school_name ?? 'ShuleXpert' }}</div>
        <div class="report-title">{{ $title }}</div>
        <div style="font-size: 9px; color: #666; margin-top: 4px;">ZKTeco UF200-S &bull; LAN School Sync &bull; {{ $generated_at }}</div>
    </div>

    <div class="summary-box">
        <strong>Muhtasari:</strong> UF200-S ni device ya <strong>TCP/IP (standalone)</strong>: haina ADMS/Push kwenye menu ya Comm.
        Server ya live (cPanel) <strong>haiwezi</strong> kufikia IP ya ndani <span class="mono">192.168.x.x</span>.
        Suluhisho: <strong>PC moja shuleni</strong> yenye Laragon + ShuleXpert inasoma device kwenye LAN na kuandika kwenye database ya mfumo (live au local).
    </div>

    <div class="section">
        <div class="section-title">1. Kwa nini Test Connection haifanyi kazi kwenye Live?</div>
        <table class="data-table">
            <tr>
                <th style="width:35%;">Mahali</th>
                <th>Inafanya kazi?</th>
                <th>Sababu</th>
            </tr>
            <tr>
                <td><strong>test.shulexpert.com</strong> (cPanel)</td>
                <td style="color:#c00;">Hapana</td>
                <td>Server iko internet; device iko LAN ya shule, haziwezi kuongea moja kwa moja.</td>
            </tr>
            <tr>
                <td><strong>Laragon / PC shuleni</strong></td>
                <td style="color:#080;">Ndiyo</td>
                <td>PC na device ziko network moja, TCP port <span class="mono">4370</span> inafanya kazi.</td>
            </tr>
            <tr>
                <td><strong>UF200-S, Comm menu</strong></td>
                <td>Ethernet + PC Connection tu</td>
                <td>Model hii haina Cloud Server / ADMS, ni kawaida.</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">2. Mfumo unaopendekezwa (School LAN Sync)</div>
        <div class="diagram">[Walimu / Staff]  --->  [UF200-S Device]  &lt;---LAN---&gt;  [PC ya Shule, Laragon + ShuleXpert]
                                                              |
                                                              |  Internet (HTTPS / MySQL)
                                                              v
                                                    [Database ya ShuleXpert]
                                                              |
                                                              v
                                                    [Live Site: {{ $liveUrl }}]</div>
        <p><strong>Kila siku:</strong> Watu wanaweka kidole kwenye device kama kawaida (hakuna mabadiliko).</p>
        <p><strong>Sync:</strong> PC ya shule inavuta rekodi kutoka device na kuziweka kwenye database, data inaonekana kwenye live site.</p>
    </div>

    <div class="warn-box">
        <strong>Muhimu, VPN kwenye PC haisaidii sync kwenye website ya live:</strong>
        Ukibonyeza Sync kwenye <span class="mono">{{ $liveUrl }}</span>, ni <strong>server ya cPanel</strong> inayojaribu kuunganisha device, si PC yako.
        VPN inasaidia tu ikiwa <strong>sync inafanywa kwenye PC ya shule</strong> (Laragon), si kwenye browser ya live site.
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">3. Mahitaji (Hardware &amp; Software)</div>
        <table class="data-table">
            <tr><th>Kitu</th><th>Maelezo</th></tr>
            <tr><td>ZKTeco UF200-S</td><td>Serial mf. TRU725... &bull; Firmware Ver 8.0.4.2+ &bull; Comm: Ethernet + PC Connection</td></tr>
            <tr><td>Network</td><td>Device na PC ziko subnet moja (mf. <span class="mono">192.168.100.x</span>)</td></tr>
            <tr><td>PC ya shule</td><td>Windows + Laragon (PHP 8.x, MySQL), iwekwe kuwaka sync</td></tr>
            <tr><td>ShuleXpert</td><td>Copy ya project kwenye PC (mf. <span class="mono">C:\laragon\www\ShuleXpert</span>)</td></tr>
            <tr><td>Internet</td><td>PC ina internet ikiwa unatumia database ya remote (cPanel)</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">4. Usanidi wa Device (UF200-S)</div>
        <p><strong>Menu → Comm → Ethernet</strong></p>
        <ol>
            <li>Weka <strong>IP Address</strong> ya device (mf. <span class="mono">192.168.100.109</span>)</li>
            <li><strong>Subnet Mask</strong>: sawa na router (mf. <span class="mono">255.255.255.0</span>)</li>
            <li><strong>Gateway</strong>: IP ya router</li>
            <li>Hakikisha device inaonyesha icon ya network juu ya screen</li>
        </ol>
        <p><strong>Menu → Comm → PC Connection</strong></p>
        <ol>
            <li><strong>Comm Key:</strong> <span class="mono">0</span> (default, lazima ilingane na ShuleXpert)</li>
            <li><strong>Device ID:</strong> <span class="mono">1</span> (kawaida)</li>
        </ol>
        <p><strong>Menu → System Info</strong>: andika IP, Serial Number, Firmware kwa kumbukumbu.</p>
    </div>

    <div class="section">
        <div class="section-title">5. Usajili Device kwenye ShuleXpert (PC ya shule)</div>
        <ol>
            <li>Fungua ShuleXpert kwenye PC ya shule: <span class="mono">http://127.0.0.1/ShuleXpert/public</span> (au Laragon URL yako)</li>
            <li>Ingia kama <strong>Admin</strong></li>
            <li>Nenda: <strong>Services → Fingerprint → Fingerprint Device Settings</strong></li>
            <li>Bonyeza <strong>Register New Device</strong>:
                <ul>
                    <li>Label: mf. <em>Main Gate UF200-S</em></li>
                    <li>Brand: ZKTeco</li>
                    <li>IP: IP ya device</li>
                    <li>Port: <span class="mono">4370</span></li>
                    <li>Comm Key: <span class="mono">0</span></li>
                    <li>Status: Active</li>
                </ul>
            </li>
            <li>Kwenye tab <strong>Device Tester</strong> → chagua device → <strong>Test Connection</strong> (lazima iwe Success kwenye PC ya shule)</li>
        </ol>
    </div>

    <div class="section">
        <div class="section-title">6. Database, Chaguo mbili</div>
        <table class="data-table">
            <tr>
                <th>Chaguo</th>
                <th>Maelezo</th>
                <th>Data kwenye live</th>
            </tr>
            <tr>
                <td><strong>A, Remote MySQL (cPanel)</strong></td>
                <td>Kwenye PC ya shule, <span class="mono">.env</span> inaelekeza kwenye MySQL ya cPanel. Weka <span class="mono">DB_HOST</span>, <span class="mono">DB_DATABASE</span>, <span class="mono">DB_USERNAME</span>, <span class="mono">DB_PASSWORD</span> kutoka cPanel → Remote MySQL (ruhusu IP ya shule).</td>
                <td style="color:#080;">Ndiyo, moja kwa moja</td>
            </tr>
            <tr>
                <td><strong>B, Database ya local</strong></td>
                <td>PC inatumia MySQL ya Laragon. Live site ina database tofauti, data haisongei mpaka uhamishe manually.</td>
                <td style="color:#c00;">Hapana (isipokuwa uhamishe)</td>
            </tr>
        </table>
        <p><strong>Inapendekezwa:</strong> Chaguo A, sync kwenye PC ya shule inaandika moja kwa moja kwenye database ya <span class="mono">{{ $liveUrl }}</span>.</p>
    </div>

    <div class="page-break"></div>

    <div class="section">
        <div class="section-title">7. Mahudhurio ya Walimu / Staff, Hatua za kila siku</div>
        <p>Nenda: <strong>Attendance → Teacher and Staff Attendance</strong></p>
        <table class="data-table">
            <tr><th>Tab</th><th>Kazi</th><th>Lini</th></tr>
            <tr>
                <td><strong>Live Attendance</strong></td>
                <td>Chagua device → ona walio scan leo (auto refresh)</td>
                <td>Mchana / wakati wowote</td>
            </tr>
            <tr>
                <td><strong>Sync from Device</strong></td>
                <td><strong>Sync All from Device</strong>: inavuta logs zote zisizohifadhiwa na kuziweka database</td>
                <td>Mwisho wa siku / asubuhi</td>
            </tr>
            <tr>
                <td><strong>Saved Records</strong></td>
                <td>Angalia rekodi zilizohifadhiwa (database)</td>
                <td>Kuthibitisha sync</td>
            </tr>
            <tr>
                <td><strong>Report</strong></td>
                <td>Ripoti za Present / Absent / PDF / SMS</td>
                <td>Mwisho wa mwezi</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">8. Ratiba ya Sync (Task Scheduler, Hiari)</div>
        <p>Ili sync ifanyike bila kubonyeza kila siku, weka <strong>Windows Task Scheduler</strong> kwenye PC ya shule:</p>
        <ol>
            <li>Unda file <span class="mono">sync-attendance.bat</span>:</li>
        </ol>
        <div class="diagram">@echo off
cd C:\laragon\www\ShuleXpert
C:\laragon\bin\php\php-8.x.x\php.exe artisan zkteco:sync-teacher-staff --device_id=1
echo Sync completed at %date% %time% >> storage\logs\school-sync.log</div>
        <p><em>(Badilisha path za PHP na device_id kulingana na usanidi wako. Ikiwa command haipo bado, tumia Sync All kutoka interface kila siku.)</em></p>
        <ol start="2">
            <li>Task Scheduler → Run kila <strong>5-15 dakika</strong> wakati wa siku za shule</li>
            <li>PC iwe <strong>wazi na kwenye network</strong> wakati wa sync</li>
        </ol>
    </div>

    <div class="section">
        <div class="section-title">9. Matatizo ya kawaida (Troubleshooting)</div>
        <table class="data-table">
            <tr><th>Tatizo</th><th>Suluhisho</th></tr>
            <tr><td>Test Connection inashindwa kwenye PC</td><td>Angalia IP, port 4370, Comm Key 0, firewall Windows, device imewashwa</td></tr>
            <tr><td>Sync inafanya kazi local lakini live haina data</td><td>Thibitisha <span class="mono">.env</span> inaelekeza remote MySQL ya cPanel; Remote MySQL iruhusu IP ya shule</td></tr>
            <tr><td>Live site Test Connection inashindwa</td><td>Hii ni kawaida, usitumie live kwa sync; tumia PC ya shule</td></tr>
            <tr><td>Hakuna ADMS kwenye Comm</td><td>UF200-S haina Push, tumia njia hii ya LAN sync</td></tr>
            <tr><td>Report ina Present 0</td><td>Hakikisha sync imefanyika; angalia filter ya mwezi; working days kwenye calendar</td></tr>
        </table>
    </div>

    <div class="ok-box">
        <strong>Checklist ya kuanza:</strong>
        ☐ Device imewekwa IP kwenye LAN &nbsp;|&nbsp;
        ☐ Comm Key = 0 &nbsp;|&nbsp;
        ☐ Device imesajiliwa ShuleXpert &nbsp;|&nbsp;
        ☐ Test Connection = Success (PC shule) &nbsp;|&nbsp;
        ☐ .env ina database sahihi &nbsp;|&nbsp;
        ☐ Sync All imefanyika &nbsp;|&nbsp;
        ☐ Saved Records zina data &nbsp;|&nbsp;
        ☐ Live site inaona data (Chaguo A)
    </div>

    <div class="section" style="margin-top: 20px;">
        <div class="section-title">10. Mawasiliano &amp; Msaada</div>
        <p>Kwa msaada wa kiufundi kuhusu ShuleXpert na fingerprint integration, wasiliana na timu ya <strong>EmCa Techonology</strong> kupitia support channel ya shule yako.</p>
        <p style="font-size: 9px; color: #666;">Hati hii imetengenezwa kiotomatiki na ShuleXpert, {{ $generated_at }}</p>
    </div>

    <div class="footer">
        Generated by ShuleXpert (School Management System), Powered By EmCa Techonology &copy; {{ date('Y') }}
    </div>
</body>
</html>
