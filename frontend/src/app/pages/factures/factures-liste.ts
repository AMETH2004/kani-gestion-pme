import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { FactureService } from '../../services/facture.service';
import { Facture } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { TelechargementService } from '../../core/telechargement.service';
import { MontantPipe } from '../../shared/montant.pipe';
import { StatutBadgeComponent } from '../../shared/statut-badge';

@Component({
  selector: 'app-factures-liste',
  imports: [RouterLink, DatePipe, MontantPipe, StatutBadgeComponent],
  templateUrl: './factures-liste.html',
})
export class FacturesListeComponent implements OnInit {
  private service = inject(FactureService);
  private telechargement = inject(TelechargementService);
  private auth = inject(AuthService);

  readonly factures = signal<Facture[]>([]);
  readonly chargement = signal(true);
  readonly filtreStatut = signal('');

  readonly peutModifier = this.auth.aRole('Administrateur', 'Gestionnaire');

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service.liste({ statut: this.filtreStatut() || undefined }).subscribe({
      next: (page) => {
        this.factures.set(page.data);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  majStatut(v: string): void {
    this.filtreStatut.set(v);
    this.charger();
  }

  pdf(f: Facture): void {
    this.telechargement.ouvrirPdf(this.service.urlPdf(f.id));
  }
}
