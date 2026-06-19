@extends('mailing.notification')

@section('heading', 'Hoe was je kot?')

@section('body')
    <p style="margin:0 0 18px;font-size:15px;line-height:1.6;color:#586573;">
        Je huurde {{ $invitation->room->title ?: 'je kot' }} via KotKompas. Geef je score —
        <strong>anoniem</strong>, geen tekst nodig. Tik een cijfer (1 = ondermaats, 5 = uitstekend);
        op de volgende pagina rond je af.
    </p>

    @foreach ($criteria as $field => $criterion)
        <p style="margin:0 0 6px;font-size:13px;font-weight:bold;color:#0f1720;">{{ $criterion['label'] }}</p>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 16px;">
            <tr>
                @for ($i = 1; $i <= 5; $i++)
                    <td style="padding-right:6px;">
                        <a href="{{ route('reviews.create', [$invitation, $field => $i]) }}"
                           style="display:inline-block;width:34px;height:34px;line-height:34px;text-align:center;border:1px solid #c8ccd2;border-radius:4px;color:#0f1720;text-decoration:none;font-size:14px;font-weight:bold;">{{ $i }}</a>
                    </td>
                @endfor
            </tr>
        </table>
    @endforeach
@endsection

@section('extra')
    <p style="margin:8px 0 0;font-size:13px;color:#586573;">
        Deze link is geldig tot {{ $invitation->expires_at->format('d-m-Y') }}.
    </p>
@endsection
