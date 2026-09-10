<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>@yield('titre')</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { font-size: 12px; color: #1f2937; margin: 0; }
        .wrap { padding: 32px 40px; }
        h1 { font-size: 22px; margin: 0 0 2px; color: #111827; }
        .muted { color: #6b7280; }
        .row { width: 100%; }
        .row:after { content: ""; display: table; clear: both; }
        .col-left { float: left; width: 55%; }
        .col-right { float: right; width: 40%; text-align: right; }
        table.lignes { width: 100%; border-collapse: collapse; margin-top: 24px; }
        table.lignes th { background: #111827; color: #fff; text-align: left; padding: 8px 10px; font-size: 11px; }
        table.lignes td { padding: 8px 10px; border-bottom: 1px solid #e5e7eb; }
        table.lignes td.num, table.lignes th.num { text-align: right; }
        .totaux { float: right; width: 45%; margin-top: 16px; }
        .totaux td { padding: 6px 10px; }
        .totaux tr.ttc td { font-size: 14px; font-weight: bold; border-top: 2px solid #111827; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 11px; background: #eef2ff; color: #3730a3; text-transform: capitalize; }
        .footer { position: fixed; bottom: 24px; left: 40px; right: 40px; font-size: 10px; color: #9ca3af; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 8px; }
        .notes { margin-top: 28px; padding: 12px; background: #f9fafb; border-left: 3px solid #d1d5db; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="row">
        <div class="col-left">
            <h1>{{ $entreprise->raison_sociale }}</h1>
            <div class="muted">
                @if($entreprise->adresse){{ $entreprise->adresse }}<br>@endif
                @if($entreprise->telephone)Tél : {{ $entreprise->telephone }}<br>@endif
                @if($entreprise->email){{ $entreprise->email }}<br>@endif
                @if($entreprise->ninea)NINEA : {{ $entreprise->ninea }}@endif
                @if($entreprise->rccm) &nbsp;·&nbsp; RCCM : {{ $entreprise->rccm }}@endif
            </div>
        </div>
        <div class="col-right">
            @yield('entete_droite')
        </div>
    </div>

    @yield('contenu')

    <div class="footer">
        {{ $entreprise->pied_de_page ?: $entreprise->raison_sociale }}
    </div>
</div>
</body>
</html>
