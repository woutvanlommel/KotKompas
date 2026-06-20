<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $document->name ?? 'Huurcontract' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* Paginamarge voor ELKE pagina (ook vervolgpagina's).
           Let op: de '*'-reset hierboven nult de @page-marge in DomPDF,
           omdat @page enkel de startwaarde van de root-frame zet.
           Daarom zetten we de top-marge op 'html' (hogere specificiteit
           dan '*') — DomPDF gebruikt de root-marge op elke pagina.
           Links/rechts blijft via de 56px padding van de blokken. */
        @page { margin: 16mm 0 0 0; }
        html  { margin-top: 16mm; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9.5pt;
            line-height: 1.65;
            color: #000;
            background: #fff;
        }

        /* ── HEADER ── */
        .doc-header {
            padding: 0 56px 0 56px;
        }
        .doc-header table { width: 100%; }
        .doc-header td { vertical-align: top; }
        .brand { font-size: 10pt; font-weight: bold; letter-spacing: 0.2px; }
        .brand-sub { font-size: 7pt; color: #555; margin-top: 2px; }
        .doc-ref { text-align: right; font-size: 7.5pt; color: #333; line-height: 1.7; }
        .doc-ref strong { color: #000; }

        /* ── TITLE BLOCK ── */
        .title-block {
            text-align: center;
            padding: 20px 56px 18px 56px;
            border-top: 1.5pt solid #000;
            border-bottom: 1.5pt solid #000;
            margin-top: 14px;
        }
        .title-block h1 {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .title-block .doc-subtitle {
            font-size: 8pt;
            color: #333;
            margin-top: 4px;
            letter-spacing: 0.2px;
        }
        .title-block .doc-status {
            font-size: 7.5pt;
            color: #555;
            margin-top: 3px;
        }

        /* ── INTRO ── */
        .intro {
            padding: 14px 56px 0 56px;
            font-size: 9pt;
            color: #111;
            line-height: 1.65;
        }

        /* ── PAGE ── */
        .page { padding: 0 56px 40px 56px; margin-bottom: 28px; }

        /* ── SECTIONS ── */
        .section { margin-top: 22px; page-break-inside: avoid; }
        .section.allow-break { page-break-inside: auto; }
        .section-heading {
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 0.5pt solid #000;
            padding-bottom: 3px;
            margin-bottom: 10px;
        }
        .art-nr { color: #555; font-weight: normal; margin-right: 4px; }

        /* ── NOTE TEXT ── */
        .note-text { font-size: 8pt; color: #444; margin-top: 6px; line-height: 1.55; }

        /* ── PARTY BLOCKS ── */
        .party-block { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 0.5pt solid #ddd; }
        .party-block:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .party-role {
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #444;
            margin-bottom: 5px;
        }

        /* ── DATA ROWS ── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table tr { border-bottom: 0.5pt solid #e0e0e0; }
        .data-table tr:last-child { border-bottom: none; }
        .data-table td { padding: 3.5px 0; font-size: 9pt; }
        .data-table td.label { color: #444; width: 38%; font-size: 8.5pt; }
        .data-table td.value { color: #000; }

        /* ── FINANCIAL TABLE ── */
        .finance-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .finance-table thead tr { border-top: 1pt solid #000; border-bottom: 1pt solid #000; }
        .finance-table thead td { font-size: 8pt; font-weight: bold; padding: 5px 8px 5px 0; }
        .finance-table tbody tr { border-bottom: 0.5pt solid #ddd; }
        .finance-table tbody tr:last-child { border-bottom: 1pt solid #000; }
        .finance-table tbody td { padding: 5px 8px 5px 0; font-size: 9pt; }
        .finance-table .amount { text-align: right; padding-right: 16px; }
        .finance-table thead td.amount { padding-right: 16px; }
        .finance-table .ref { font-size: 7.5pt; color: #555; }
        .finance-table .sub { font-size: 7.5pt; color: #555; margin-top: 1px; }

        /* ── LEGAL ARTICLES ── */
        /* Titel + beschrijving van één artikel blijven samen op dezelfde pagina. */
        .legal-article { margin-bottom: 9px; page-break-inside: avoid; }
        /* Sectietitel blijft samen met het eerste artikel eronder. */
        .keep-with-next { page-break-inside: avoid; }
        .legal-article .art-title { font-size: 9pt; font-weight: bold; margin-bottom: 2px; }
        .legal-article p { font-size: 8.5pt; color: #111; line-height: 1.6; }

        /* ── BIJZONDERE VOORWAARDEN ── */
        .special-box { border-top: 0.5pt solid #000; border-bottom: 0.5pt solid #000; padding: 9px 0; font-size: 9pt; line-height: 1.6; }

        /* ── SIGNATURES ── */
        /* Niet meer geforceerd naar een nieuwe pagina; sluit aan bij de
           rest en springt enkel mee over als het blok niet meer past. */
        .sig-section { page-break-inside: avoid; }
        .sig-intro { font-size: 9pt; color: #111; margin-bottom: 20px; line-height: 1.65; }
        .sig-table { width: 100%; border-collapse: collapse; }
        .sig-table td { width: 50%; vertical-align: top; padding-right: 28px; }
        .sig-table td:last-child { padding-right: 0; padding-left: 28px; }
        .sig-block { padding: 4px 0; }
        .sig-role { font-size: 7pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.8px; color: #444; margin-bottom: 4px; }
        .sig-name { font-size: 9.5pt; font-weight: bold; }
        .sig-email { font-size: 7.5pt; color: #555; margin-top: 1px; }
        .sig-line { border-bottom: 0.5pt solid #000; margin: 36px 0 4px; }
        .sig-label { font-size: 7pt; color: #777; }
        .sig-done { border: 0.5pt solid #999; padding: 5px 8px; margin-top: 14px; font-size: 8pt; line-height: 1.5; }
        .sig-done .check { font-size: 9pt; }

        /* ── FOOTER ── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5px 56px 10px 56px;
            border-top: 0.5pt solid #000;
            text-align: center;
            font-size: 7pt;
            color: #555;
            background: #fff;
        }

        /* ── WATERMARK ── */
        .watermark {
            position: fixed; top: 38%; left: 5%; width: 90%;
            text-align: center; font-size: 72pt; font-weight: bold;
            color: rgba(0,0,0,0.04); transform: rotate(-30deg);
            z-index: -1; letter-spacing: 10px;
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
    $huurders   = collect($partijen['huurders'] ?? [])
        ->sortByDesc(fn($h) => !empty($h['is_primary']))
        ->values()
        ->all();

    $refNr = strtoupper(substr(md5($document->id . $document->created_at), 0, 8));

    $statusLabel = match($document->status) {
        'signed'   => 'Ondertekend',
        'archived' => 'Gearchiveerd',
        default    => 'Niet ondertekend',
    };

    $sectionNr = 1;

    $totaalMaandelijks = (float)($financieel['totaal_maandelijks'] ?? 0);
    $huurprijs = (float)($financieel['huurprijs'] ?? 0);
@endphp

@if($document->status !== 'signed')
<div class="watermark">CONCEPT</div>
@endif

{{-- ── HEADER ──────────────────────────────────────────────────────────────── --}}
<div class="doc-header">
    <table cellspacing="0" cellpadding="0">
        <tr>
            <td style="width:50%">
                <div class="brand">KotKompas</div>
                <div class="brand-sub">Studentenhuurplatform · België</div>
            </td>
            <td class="doc-ref" style="width:50%">
                <strong>Ref.</strong> KK-{{ $refNr }}<br>
                <strong>Opgemaakt op</strong> {{ \Carbon\Carbon::parse($ondertekening['aangemaakt_op'] ?? $document->created_at)->format('d/m/Y') }}<br>
                {{ $statusLabel }}
            </td>
        </tr>
    </table>
</div>

{{-- ── TITLE BLOCK ── --}}
<div class="title-block">
    <h1>Studentenhuurovereenkomst</h1>
    <div class="doc-subtitle">Conform de Vlaamse Codex Wonen 2021 — Boek 3, Titel 2 (Studentenhuur)</div>
    @if(!empty($document->name))
    <div class="doc-status">{{ $document->name }}</div>
    @endif
</div>

{{-- ── INTRO ── --}}
<div class="intro">
    <p>
        Tussen de ondergetekenden, hierna respectievelijk "verhuurder" en "huurder" genoemd,
        wordt de volgende studentenhuurovereenkomst gesloten, overeenkomstig de bepalingen van de
        Vlaamse Codex Wonen 2021.
    </p>
</div>

{{-- ── FOOTER (fixed — herhaalt op elke pagina) ───────────────────────────── --}}
<div class="footer">
    KotKompas &nbsp;·&nbsp; Ref. KK-{{ $refNr }} &nbsp;·&nbsp;
    Gegenereerd op {{ now()->format('d/m/Y \o\m H:i') }} &nbsp;·&nbsp;
    Conform de Vlaamse Codex Wonen 2021
</div>

<div class="page">

{{-- ── PARTIJEN ─────────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Partijen</div>

    <div class="party-block">
        <div class="party-role">Verhuurder</div>
        <table class="data-table">
            <tr><td class="label">Naam</td><td class="value">{{ $verhuurder['naam'] ?? '—' }}</td></tr>
            <tr><td class="label">E-mail</td><td class="value">{{ $verhuurder['email'] ?? '—' }}</td></tr>
            @if(!empty($verhuurder['tel']))
            <tr><td class="label">Telefoon</td><td class="value">{{ $verhuurder['tel'] }}</td></tr>
            @endif
        </table>
    </div>

    @forelse($huurders as $huurder)
    <div class="party-block">
        <div class="party-role">{{ empty($huurder['is_primary']) ? 'Medehuurder' : 'Hoofdhuurder' }}</div>
        <table class="data-table">
            <tr><td class="label">Naam</td><td class="value">{{ $huurder['naam'] ?? '—' }}</td></tr>
            <tr><td class="label">E-mail</td><td class="value">{{ $huurder['email'] ?? '—' }}</td></tr>
            @if(!empty($huurder['tel']))
            <tr><td class="label">Telefoon</td><td class="value">{{ $huurder['tel'] }}</td></tr>
            @endif
        </table>
    </div>
    @empty
    <p class="note-text">Geen huurder opgegeven.</p>
    @endforelse
</div>

{{-- ── GOED ─────────────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Het verhuurde goed</div>
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
    <p class="note-text">
        De verhuurder verklaart dat het goed voldoet aan de elementaire vereisten van veiligheid, gezondheid en
        woonkwaliteit zoals bepaald in art. 5.32 van de Vlaamse Codex Wonen 2021.
    </p>
</div>

{{-- ── HUURPERIODE ─────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Huurperiode en duurtijd</div>
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
            <td class="value">{{ $huurperiode['einde'] ? \Carbon\Carbon::parse($huurperiode['einde'])->translatedFormat('d F Y') : 'Onbepaalde duur' }}</td>
        </tr>
    </table>
    <p class="note-text">
        Deze overeenkomst neemt van rechtswege een einde op de einddatum zonder dat opzegging vereist is
        (art. 5.93 VCW). Bij stilzwijgende voortzetting wordt de overeenkomst omgezet naar onbepaalde duur.
        Vervroegde beëindiging door de huurder is mogelijk mits drie maanden opzegging en een schadevergoeding
        gelijk aan één maand huur, te verminderen naar rato van de resterende duur (art. 5.94 VCW).
    </p>
</div>

{{-- ── FINANCIEEL ──────────────────────────────────────────────────────────── --}}
<div class="section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Financiële bepalingen</div>
    <table class="finance-table" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <td style="width:58%">Omschrijving</td>
                <td style="width:22%" class="amount">Bedrag</td>
                <td style="width:20%" class="ref">Referentie</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Maandelijkse huurprijs</td>
                <td class="amount">€&nbsp;{{ number_format($huurprijs, 2, ',', '.') }}</td>
                <td class="ref">maandelijks vooraf</td>
            </tr>
            @if ($totaalMaandelijks > $huurprijs)
            <tr>
                <td>
                    Vaste kosten
                    <div class="sub">Maandelijks inbegrepen in totaalprijs</div>
                </td>
                <td class="amount">€&nbsp;{{ number_format($totaalMaandelijks - $huurprijs, 2, ',', '.') }}</td>
                <td class="ref">maandelijks vooraf</td>
            </tr>
            <tr>
                <td><strong>Totaal maandelijks te betalen</strong></td>
                <td class="amount"><strong>€&nbsp;{{ number_format($totaalMaandelijks, 2, ',', '.') }}</strong></td>
                <td class="ref">maandelijks vooraf</td>
            </tr>
            @endif
            <tr>
                <td>
                    Borgsom
                    <div class="sub">Geblokkeerde rekening op naam van de huurder (max. 2 maanden huur)</div>
                </td>
                <td class="amount">€&nbsp;{{ number_format((float)($financieel['borgsom'] ?? 0), 2, ',', '.') }}</td>
                <td class="ref">art. 5.33–5.34 VCW</td>
            </tr>
        </tbody>
    </table>
    <p class="note-text">
        De huurprijs is betaalbaar op de eerste dag van elke maand via overschrijving op het rekeningnummer
        van de verhuurder. Indexering van de huurprijs is van toepassing conform art. 5.53 VCW op basis van
        het gezondheidsindexcijfer.
    </p>
</div>

{{-- ── BIJZONDERE VOORWAARDEN ─────────────────────────────────────────────── --}}
@if(!empty($bijzonder))
<div class="section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Bijzondere voorwaarden</div>
    <div class="special-box">{{ $bijzonder }}</div>
</div>
@endif

{{-- ── WETTELIJKE BEPALINGEN ──────────────────────────────────────────────── --}}
<div class="section allow-break">
    <div class="keep-with-next">
        <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Wettelijke bepalingen en verplichtingen</div>

        <div class="legal-article">
            <div class="art-title">Staat van het goed (art. 5.28 VCW)</div>
            <p>Een gedetailleerde plaatsbeschrijving wordt opgesteld bij aanvang en bij beëindiging van de huurovereenkomst,
            op tegensprekelijke wijze en op kosten van beide partijen samen.</p>
        </div>
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
        <div class="art-title">Toepasselijk recht en bevoegde rechter</div>
        <p>{{ $wettelijk['toepasselijk_recht'] ?? 'Vlaamse Codex Wonen 2021 — Studentenhuurovereenkomst (Boek 3, Titel 2)' }}.
        Bij geschillen is de vrederechter van de gerechtelijke afdeling waar het gehuurde goed gelegen is bevoegd.</p>
    </div>
</div>

{{-- ── ONDERTEKENING ───────────────────────────────────────────────────────── --}}
<div class="section sig-section">
    <div class="section-heading"><span class="art-nr">Art. {{ $sectionNr++ }}.</span> Ondertekening</div>
    <p class="sig-intro">
        Opgemaakt te {{ $goed['gemeente'] ?? ($goed['adres'] ?? '—') }}, in zoveel originele exemplaren als er partijen zijn,
        waarbij iedere partij erkent één origineel exemplaar te hebben ontvangen.
        Door ondertekening verklaren alle partijen de inhoud van deze overeenkomst te hebben gelezen en te aanvaarden.
    </p>

    <table class="sig-table" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div class="sig-block">
                    <div class="sig-role">Verhuurder</div>
                    <div class="sig-name">{{ $verhuurder['naam'] ?? '—' }}</div>
                    <div class="sig-email">{{ $verhuurder['email'] ?? '' }}</div>
                    @if($verhuurderTekening)
                        <div class="sig-done">
                            <span class="check">✓</span>
                            Digitaal ondertekend op
                            <strong>{{ \Carbon\Carbon::parse($verhuurderTekening['signed_at'])->translatedFormat('d F Y \o\m H:i') }}</strong>
                        </div>
                    @else
                        <div class="sig-line"></div>
                        <div class="sig-label">Handtekening &amp; datum</div>
                    @endif
                </div>
            </td>
            <td>
                @foreach($huurders as $huurder)
                    @php
                        $huurderTekening = $handtekeningen
                            ->where('is_verhuurder', '!=', true)
                            ->firstWhere('user_id', $huurder['user_id'] ?? null);
                    @endphp
                    <div class="sig-block" style="{{ !$loop->first ? 'margin-top:14px' : '' }}">
                        <div class="sig-role">{{ empty($huurder['is_primary']) ? 'Medehuurder' : 'Hoofdhuurder' }}</div>
                        <div class="sig-name">{{ $huurder['naam'] ?? '—' }}</div>
                        <div class="sig-email">{{ $huurder['email'] ?? '' }}</div>
                        @if($huurderTekening)
                            <div class="sig-done">
                                <span class="check">✓</span>
                                Digitaal ondertekend op
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

</div>{{-- .page --}}
</body>
</html>
