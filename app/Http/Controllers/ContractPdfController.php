<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ContractPdfController extends Controller
{
    public function __invoke(Document $document): Response
    {
        // Huurder mag enkel eigen contract zien, verhuurder enkel contracten van zijn kamers
        $user = auth()->user();

        if ($user->hasRole('huurder')) {
            abort_unless(
                $document->rentalPeriod?->tenants()->where('users.id', $user->id)->exists(),
                403
            );
        } elseif ($user->hasRole('verhuurder')) {
            abort_unless(
                $document->rentalPeriod?->room?->building?->landlord_id === $user->id
                || $document->user_id === $user->id,
                403
            );
        } else {
            abort(403);
        }

        abort_unless($document->type === 'contract', 404);

        $pdf = Pdf::loadView('pdf.contract', [
            'document' => $document,
            'blocks'   => $document->blocks ?? [],
        ])->setPaper('a4');

        $filename = str($document->name ?? 'contract')->slug() . '.pdf';

        return $pdf->stream($filename);
    }
}
