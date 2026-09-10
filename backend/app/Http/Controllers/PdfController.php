<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\Entreprise;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfController extends Controller
{
    public function facture(Facture $facture)
    {
        $facture->load(['client', 'lignes', 'paiements']);

        $pdf = Pdf::loadView('pdf.facture', [
            'entreprise' => Entreprise::current(),
            'facture' => $facture,
        ])->setPaper('a4');

        return $pdf->stream('facture-'.$facture->numero.'.pdf');
    }

    public function devis(Devis $devis)
    {
        $devis->load(['client', 'lignes']);

        $pdf = Pdf::loadView('pdf.devis', [
            'entreprise' => Entreprise::current(),
            'devis' => $devis,
        ])->setPaper('a4');

        return $pdf->stream('devis-'.$devis->numero.'.pdf');
    }
}
