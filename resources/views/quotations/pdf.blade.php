<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Quotation #{{ $quotation->quotation_no }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        @page {
            margin: 1.5cm 1.5cm;
            size: A4 portrait;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 9pt;
            color: #111; /* Darker text */
            line-height: 1.5;
        }
        
        /* Header */
        .header {
            width: 100%;
            margin-bottom: 40px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .header-left {
            float: left;
            width: 60%;
        }
        .header-right {
            float: right;
            width: 40%;
            text-align: right;
        }
        .company-name {
            font-size: 14pt;
            font-weight: 800;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 5px;
        }
        .company-details {
            font-size: 9pt;
            color: #333; /* Darker gray */
            line-height: 1.4;
        }
        .quote-number {
            font-size: 12pt;
            font-weight: 700;
            color: #000;
            margin-bottom: 2px;
        }
        .quote-date {
            font-size: 10pt;
            color: #333;
        }

        /* Project Info (Simplified) */
        .info-section {
            margin-bottom: 30px;
            width: 100%;
        }
        .project-name {
            font-size: 14pt;
            font-weight: 700;
            color: #000;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .project-details {
            font-size: 10pt;
            color: #333;
            font-weight: 500;
        }

        /* Table */
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            text-align: left;
            font-size: 9pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #000; /* Black headers */
            padding: 10px 5px;
            border-bottom: 2px solid #000;
        }
        .items-table td {
            padding: 10px 5px;
            border-bottom: 1px solid #ccc; /* Darker border */
            vertical-align: top;
            color: #111;
        }
        .items-table tr:last-child td {
            border-bottom: 1px solid #000;
        }
        
        /* Column Widths */
        .col-no { width: 5%; color: #333; font-weight: 600; }
        .col-desc { width: 50%; }
        .col-unit { width: 10%; text-align: center; color: #333; }
        .col-qty { width: 10%; text-align: center; color: #333; }
        .col-price { width: 12%; text-align: right; }
        .col-total { width: 13%; text-align: right; font-weight: 700; }

        /* Hierarchy */
        .item-parent {
            font-weight: 700;
            color: #000;
            background-color: #f9f9f9;
        }
        .item-child {
            color: #222;
        }
        .indent-1 { padding-left: 20px !important; }
        .indent-2 { padding-left: 40px !important; }
        .indent-3 { padding-left: 60px !important; }

        /* Totals */
        .totals-section {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 30px;
        }
        .totals-table {
            float: right;
            width: 40%;
        }
        .totals-table td {
            padding: 5px 0;
            text-align: right;
        }
        .total-label {
            color: #333;
            font-size: 10pt;
            font-weight: 600;
            padding-right: 15px;
        }
        .total-value {
            color: #000;
            font-weight: 700;
            font-size: 10pt;
        }
        .grand-total-row td {
            padding-top: 10px;
            border-top: 2px solid #000;
            font-size: 12pt;
            font-weight: 800;
            color: #000;
        }

        /* Terms & Conditions */
        .terms-section {
            margin-bottom: 40px;
            page-break-inside: avoid;
        }
        .terms-title {
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding-bottom: 2px;
        }
        .terms-list {
            list-style-type: disc;
            padding-left: 20px;
            font-size: 9pt;
            color: #222;
        }
        .terms-list li {
            margin-bottom: 4px;
        }

        /* Footer / Signature */
        .footer {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-section {
            width: 100%;
        }
        .signature-block {
            float: right;
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-top: 60px;
            margin-bottom: 5px;
        }
        .signature-name {
            font-size: 10pt;
            font-weight: 700;
            color: #000;
        }
        .signature-title {
            font-size: 9pt;
            color: #333;
        }

        /* Utils */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header clearfix">
        <div class="header-left">
            <img src="{{ public_path('images/logo.png') }}" style="height: 80px; margin-bottom: 10px;">
            <div class="company-name">PT Sketsaa Artama Indonesia</div>
            <div class="company-details">
                Jl. Tubagus Ismail Indah no. 11 Bandung<br>
                (022) 12345678 &bull; info@sketsaa.co.id
            </div>
        </div>
        <div class="header-right">
            <div style="font-size: 16pt; font-weight: 800; color: #000; margin-bottom: 5px;">RENCANA ANGGARAN BIAYA</div>
            <div class="quote-date">{{ \Carbon\Carbon::parse($quotation->date)->format('d F Y') }}</div>
        </div>
    </div>

    <!-- Project Info -->
    <div class="info-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 80px; font-weight: 600; color: #333;">Project:</td>
                <td style="font-weight: 700; color: #000; text-transform: uppercase;">{{ $quotation->project_name }}</td>
            </tr>
            <tr>
                <td style="width: 80px; font-weight: 600; color: #333;">Lokasi:</td>
                <td style="color: #000;">{{ $quotation->location }}</td>
            </tr>
            <tr>
                <td style="width: 80px; font-weight: 600; color: #333;">Tahun:</td>
                <td style="color: #000;">{{ \Carbon\Carbon::parse($quotation->date)->format('Y') }}</td>
            </tr>
        </table>
    </div>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-desc">Description</th>
                <th class="col-unit">Unit</th>
                <th class="col-qty">Qty</th>
                <th class="col-price">Price</th>
                <th class="col-total">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $counter = 1;
                // $rootItems is passed from the controller, already sorted and filtered
            @endphp

            @foreach($rootItems as $item)
                @include('quotations.partials.pdf_item_row', ['item' => $item, 'level' => 0, 'number' => $counter++])
            @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section clearfix">
        <table class="totals-table">
            <tr>
                <td class="total-label">Subtotal</td>
                <td class="total-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total-row">
                <td class="total-label" style="color: #000;">TOTAL</td>
                <td class="total-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Terms & Conditions -->
    <div class="terms-section">
        <div class="terms-title">Terms & Conditions</div>
        <ul class="terms-list">
            <li>Harga sudah termasuk jasa dan material.</li>
            <li>Pembayaran: DP 50%, Progress 40%, Retensi 10%.</li>
            <li>Masa berlaku penawaran 30 hari sejak tanggal diterbitkan.</li>
            <li>Harga belum termasuk PPN 11% (kecuali disebutkan lain).</li>
            <li>Perubahan spesifikasi atau volume pekerjaan akan diperhitungkan sebagai pekerjaan tambah/kurang.</li>
        </ul>
    </div>

    <!-- Footer / Signature -->
    <div class="footer clearfix">
        <div class="signature-section clearfix">
            <div class="signature-block">
                <div style="margin-bottom: 10px; font-size: 9pt; color: #333;">Hormat Kami,</div>
                <div class="signature-line"></div>
                <div class="signature-name">PT Sketsaa Artama Indonesia</div>
            </div>
        </div>
    </div>
</body>
</html>
