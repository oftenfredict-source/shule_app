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
        if (!function_exists('sxPdfLogoBase64')) {
            function sxPdfLogoBase64($path) {
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
        $logo = sxPdfLogoBase64('images/shuleXpert.jpg') ?: sxPdfLogoBase64('images/logo.png');
        $docDate = $generated_at ?? date('d M Y');
        $docRef = $document_ref ?? ('SX-NBC-PAY-' . date('Y'));
    @endphp

    <div class="header">
        @if($logo)
            <img src="{{ $logo }}" style="max-height: 58px; margin-bottom: 4px;">
        @endif
        <div class="brand">ShuleXpert</div>
        <div style="font-size: 10px; color: #666;">School Management Platform &mdash; Powered by EmCa Techonology</div>
        <div class="doc-title">{{ $title }}</div>
        <div class="meta-line">Document Ref: {{ $docRef }} &nbsp;|&nbsp; Version {{ $version ?? '1.0' }} &nbsp;|&nbsp; {{ $docDate }} &nbsp;|&nbsp; Confidential, For NBC Integration Use</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Prepared For</td>
            <td>National Bank of Commerce (NBC), Payment Integration Team</td>
            <td class="label">Prepared By</td>
            <td>ShuleXpert / EmCa Techonology</td>
        </tr>
        <tr>
            <td class="label">Subject</td>
            <td colspan="3">Proposed School Fee Collection Integration Modality (Control Number Based)</td>
        </tr>
        <tr>
            <td class="label">Languages</td>
            <td>English (this document) / Kiswahili (companion PDF)</td>
            <td class="label">Status</td>
            <td>Proposal for API Design Collaboration</td>
        </tr>
    </table>

    <div class="summary-box">
        <strong>Purpose.</strong>
        This document describes how ShuleXpert currently manages school fee structures and control numbers,
        and proposes a clear integration modality with NBC so that parents/guardians can pay school fees
        through NBC channels (bank, mobile money partners, and other NBC-connected rails), while school
        collections are credited correctly and ShuleXpert remains synchronized in real time.
        <br><br>
        <strong>Important:</strong> Live bank API integration is not yet implemented. This document is the
        agreed modality blueprint for NBC to design/build complementary APIs while ShuleXpert implements
        its endpoints.
    </div>

    <div class="section-title">1. Current ShuleXpert Fee &amp; Payment Model</div>
    <p>
        Schools define an official <strong>Fee Structure</strong> in ShuleXpert (per class / academic year),
        including fee names, amounts, and the school’s verified <strong>NBC (or partner) bank account number(s)</strong>.
        The system then generates a <strong>Control Number</strong> for each student bill. Parents use that
        control number as the payment reference when paying.
    </p>
    <table class="data-table">
        <tr>
            <th style="width:28%;">Component</th>
            <th>Description</th>
        </tr>
        <tr>
            <td><strong>Fee Structure</strong></td>
            <td>Defines what must be paid (fee type/name, amount, class, academic year) and links each fee (or fee group) to a verified school bank account.</td>
        </tr>
        <tr>
            <td><strong>Bank Account</strong></td>
            <td>School account registered in ShuleXpert and approved/verified for fee collection. Used as the destination account for that bill.</td>
        </tr>
        <tr>
            <td><strong>Control Number</strong></td>
            <td>Unique 9-digit bill identifier (format <span style="font-family:Courier New,monospace;">3345XXXXX</span>) generated by ShuleXpert and issued to the parent. It maps to one student bill and one destination account.</td>
        </tr>
        <tr>
            <td><strong>Payment Ledger</strong></td>
            <td>ShuleXpert records payments against the control number, allocates to fee lines, and updates balances / payment status.</td>
        </tr>
    </table>
    <div class="note-box">
        <strong>Single vs Multi Account:</strong>
        A class may use one shared school account for all fees (one control number), or separate accounts per fee
        (separate control numbers). In all cases, Name Lookup returns the correct verified account for that control number.
    </div>

    <div class="section-title">2. Proposed End-to-End Payment Flow</div>
    <div class="flow">Parent / Payer
   │
   │  Enters Control Number on NBC channel
   │  (NBC App / Branch / Agent / Mobile Money rail connected to NBC)
   ▼
NBC Core  ──GET──►  ShuleXpert Name Lookup API
   │                 (returns student, fee type, school account number)
   │
   │  Shows payer: Student Name + Fee/Bill type
   │  Resolves Account Name from Account Number (NBC side)
   │  Shows payer: Paying to [Account Name]
   │
   │  Payer confirms &amp; pays
   ▼
Funds credited to School Account Number (from Name Lookup response)
   │
   │  Commission/deduction applied at payment time (see API 4)
   │  Platform share credited to ShuleXpert company account(s)
   ▼
NBC  ──POST Webhook──►  ShuleXpert Payment Notification API
   │                     (control number, amount, reference, status)
   ▼
ShuleXpert updates bill as Paid / Failed (ledger + status)

If reconciliation needed later:
ShuleXpert  ──Inquiry──►  NBC Transaction Status API (by reference number)</div>

    <div class="section-title">3. API Module Overview</div>
    <table class="data-table">
        <tr>
            <th style="width:8%;">#</th>
            <th style="width:28%;">API</th>
            <th style="width:22%;">Owner</th>
            <th>Role</th>
        </tr>
        <tr>
            <td>1</td>
            <td>Name Lookup API</td>
            <td class="owner-sx">ShuleXpert builds</td>
            <td>Validate control number and return bill + destination account details.</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Payment Notification (Webhook)</td>
            <td class="owner-sx">ShuleXpert builds</td>
            <td>Receive payment result from NBC and post to school ledger.</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Transaction Status Inquiry</td>
            <td class="owner-nbc">NBC provides</td>
            <td>Allow ShuleXpert to verify a payment using bank reference number.</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Deduction / Platform Commission</td>
            <td class="owner-nbc">NBC provides</td>
            <td>Apply agreed percentage deduction at payment time; credit platform share to company account(s).</td>
        </tr>
    </table>

    <div class="page-break"></div>
    <div class="header" style="border-bottom: 1.5px solid #940000; margin-bottom: 10px; padding-bottom: 6px;">
        <div class="brand" style="font-size: 14px;">ShuleXpert × NBC, API Specifications</div>
        <div class="meta-line">{{ $docRef }} &nbsp;|&nbsp; Continued</div>
    </div>

    <div class="section-title">4. API 1, Name Lookup API <span class="api-badge">SHULEXPERT</span></div>
    <p>
        When a payer enters a control number on an NBC channel, NBC calls ShuleXpert to confirm the bill
        and retrieve display + settlement data.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Item</th><th>Proposal</th></tr>
        <tr><td>Method</td><td><strong>GET</strong></td></tr>
        <tr><td>Caller</td><td>NBC systems / payment rails</td></tr>
        <tr><td>Provider</td><td>ShuleXpert</td></tr>
        <tr><td>Primary parameter</td><td><code>control_number</code> (required)</td></tr>
        <tr><td>Auth (to agree)</td><td>API key / mutual TLS / IP allow-list (finalized during implementation)</td></tr>
    </table>
    <div class="sub-title">Expected successful JSON response (illustrative)</div>
    <div class="mono">{
  "success": true,
  "control_number": "334512345",
  "student_id": "STU-000123",
  "student_full_name": "Amina Juma Hassan",
  "fee_type": "School Fees, Form One (Tuition)",
  "account_number": "01110300XXXX",
  "bank_code": "NBC",
  "currency": "TZS",
  "amount_due": 450000.00,
  "school_name": "Example Secondary School"
}</div>
    <div class="ok-box">
        <strong>Customer experience after this response:</strong>
        NBC displays the student full name and fee/bill type to the payer for confirmation.
        Using the returned <strong>account_number</strong>, NBC resolves and shows the <strong>account name</strong>
        (account owner) before payment. On confirmation, funds are credited <strong>directly to that school account</strong>.
    </div>
    <div class="note-box">
        <strong>Lookup failure:</strong> If the control number is unknown, inactive, or not payable, ShuleXpert
        returns a clear error (e.g. <code>CONTROL_NUMBER_NOT_FOUND</code>) so NBC can stop the payment journey.
    </div>

    <div class="section-title">5. Payment Confirmation at NBC (Settlement Behaviour)</div>
    <ol>
        <li>Payer confirms identity of the bill (name + fee type) shown from Name Lookup.</li>
        <li>NBC shows destination account name resolved from the returned account number.</li>
        <li>On successful debit of the payer, NBC credits the <strong>school account number</strong> returned by ShuleXpert.</li>
        <li>In parallel (or atomically as designed by NBC), the deduction module (API 4) applies the agreed platform percentage and credits ShuleXpert company account(s).</li>
        <li>NBC then notifies ShuleXpert via the Payment Webhook (API 2).</li>
    </ol>

    <div class="section-title">6. API 2, Payment Notification Webhook <span class="api-badge">SHULEXPERT</span></div>
    <p>
        After a payment attempt, NBC posts the result to ShuleXpert. ShuleXpert records both successful and failed attempts
        for auditability and support.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Item</th><th>Proposal</th></tr>
        <tr><td>Method</td><td><strong>POST</strong> (webhook / callback)</td></tr>
        <tr><td>Caller</td><td>NBC</td></tr>
        <tr><td>Provider</td><td>ShuleXpert endpoint (URL to be submitted to NBC)</td></tr>
        <tr><td>Idempotency</td><td>Must be safe to retry using <code>reference_number</code></td></tr>
    </table>
    <div class="sub-title">Expected webhook JSON payload (illustrative)</div>
    <div class="mono">{
  "control_number": "334512345",
  "amount": 450000.00,
  "currency": "TZS",
  "reference_number": "NBC202607171234567",
  "payment_status": "SUCCESS",
  "paid_at": "2026-07-17T14:22:10+03:00",
  "channel": "MOBILE_MONEY",
  "payer_msisdn": "2557XXXXXXXX",
  "narration": "School fee payment"
}</div>
    <table class="data-table">
        <tr>
            <th style="width:28%;">payment_status</th>
            <th>ShuleXpert action</th>
        </tr>
        <tr>
            <td><strong>SUCCESS</strong> (or equivalent)</td>
            <td>Create verified payment record against the control number; allocate to fee lines; update balance and status (Paid / Partial as applicable).</td>
        </tr>
        <tr>
            <td><strong>FAILED</strong> (or equivalent)</td>
            <td>Record failed attempt with reference for support/audit; do not credit student balance.</td>
        </tr>
    </table>

    <div class="section-title">7. API 3, Transaction Status Inquiry <span class="api-badge" style="background:#0a7a45;">NBC</span></div>
    <p>
        Used when a payer reports payment but ShuleXpert has not received (or has delayed) the webhook.
        ShuleXpert sends the bank <strong>reference number</strong>; NBC returns whether the transaction succeeded.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Item</th><th>Proposal</th></tr>
        <tr><td>Method</td><td>GET or POST (as preferred by NBC)</td></tr>
        <tr><td>Caller</td><td>ShuleXpert</td></tr>
        <tr><td>Provider</td><td>NBC</td></tr>
        <tr><td>Key parameter</td><td><code>reference_number</code> (and optionally <code>control_number</code>)</td></tr>
        <tr><td>Expected result</td><td>Transaction status, amount, timestamps, and finality indicator</td></tr>
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
        <div class="brand" style="font-size: 14px;">ShuleXpert × NBC, Deduction &amp; Next Steps</div>
        <div class="meta-line">{{ $docRef }} &nbsp;|&nbsp; Continued</div>
    </div>

    <div class="section-title">8. API 4, Deduction / Platform Commission <span class="api-badge" style="background:#0a7a45;">NBC</span></div>
    <p>
        ShuleXpert requests that deduction happen <strong>at the moment the client pays</strong>, not by later
        withdrawing from the school account. The school receives the fee collection as designed for their
        account credit path, while the agreed platform percentage is routed to ShuleXpert company account(s)
        as a source of income for the platform.
    </p>
    <table class="data-table">
        <tr><th style="width:30%;">Item</th><th>Proposal</th></tr>
        <tr><td>Caller / config source</td><td>ShuleXpert provides deduction percentage and destination company account JSON</td></tr>
        <tr><td>Provider</td><td>NBC applies deduction during payment processing</td></tr>
        <tr><td>Base</td><td>Percentage of the amount being paid by the client</td></tr>
        <tr><td>Timing</td><td>At payment time (point of collection)</td></tr>
    </table>
    <div class="sub-title">Illustrative configuration payload from ShuleXpert to NBC</div>
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
  "notes": "Deduct platform fee at payment; credit school account with school portion"
}</div>
    <div class="note-box">
        Exact split mechanics (gross vs net display to payer, VAT/tax treatment if any, and statement narration)
        will be finalized with NBC product/finance teams. The business intent is clear:
        <strong>deduct once at payment</strong>, credit school + platform destinations correctly, and keep
        ShuleXpert ledger updated via webhook.
    </div>

    <div class="section-title">9. Responsibilities Matrix</div>
    <table class="data-table">
        <tr>
            <th style="width:34%;">Responsibility</th>
            <th style="width:33%;">ShuleXpert</th>
            <th>NBC</th>
        </tr>
        <tr>
            <td>Fee structure &amp; control numbers</td>
            <td>Yes</td>
            <td>-</td>
        </tr>
        <tr>
            <td>Verified school account registry</td>
            <td>Yes (source of truth for lookup)</td>
            <td>Account name resolution &amp; settlement rails</td>
        </tr>
        <tr>
            <td>Name Lookup API</td>
            <td>Build &amp; host</td>
            <td>Consume</td>
        </tr>
        <tr>
            <td>Payment UI / channels</td>
            <td>-</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Credit school account</td>
            <td>-</td>
            <td>Yes</td>
        </tr>
        <tr>
            <td>Payment webhook</td>
            <td>Build &amp; host</td>
            <td>Call on SUCCESS/FAILED</td>
        </tr>
        <tr>
            <td>Status inquiry API</td>
            <td>Consume</td>
            <td>Build &amp; host</td>
        </tr>
        <tr>
            <td>Deduction engine</td>
            <td>Send % + company accounts</td>
            <td>Apply at payment time</td>
        </tr>
        <tr>
            <td>School ledger update</td>
            <td>Yes</td>
            <td>-</td>
        </tr>
    </table>

    <div class="section-title">10. Security, Reliability &amp; Non-Functional Requirements</div>
    <ul>
        <li>Mutual authentication (API keys / certificates) and IP allow-listing where applicable.</li>
        <li>HTTPS only; no control-number data over plain HTTP.</li>
        <li>Webhook signature / HMAC recommended so ShuleXpert can verify NBC authenticity.</li>
        <li>Idempotent webhook handling keyed by <code>reference_number</code>.</li>
        <li>Retry policy with exponential backoff from NBC for failed webhook delivery.</li>
        <li>Audit logs retained on both sides for reconciliation.</li>
        <li>UAT environment endpoints before production cut-over.</li>
    </ul>

    <div class="section-title">11. Proposed Collaboration Next Steps</div>
    <ol>
        <li>NBC reviews and confirms this modality (or proposes field-level adjustments).</li>
        <li>Joint agreement on JSON schemas, status enums, auth method, and error codes.</li>
        <li>NBC shares Transaction Status Inquiry API and Deduction API contracts.</li>
        <li>ShuleXpert implements Name Lookup + Payment Webhook in UAT.</li>
        <li>End-to-end UAT: lookup → pay → school credit → webhook → ledger → inquiry fallback.</li>
        <li>Production go-live checklist (monitoring, support contacts, rollback plan).</li>
    </ol>

    <div class="ok-box">
        <strong>Contact.</strong>
        For technical clarification on this modality, please engage the ShuleXpert / EmCa Techonology
        integration team via the Super Admin banking channel. Companion document available in Kiswahili.
    </div>

    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">For ShuleXpert / EmCa Techonology<br><span class="muted">Name / Title / Date</span></div>
            </td>
            <td>
                <div class="sig-line">For National Bank of Commerce (NBC)<br><span class="muted">Name / Title / Date</span></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        ShuleXpert Payment Integration Modality, Confidential &nbsp;|&nbsp; {{ $docRef }} &nbsp;|&nbsp; Page generated {{ $docDate }} &nbsp;|&nbsp; Powered by EmCa Techonology
    </div>
</body>
</html>
