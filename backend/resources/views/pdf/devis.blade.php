@extends('pdf.layout')

@php
    $devise = $entreprise->devise ?: 'XOF';
    $fmt = fn ($n) => number_format((float) $n, 0, ',', ' ').' '.$devise;
@endphp

@section('titre', 'Devis '.$devis->numero)

@section('entete_droite')
    <h1 style="font-size:18px;">DEVIS</h1>
    <div class="muted">
        N° <strong>{{ $devis->numero }}</strong><br>
        Date : {{ $devis->date_devis->format('d/m/Y') }}<br>
        @if($devis->date_validite)Valable jusqu'au : {{ $devis->date_validite->format('d/m/Y') }}<br>@endif
        <span class="badge">{{ str_replace('_', ' ', $devis->statut) }}</span>
    </div>
@endsection

@section('contenu')
    <div style="margin-top:28px;">
        <div class="muted" style="text-transform:uppercase;font-size:10px;">Client</div>
        <strong>{{ $devis->client->nom }}</strong><br>
        <span class="muted">
            @if($devis->client->adresse){{ $devis->client->adresse }}<br>@endif
            @if($devis->client->telephone){{ $devis->client->telephone }}@endif
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
        @foreach($devis->lignes as $ligne)
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
        <tr><td class="muted">Total HT</td><td class="num">{{ $fmt($devis->montant_ht) }}</td></tr>
        @if($devis->montant_remise > 0)
            <tr><td class="muted">Remise</td><td class="num">- {{ $fmt($devis->montant_remise) }}</td></tr>
        @endif
        <tr><td class="muted">TVA</td><td class="num">{{ $fmt($devis->montant_tva) }}</td></tr>
        <tr class="ttc"><td>Total TTC</td><td class="num">{{ $fmt($devis->montant_ttc) }}</td></tr>
    </table>

    <div style="clear:both;"></div>

    @if($devis->notes)
        <div class="notes">{{ $devis->notes }}</div>
    @endif
@endsection
