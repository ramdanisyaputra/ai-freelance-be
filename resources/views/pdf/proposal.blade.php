<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $proposal->title ?? 'Proposal' }}</title>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.5;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #666;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1a202c;
            border-left: 4px solid #3182ce;
            padding-left: 10px;
        }

        .content {
            font-size: 14px;
        }

        .scope-item {
            margin-bottom: 5px;
        }

        .price-box {
            background-color: #f7fafc;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            text-align: right;
            page-break-inside: avoid;
        }

        .price-label {
            font-size: 14px;
            color: #718096;
        }

        .price-value {
            font-size: 20px;
            font-weight: bold;
            color: #2d3748;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            color: #1a202c;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        p {
            margin-bottom: 10px;
        }

        ul {
            list-style-type: disc;
            margin-left: 20px;
            margin-bottom: 10px;
        }

        ol {
            list-style-type: decimal;
            margin-left: 20px;
            margin-bottom: 10px;
        }

        li {
            margin-bottom: 5px;
        }

        blockquote {
            border-left: 4px solid #e2e8f0;
            padding-left: 15px;
            font-style: italic;
            color: #4a5568;
            margin: 10px 0;
        }

        img {
            max-width: 100%;
            height: auto;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f7fafc;
            font-weight: bold;
        }

        hr {
            border: 0;
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        /* Links */
        a {
            color: #3182ce;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $proposal->title ?? 'Proposal Proyek' }}</div>
        <div class="subtitle">Dibuat pada {{ $proposal->created_at->format('d F Y') }}</div>
    </div>

    @if ($proposal->summary)
        <div class="section">
            <div class="section-title">Ringkasan</div>
            <div class="content">{{ $proposal->summary }}</div>
        </div>
    @endif

    @if ($proposal->scope)
        <div class="section">
            <div class="section-title">Scope Pekerjaan</div>
            <div class="content">
                <ul>
                    @foreach ($proposal->scope ?? [] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="section">
        <div class="section-title">Detail Proposal</div>
        <div class="content">
            {!! $proposal->content !!}
        </div>
    </div>

    <div class="price-box">
        <div class="price-label">Estimasi Biaya</div>
        <div class="price-value">
            {{ 'IDR ' . number_format($proposal->price, 0, ',', '.') }}
        </div>
        <div class="price-label" style="font-size: 12px; margin-top: 5px;">
            Durasi: {{ $proposal->duration_days }} Hari
        </div>
    </div>
</body>

</html>
