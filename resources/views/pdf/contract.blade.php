<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $document->name ?? 'Huurcontract' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #1a1a1a;
            padding: 40px 50px;
        }

        h1 { font-size: 16pt; font-weight: bold; margin-bottom: 4px; }
        h2 { font-size: 11pt; font-weight: bold; margin: 20px 0 8px; border-bottom: 1px solid #ccc; padding-bottom: 3px; color: #333; }
        h3 { font-size: 10pt; font-weight: bold; margin: 12px 0 4px; }

        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a1a1a; padding-bottom: 16px; }
        .header .subtitle { font-size: 9pt; color: #666; margin-top: 2px; }
        .header .ref { font-size: 8pt; color: #999; margin-top: 6px; }

        .two-col { width: 100%; }
        .two-col td { width: 50%; vertical-align: top; padding-right: 20px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .info-table td { padding: 3px 0; }
        .info-table td:first-child { font-weight: bold; width: 38%; color: #444; }

        .highlight-box {
            background: #f5f5f5;
            border-left: 3px solid #333;
            padding: 10px 14px;
            margin: 10px 0;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        .status-draft    { background: #fef3c7; color: #92400e; }
        .status-signed   { background: #d1fae5; color: #065f46; }
        .status-archived { background: #f3f4f6; color: #6b7280; }

        .legal-text {
            font-size: 8.5pt;
            color: #555;
            background: #fafafa;
            border: 1px solid #e5e5e5;
            padding: 10px 14px;
            margin-top: 8px;
        }

        .signature-box {
            border: 1px solid #ccc;
            padding: 12px;
            margin-top: 8px;
            min-height: 60px;
        }
        .signature-line {
            border-bottom: 1px solid #999;
            margin-top: 30px;
            margin-bottom: 4px;
        }
        .signature-label { font-size: 8pt; color: #666; }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #999;
            text-align: center;
        }

        p { margin-bottom: 6px; }
    </style>
</head>
<body>

@php
    $partijen  = $blocks['partijen']  ?? [];
    $goed      = $blocks['goed']      ?? [];
    $huurperiode = $blocks['huurperiode'] ?? [];
    $financieel  = $blocks['financieel']  ?? [];
    $bijzonder   = $blocks['bijzondere_voorwaarden'] ?? null;
    $wettelijk   = $blocks['wettelijk'] ?? [];
    $ondertekening      = $blocks['ondertekening'] ?? [];
    $handtekeningen     = collect($ondertekening['handtekeningen'] ?? []);
    $verhuurderTekening = $handtekeningen->firstWhere('is_verhuurder', true);

    $verhuurder = $partijen['verhuurder'] ?? [];
    $huurders   = $partijen['huurders']   ?? [];

    $statusLabel = match($document->status) {
        'signed'   => ['label' => 'Ondertekend',           'class' => 'status-signed'],
        'archived' => ['label' => 'Gearchiveerd',          'class' => 'status-archived'],
        default    => ['label' => 'Wacht op ondertekening','class' => 'status-draft'],
    };
@endphp

{{-- ── HEADER ──────────────────────────────────────────────────────────────── --}}
<div class="header">
    <h1>Studentenhuurovereenkomst</h1>
    <div class="subtitle">Conform de Vlaamse Codex Wonen 2021 — Boek 3</div>
    <div class="ref">
        {{ $document->name }}
        &nbsp;·&nbsp;
        Aangemaakt op {{ \Carbon\Carbon::parse($ondertekening['aangemaakt_op'] ?? $document->created_at)->format('d/m/Y') }}
        &nbsp;·&nbsp;
        <span class="status-badge {{ $statusLabel['class'] }}">{{ $statusLabel['label'] }}</span>
    </div>
</div>

{{-- ── PARTIJEN ─────────────────────────────────────────────────────────────── --}}
<h2>1. Partijen</h2>

<table class="two-col">
    <tr>
        <td>
            <h3>Verhuurder</h3>
            <table class="info-table">
                <tr><td>Naam</td><td>{{ $verhuurder['naam'] ?? '—' }}</td></tr>
                <tr><td>E-mail</td><td>{{ $verhuurder['email'] ?? '—' }}</td></tr>
                @if(!empty($verhuurder['tel']))
                    <tr><td>Telefoon</td><td>{{ $verhuurder['tel'] }}</td></tr>
                @endif
            </table>
        </td>
        <td>
            <h3>Huurder(s)</h3>
            @forelse($huurders as $huurder)
                <table class="info-table" style="margin-bottom:8px">
                    <tr>
                        <td>Naam</td>
                        <td>
                            {{ $huurder['naam'] ?? '—' }}
                            @if(!empty($huurder['is_primary'])) <em style="font-size:8pt;color:#666">(hoofdhuurder)</em> @endif
                        </td>
                    </tr>
                    <tr><td>E-mail</td><td>{{ $huurder['email'] ?? '—' }}</td></tr>
                    @if(!empty($huurder['tel']))
                        <tr><td>Telefoon</td><td>{{ $huurder['tel'] }}</td></tr>
                    @endif
                </table>
            @empty
                <p style="color:#999">Geen huurder opgegeven.</p>
            @endforelse
        </td>
    </tr>
</table>

{{-- ── GOED ─────────────────────────────────────────────────────────────────── --}}
<h2>2. Het gehuurde goed</h2>

<table class="info-table">
    <tr><td>Adres</td><td>{{ $goed['adres'] ?? '—' }}</td></tr>
    <tr><td>Kamer</td><td>{{ $goed['kamer'] ?? '—' }}</td></tr>
    @if(!empty($goed['type']))
        <tr><td>Type</td><td>{{ ucfirst($goed['type']) }}</td></tr>
    @endif
    @if(!empty($goed['oppervlakte']))
        <tr><td>Oppervlakte</td><td>{{ $goed['oppervlakte'] }} m²</td></tr>
    @endif
    <tr><td>Gemeubeld</td><td>{{ ($goed['gemeubeld'] ?? false) ? 'Ja' : 'Nee' }}</td></tr>
</table>

{{-- ── HUURPERIODE ─────────────────────────────────────────────────────────── --}}
<h2>3. Huurperiode</h2>

<div class="highlight-box">
    <table class="info-table">
        @if(!empty($huurperiode['duur_maanden']))
            <tr><td>Duur</td><td>{{ $huurperiode['duur_maanden'] }} maanden</td></tr>
        @endif
        <tr><td>Startdatum</td><td>{{ $huurperiode['start'] ? \Carbon\Carbon::parse($huurperiode['start'])->format('d/m/Y') : '—' }}</td></tr>
        <tr><td>Einddatum</td><td>{{ $huurperiode['einde'] ? \Carbon\Carbon::parse($huurperiode['einde'])->format('d/m/Y') : 'Onbepaalde duur' }}</td></tr>
    </table>
</div>

<p style="font-size:9pt;color:#555;margin-top:6px">
    Deze overeenkomst neemt van rechtswege een einde op de einddatum, zonder dat opzegging vereist is.
    Bij stilzwijgende voortzetting wordt de overeenkomst omgezet naar een huurovereenkomst van onbepaalde duur.
</p>

{{-- ── FINANCIEEL ──────────────────────────────────────────────────────────── --}}
<h2>4. Financiële bepalingen</h2>

<table class="info-table">
    <tr>
        <td>Maandelijkse huurprijs</td>
        <td>€ {{ number_format((float)($financieel['huurprijs'] ?? 0), 2, ',', '.') }}</td>
    </tr>
    <tr>
        <td>Borgsom</td>
        <td>€ {{ number_format((float)($financieel['borgsom'] ?? 0), 2, ',', '.') }}
            <span style="font-size:8.5pt;color:#666">(max. 2 maanden huur — art. 5.33 VCW)</span>
        </td>
    </tr>
</table>

<p style="font-size:9pt;color:#555;margin-top:6px">
    De huur is maandelijks vooraf betaalbaar. De borgsom wordt gestort op een geblokkeerde rekening op naam van de huurder
    (art. 5.34 Vlaamse Codex Wonen 2021).
</p>

{{-- ── BIJZONDERE VOORWAARDEN ─────────────────────────────────────────────── --}}
@if(!empty($bijzonder))
    <h2>5. Bijzondere voorwaarden</h2>
    <p>{{ $bijzonder }}</p>
@endif

{{-- ── WETTELIJKE BEPALINGEN ──────────────────────────────────────────────── --}}
<h2>{{ !empty($bijzonder) ? '6' : '5' }}. Wettelijke bepalingen</h2>

<div class="legal-text">
    <p><strong>Toepasselijk recht:</strong> {{ $wettelijk['toepasselijk_recht'] ?? 'Vlaamse Codex Wonen 2021 — Studentenhuurovereenkomst' }}</p>
    <br>
    <p>
        De huurder heeft recht op het rustig genot van het gehuurde goed (art. 5.64 VCW).
        Verhuurder staat in voor gebreken die het normale gebruik verhinderen.
        Plaatsbeschrijving bij intrede en uittrede is verplicht (art. 5.28 VCW).
        Dit contract dient geregistreerd te worden binnen 2 maanden na ondertekening door de verhuurder (art. 5.17 VCW).
    </p>
</div>

{{-- ── ONDERTEKENING ───────────────────────────────────────────────────────── --}}
<h2>{{ !empty($bijzonder) ? '7' : '6' }}. Ondertekening</h2>

<p style="font-size:9pt;color:#555;margin-bottom:12px">
    Opgemaakt in twee originele exemplaren, waarvan elke partij erkent er één te hebben ontvangen.
</p>

<table class="two-col">
    <tr>
        <td>
            <p><strong>Verhuurder</strong><br>
            {{ $verhuurder['naam'] ?? '—' }}</p>
            @if($verhuurderTekening)
                <p style="color:#065f46;font-size:9pt">
                    ✓ Ondertekend op {{ \Carbon\Carbon::parse($verhuurderTekening['signed_at'])->format('d/m/Y') }}
                </p>
            @else
                <div class="signature-line"></div>
                <div class="signature-label">Handtekening &amp; datum</div>
            @endif
        </td>
        <td>
            @foreach($huurders as $huurder)
                @php
                    $huurderTekening = $handtekeningen
                        ->where('is_verhuurder', false)
                        ->firstWhere('user_id', $huurder['user_id'] ?? null);
                @endphp
                <p><strong>{{ empty($huurder['is_primary']) ? 'Medehuurder' : 'Huurder' }}</strong><br>
                {{ $huurder['naam'] ?? '—' }}</p>
                @if($huurderTekening)
                    <p style="color:#065f46;font-size:9pt">
                        ✓ Ondertekend op {{ \Carbon\Carbon::parse($huurderTekening['signed_at'])->format('d/m/Y') }}
                    </p>
                @else
                    <div class="signature-line"></div>
                    <div class="signature-label">Handtekening &amp; datum</div>
                @endif
                <br>
            @endforeach
        </td>
    </tr>
</table>

{{-- ── FOOTER ──────────────────────────────────────────────────────────────── --}}
<div class="footer">
    KotKompas &nbsp;·&nbsp; {{ config('app.url') }} &nbsp;·&nbsp;
    Gegenereerd op {{ now()->format('d/m/Y \o\m H:i') }}
</div>

</body>
</html>
