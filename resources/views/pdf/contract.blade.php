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
            font-size: 9.5pt;
            line-height: 1.55;
            color: #1c1c1e;
            background: #fff;
        }

        /* ── PAGE MARGINS via wrapper ── */
        .page {
            padding: 0 52px 40px 52px;
        }

        /* ── HEADER BAND ── */
        .header-band {
            background: #1a2744;
            margin: 0 0 0 0;
            padding: 22px 52px 18px 52px;
        }
        .header-band-top {
            width: 100%;
        }
        .header-band-top td { vertical-align: middle; }
        .brand {
            font-size: 13pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.5px;
        }
        .brand-sub {
            font-size: 7.5pt;
            color: #94a3c0;
            margin-top: 1px;
        }
        .contract-title {
            text-align: right;
        }
        .contract-title h1 {
            font-size: 13pt;
            font-weight: bold;
            color: #ffffff;
            letter-spacing: 0.3px;
        }
        .contract-title .subtitle {
            font-size: 7.5pt;
            color: #94a3c0;
            margin-top: 2px;
        }

        .header-meta {
            background: #243056;
            padding: 9px 52px;
            width: 100%;
        }
        .header-meta td {
            font-size: 7.5pt;
            color: #b0bcd4;
            padding: 0 24px 0 0;
        }
        .header-meta td strong { color: #dce5f5; }

        /* ── STATUS BADGE ── */
        .status-draft    { background: #fef3c7; color: #92400e; padding: 2px 7px; font-size: 7.5pt; font-weight: bold; }
        .status-signed   { background: #d1fae5; color: #065f46; padding: 2px 7px; font-size: 7.5pt; font-weight: bold; }
        .status-archived { background: #f1f5f9; color: #64748b; padding: 2px 7px; font-size: 7.5pt; font-weight: bold; }

        /* ── SECTION HEADINGS ── */
        .section {
            margin-top: 18px;
        }
        .section-heading {
            background: #f1f4f9;
            border-left: 3px solid #1a2744;
            padding: 5px 10px;
            font-size: 9.5pt;
            font-weight: bold;
            color: #1a2744;
            margin-bottom: 10px;
        }

        /* ── PARTY CARDS ── */
        .party-table { width: 100%; border-collapse: collapse; }
        .party-table td { width: 50%; vertical-align: top; padding-right: 12px; }
        .party-table td:last-child { padding-right: 0; padding-left: 12px; }

        .party-card {
            border: 1px solid #dde3ed;
            padding: 10px 12px;
            background: #fafbfd;
        }
        .party-card .party-role {
            font-size: 7pt;
            font-weight: bold;
            color: #1a2744;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 6px;
            border-bottom: 1px solid #dde3ed;
            padding-bottom: 4px;
        }

        /* ── DATA ROWS ── */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .data-table tr { border-bottom: 1px solid #f0f2f5; }
        .data-table tr:last-child { border-bottom: none; }
        .data-table td { padding: 3.5px 0; font-size: 9pt; }
        .data-table td.label { color: #6b7280; width: 38%; font-size: 8.5pt; }
        .data-table td.value { color: #1c1c1e; font-weight: bold; }

        /* ── HIGHLIGHT BOX ── */
        .highlight-box {
            background: #f0f4ff;
            border: 1px solid #c7d5f0;
            border-left: 3px solid #1a2744;
            padding: 10px 14px;
            margin-bottom: 8px;
        }
        .highlight-box .data-table td.label { color: #4b5c80; }
        .highlight-box .data-table td.value { color: #1a2744; }

        /* ── FINANCIAL TABLE ── */
        .finance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .finance-table thead tr {
            background: #1a2744;
        }
        .finance-table thead td {
            color: #fff;
            font-size: 8pt;
            font-weight: bold;
            padding: 5px 10px;
        }
        .finance-table tbody tr { border-bottom: 1px solid #e8ecf3; }
        .finance-table tbody tr:last-child { border-bottom: none; }
        .finance-table tbody td {
            padding: 5px 10px;
            font-size: 9pt;
            color: #1c1c1e;
        }
        .finance-table tbody tr:nth-child(even) { background: #f7f9fc; }
        .finance-table .amount { font-weight: bold; text-align: right; }
        .finance-table .note { font-size: 7.5pt; color: #6b7280; }

        /* ── LEGAL TEXT ── */
        .legal-block {
            background: #fafafa;
            border: 1px solid #e5e7eb;
            padding: 10px 14px;
            margin-bottom: 6px;
        }
        .legal-article {
            margin-bottom: 7px;
        }
        .legal-article .art-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1a2744;
            margin-bottom: 2px;
        }
        .legal-article p {
            font-size: 8.5pt;
            color: #444;
            line-height: 1.5;
        }

        /* ── SIGNATURE SECTION ── */
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 50%; vertical-align: top; padding-right: 12px; }
        .sig-table td:last-child { padding-right: 0; padding-left: 12px; }

        .sig-block {
            border: 1px solid #dde3ed;
            padding: 12px 14px 10px;
            background: #fafbfd;
            min-height: 80px;
        }
        .sig-block .sig-role {
            font-size: 7pt;
            font-weight: bold;
            color: #1a2744;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #dde3ed;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }
        .sig-block .sig-name { font-size: 9pt; font-weight: bold; color: #1c1c1e; }
        .sig-block .sig-email { font-size: 7.5pt; color: #6b7280; margin-top: 1px; margin-bottom: 10px; }
        .sig-line { border-bottom: 1px dashed #aab0be; margin: 18px 0 3px; }
        .sig-label { font-size: 7pt; color: #9ca3af; }
        .sig-done {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: 8pt;
            color: #065f46;
        }
        .sig-done .check { font-size: 10pt; }

        /* ── BIJZONDERE VOORWAARDEN ── */
        .special-box {
            border: 1px solid #fde68a;
            background: #fffbeb;
            border-left: 3px solid #f59e0b;
            padding: 10px 14px;
            font-size: 9pt;
            color: #1c1c1e;
            line-height: 1.55;
        }

        /* ── FOOTER ── */
        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e2e6ef;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
        }
        .footer strong { color: #6b7280; }

        /* ── WATERMARK (draft) ── */
        .watermark {
            position: fixed;
            top: 38%;
            left: 5%;
            width: 90%;
            text-align: center;
            font-size: 62pt;
            font-weight: bold;
            color: rgba(200, 210, 230, 0.18);
            transform: rotate(-30deg);
            z-index: -1;
            letter-spacing: 8px;
        }

        p { margin-bottom: 5px; }
    </style>
</head>
<body>

@php
    $partijen       = $blocks['partijen']  ?? [];
    $goed           = $blocks['goed']      ?? [];
    $huurperiode    = $blocks['huurperiode'] ?? [];
    $financieel     = $blocks['financieel']  ?? [];
    $bijzonder      = $blocks['bijzondere_voorwaarden'] ?? null;
    $wettelijk      = $blocks['wettelijk'] ?? [];
    $ondertekening      = $blocks['ondertekening'] ?? [];
    $handtekeningen     = collect($ondertekening['handtekeningen'] ?? []);
    $verhuurderTekening = $handtekeningen->firstWhere('is_verhuurder', true);

    $verhuurder = $partijen['verhuurder'] ?? [];
    $huurders   = $partijen['huurders']   ?? [];

    $refNr = strtoupper(substr(md5($document->id . $document->created_at), 0, 8));

    $statusLabel = match($document->status) {
        'signed'   => ['label' => 'Ondertekend',            'class' => 'status-signed'],
        'archived' => ['label' => 'Gearchiveerd',           'class' => 'status-archived'],
        default    => ['label' => 'Wacht op ondertekening', 'class' => 'status-draft'],
    };

    $sectionNr = 1;
@endphp

@if($document->status !== 'signed')
<div class="watermark">CONCEPT</div>
@endif

{{-- ── HEADER ──────────────────────────────────────────────────────────────── --}}
<div class="header-band">
    <table class="header-band-top" cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:50%">
                <div class="brand">KotKompas</div>
                <div class="brand-sub">Studentenhuurplatform · België</div>
            </td>
            <td class="contract-title" style="width:50%">
                <h1>Studentenhuurovereenkomst</h1>
                <div class="subtitle">Conform Vlaamse Codex Wonen 2021 — Boek 3, Titel 2</div>
            </td>
        </tr>
    </table>
</div>

<table class="header-meta" cellspacing="0" cellpadding="0">
    <tr>
        <td><strong>Referentie</strong> KK-{{ $refNr }}</td>
        <td><strong>Document</strong> {{ $document->name ?? 'Huurcontract' }}</td>
        <td><strong>Opgemaakt op</strong> {{ \Carbon\Carbon::parse($ondertekening['aangemaakt_op'] ?? $document->created_at)->format('d/m/Y') }}</td>
        <td style="text-align:right">
            <span class="{{ $statusLabel['class'] }}">{{ $statusLabel['label'] }}</span>
        </td>
    </tr>
</table>

<div class="page">

{{-- ── PARTIJEN ─────────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Partijen</div>
    <table class="party-table" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="party-card">
                    <div class="party-role">Verhuurder</div>
                    <table class="data-table">
                        <tr><td class="label">Naam</td><td class="value">{{ $verhuurder['naam'] ?? '—' }}</td></tr>
                        <tr><td class="label">E-mail</td><td class="value">{{ $verhuurder['email'] ?? '—' }}</td></tr>
                        @if(!empty($verhuurder['tel']))
                        <tr><td class="label">Telefoon</td><td class="value">{{ $verhuurder['tel'] }}</td></tr>
                        @endif
                    </table>
                </div>
            </td>
            <td>
                @forelse($huurders as $idx => $huurder)
                <div class="party-card" style="{{ $idx > 0 ? 'margin-top:8px' : '' }}">
                    <div class="party-role">
                        {{ empty($huurder['is_primary']) ? 'Medehuurder' : 'Hoofdhuurder' }}
                    </div>
                    <table class="data-table">
                        <tr><td class="label">Naam</td><td class="value">{{ $huurder['naam'] ?? '—' }}</td></tr>
                        <tr><td class="label">E-mail</td><td class="value">{{ $huurder['email'] ?? '—' }}</td></tr>
                        @if(!empty($huurder['tel']))
                        <tr><td class="label">Telefoon</td><td class="value">{{ $huurder['tel'] }}</td></tr>
                        @endif
                    </table>
                </div>
                @empty
                <p style="color:#9ca3af;font-size:8.5pt">Geen huurder opgegeven.</p>
                @endforelse
            </td>
        </tr>
    </table>
</div>

{{-- ── GOED ─────────────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Het verhuurde goed</div>
    <table class="data-table">
        <tr><td class="label">Adres</td><td class="value">{{ $goed['adres'] ?? '—' }}</td></tr>
        <tr><td class="label">Kamer / eenheid</td><td class="value">{{ $goed['kamer'] ?? '—' }}</td></tr>
        @if(!empty($goed['type']))
        <tr><td class="label">Type woning</td><td class="value">{{ ucfirst($goed['type']) }}</td></tr>
        @endif
        @if(!empty($goed['oppervlakte']))
        <tr><td class="label">Bewoonbare opp.</td><td class="value">{{ $goed['oppervlakte'] }} m²</td></tr>
        @endif
        <tr><td class="label">Gemeubeld</td><td class="value">{{ ($goed['gemeubeld'] ?? false) ? 'Ja — inclusief meubelen en basisuitrusting' : 'Nee' }}</td></tr>
    </table>
    <p style="font-size:8pt;color:#6b7280;margin-top:6px">
        De verhuurder verklaart dat het goed voldoet aan de elementaire vereisten van veiligheid, gezondheid en
        woonkwaliteit zoals bepaald in art. 5.32 van de Vlaamse Codex Wonen 2021.
    </p>
</div>

{{-- ── HUURPERIODE ─────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Huurperiode en duurtijd</div>
    <div class="highlight-box">
        <table class="data-table">
            @if(!empty($huurperiode['duur_maanden']))
            <tr><td class="label">Duurtijd</td><td class="value">{{ $huurperiode['duur_maanden'] }} maanden (studentenhuurovereenkomst)</td></tr>
            @endif
            <tr>
                <td class="label">Ingangsdatum</td>
                <td class="value">{{ $huurperiode['start'] ? \Carbon\Carbon::parse($huurperiode['start'])->translatedFormat('d F Y') : '—' }}</td>
            </tr>
            <tr>
                <td class="label">Einddatum</td>
                <td class="value">
                    {{ $huurperiode['einde'] ? \Carbon\Carbon::parse($huurperiode['einde'])->translatedFormat('d F Y') : 'Onbepaalde duur' }}
                </td>
            </tr>
        </table>
    </div>
    <p style="font-size:8pt;color:#6b7280">
        Deze overeenkomst neemt van rechtswege een einde op de einddatum zonder dat opzegging vereist is
        (art. 5.93 VCW). Bij stilzwijgende voortzetting wordt de overeenkomst omgezet naar onbepaalde duur.
        Vervroegde beëindiging door de huurder is mogelijk mits drie maanden opzegging en een schadevergoeding
        gelijk aan één maand huur, te verminderen naar rato van de resterende duur (art. 5.94 VCW).
    </p>
</div>

{{-- ── FINANCIEEL ──────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Financiële bepalingen</div>
    <table class="finance-table" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <td style="width:55%">Omschrijving</td>
                <td style="width:25%">Bedrag</td>
                <td style="width:20%">Referentie</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Maandelijkse huurprijs</td>
                <td class="amount">€ {{ number_format((float)($financieel['huurprijs'] ?? 0), 2, ',', '.') }}</td>
                <td class="note">maandelijks vooraf</td>
            </tr>
            @php
                $totaalMaandelijks = (float)($financieel['totaal_maandelijks'] ?? 0);
                $huurprijs = (float)($financieel['huurprijs'] ?? 0);
            @endphp
            @if ($totaalMaandelijks > $huurprijs)
            <tr>
                <td>
                    Vaste kosten
                    <div class="note">Maandelijks inbegrepen in totaalprijs</div>
                </td>
                <td class="amount">€ {{ number_format($totaalMaandelijks - $huurprijs, 2, ',', '.') }}</td>
                <td class="note">maandelijks vooraf</td>
            </tr>
            <tr>
                <td><strong>Totaal maandelijks</strong></td>
                <td class="amount"><strong>€ {{ number_format($totaalMaandelijks, 2, ',', '.') }}</strong></td>
                <td class="note">maandelijks vooraf</td>
            </tr>
            @endif
            <tr>
                <td>
                    Borgsom
                    <div class="note">Max. 2 maanden huur · geblokkeerde rekening op naam huurder</div>
                </td>
                <td class="amount">€ {{ number_format((float)($financieel['borgsom'] ?? 0), 2, ',', '.') }}</td>
                <td class="note">art. 5.33–5.34 VCW</td>
            </tr>
        </tbody>
    </table>
    <p style="font-size:8pt;color:#6b7280;margin-top:6px">
        De huurprijs is betaalbaar op de eerste dag van elke maand via overschrijving op het rekeningnummer
        van de verhuurder. Indexering van de huurprijs is van toepassing conform art. 5.53 VCW op basis van
        het gezondheidsindexcijfer.
    </p>
</div>

{{-- ── BIJZONDERE VOORWAARDEN ─────────────────────────────────────────────── --}}
@if(!empty($bijzonder))
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Bijzondere voorwaarden</div>
    <div class="special-box">{{ $bijzonder }}</div>
</div>
@endif

{{-- ── WETTELIJKE BEPALINGEN ──────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Wettelijke bepalingen en verplichtingen</div>
    <div class="legal-block">
        <div class="legal-article">
            <div class="art-title">Staat van het goed (art. 5.28 VCW)</div>
            <p>Een gedetailleerde plaatsbeschrijving wordt opgesteld bij aanvang en bij beëindiging van de huurovereenkomst,
            op tegensprekelijke wijze en op kosten van beide partijen samen.</p>
        </div>
        <div class="legal-article">
            <div class="art-title">Herstellingen en onderhoud (art. 5.43–5.44 VCW)</div>
            <p>De huurder staat in voor de kleine herstellingen en het dagelijks onderhoud. De verhuurder staat in voor de
            grote herstellingen en gebreken die het normale gebruik verhinderen of de veiligheid in het gedrang brengen.</p>
        </div>
        <div class="legal-article">
            <div class="art-title">Rustig genot en bestemming (art. 5.64 VCW)</div>
            <p>De huurder heeft recht op het rustig genot van het gehuurde goed en zal dit uitsluitend aanwenden als
            studentenwoning. Onderverhuring en overdracht van huur zijn verboden zonder schriftelijk akkoord van de verhuurder.</p>
        </div>
        <div class="legal-article">
            <div class="art-title">Registratie (art. 5.17 VCW)</div>
            <p>De verhuurder is verplicht deze overeenkomst te laten registreren binnen twee maanden na ondertekening.
            De registratiekosten zijn ten laste van de verhuurder.</p>
        </div>
        <div class="legal-article" style="margin-bottom:0">
            <div class="art-title">Toepasselijk recht</div>
            <p>{{ $wettelijk['toepasselijk_recht'] ?? 'Vlaamse Codex Wonen 2021 — Studentenhuurovereenkomst (Boek 3, Titel 2)' }}.
            Bij geschillen is de vrederechter van de gerechtelijke afdeling waar het gehuurde goed gelegen is bevoegd.</p>
        </div>
    </div>
</div>

{{-- ── ONDERTEKENING ───────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading">Artikel {{ $sectionNr++ }}. &nbsp; Ondertekening</div>
    <p style="font-size:8.5pt;color:#374151;margin-bottom:12px">
        Opgemaakt te {{ $goed['gemeente'] ?? ($goed['adres'] ?? '—') }}, in zoveel originele exemplaren als er partijen zijn,
        waarbij iedere partij erkent één origineel exemplaar te hebben ontvangen.
        Door ondertekening verklaren alle partijen de inhoud van deze overeenkomst te hebben gelezen en te aanvaarden.
    </p>

    <table class="sig-table" cellspacing="0" cellpadding="0">
        <tr>
            <td style="vertical-align:top">
                <div class="sig-block">
                    <div class="sig-role">Verhuurder</div>
                    <div class="sig-name">{{ $verhuurder['naam'] ?? '—' }}</div>
                    <div class="sig-email">{{ $verhuurder['email'] ?? '' }}</div>
                    @if($verhuurderTekening)
                        <div class="sig-done">
                            <span class="check">✓</span>
                            Digitaal ondertekend op<br>
                            <strong>{{ \Carbon\Carbon::parse($verhuurderTekening['signed_at'])->translatedFormat('d F Y \o\m H:i') }}</strong>
                        </div>
                    @else
                        <div class="sig-line"></div>
                        <div class="sig-label">Handtekening &amp; datum</div>
                    @endif
                </div>
            </td>
            <td style="vertical-align:top">
                @foreach($huurders as $huurder)
                    @php
                        $huurderTekening = $handtekeningen
                            ->where('is_verhuurder', '!=', true)
                            ->firstWhere('user_id', $huurder['user_id'] ?? null);
                    @endphp
                    <div class="sig-block" style="{{ !$loop->first ? 'margin-top:8px' : '' }}">
                        <div class="sig-role">{{ empty($huurder['is_primary']) ? 'Medehuurder' : 'Hoofdhuurder' }}</div>
                        <div class="sig-name">{{ $huurder['naam'] ?? '—' }}</div>
                        <div class="sig-email">{{ $huurder['email'] ?? '' }}</div>
                        @if($huurderTekening)
                            <div class="sig-done">
                                <span class="check">✓</span>
                                Digitaal ondertekend op<br>
                                <strong>{{ \Carbon\Carbon::parse($huurderTekening['signed_at'])->translatedFormat('d F Y \o\m H:i') }}</strong>
                            </div>
                        @else
                            <div class="sig-line"></div>
                            <div class="sig-label">Handtekening &amp; datum</div>
                        @endif
                    </div>
                @endforeach
            </td>
        </tr>
    </table>
</div>

{{-- ── FOOTER ──────────────────────────────────────────────────────────────── --}}
<div class="footer">
    <strong>KotKompas</strong> &nbsp;·&nbsp; {{ config('app.url') }} &nbsp;·&nbsp;
    Ref. KK-{{ $refNr }} &nbsp;·&nbsp;
    Gegenereerd op {{ now()->format('d/m/Y \o\m H:i') }} &nbsp;·&nbsp;
    Dit document heeft juridische waarde conform de Vlaamse Codex Wonen 2021
</div>

</div>{{-- .page --}}
</body>
</html>
