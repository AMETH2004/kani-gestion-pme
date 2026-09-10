@extends('pdf.layout')

@php
    $devise = $entreprise->devise ?: 'XOF';
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ').' '.$devise;
@endphp

@section('titre', 'Facture '.$facture->numero)

@section('entete_droite')
    <h1 style="font-size:18px;">FACTURE</h1>
    <div class="muted">
        N° <strong>{{ $facture->numero }}</strong><br>
        Date : {{ $facture->date_facture->format('d/m/Y') }}<br>
        @if($facture->date_echeance)Échéance : {{ $facture->date_echeance->format('d/m/Y') }}<br>@endif
        <span class="badge">{{ str_replace('_', ' ', $facture->statut) }}</span>
    </div>
@endsection

@section('contenu')
    <div style="margin-top:28px;">
        <div class="muted" style="text-transform:uppercase;font-size:10px;">Facturé à</div>
        <strong>{{ $facture->client->nom }}</strong><br>
        <span class="muted">
            @if($facture->client->adresse){{ $facture->client->adresse }}<br>@endif
            @if($facture->client->telephone){{ $facture->client->telephone }}<br>@endif
            @if($facture->client->ninea)NINEA : {{ $facture->client->ninea }}@endif
        </span>
    </div>

    <table class="lignes">
        <thead>
        <tr>
            <th>Désignation</th>
            <th class="num">Qté</th>
            <th class="num">P.U. HT</th>
            <th class="num">TVA</th>
            <th class="num">Montant HT</th>
        </tr>
        </thead>
        <tbody>
        @foreach($facture->lignes as $ligne)
            <tr>
                <td>{{ $ligne->designation }}</td>
                <td class="num">{{ rtrim(rtrim(number_format($ligne->quantite, 3, ',', ' '), '0'), ',') }}</td>
                <td class="num">{{ $fmt($ligne->prix_unitaire) }}</td>
                <td class="num">{{ number_format($ligne->taux_tva, 0) }} %</td>
                <td class="num">{{ $fmt($ligne->montant_ht) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totaux">
        <tr><td class="muted">Total HT</td><td class="num">{{ $fmt($facture->montant_ht) }}</td></tr>
        @if($facture->montant_remise > 0)
            <tr><td class="muted">Remise</td><td class="num">- {{ $fmt($facture->montant_remise) }}</td></tr>
        @endif
        <tr><td class="muted">TVA</td><td class="num">{{ $fmt($facture->montant_tva) }}</td></tr>
        <tr class="ttc"><td>Total TTC</td><td class="num">{{ $fmt($facture->montant_ttc) }}</td></tr>
        @if($facture->montant_paye > 0)
            <tr><td class="muted">Déjà réglé</td><td class="num">- {{ $fmt($facture->montant_paye) }}</td></tr>
            <tr class="ttc"><td>Reste à payer</td><td class="num">{{ $fmt($facture->reste_a_payer) }}</td></tr>
        @endif
    </table>

    <div style="clear:both;"></div>

    @if($facture->notes)
        <div class="notes">{{ $facture->notes }}</div>
    @endif
@endsection
