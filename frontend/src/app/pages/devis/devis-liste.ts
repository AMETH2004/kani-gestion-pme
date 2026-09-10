import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DevisService } from '../../services/devis.service';
import { Devis } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { TelechargementService } from '../../core/telechargement.service';
import { MontantPipe } from '../../shared/montant.pipe';
import { StatutBadgeComponent } from '../../shared/statut-badge';

@Component({
  selector: 'app-devis-liste',
  imports: [RouterLink, DatePipe, MontantPipe, StatutBadgeComponent],
  templateUrl: './devis-liste.html',
})
export class DevisListeComponent implements OnInit {
  private service = inject(DevisService);
  private notif = inject(NotificationService);
  private telechargement = inject(TelechargementService);
  private auth = inject(AuthService);

  readonly devis = signal<Devis[]>([]);
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
        this.devis.set(page.data);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  majStatut(v: string): void {
    this.filtreStatut.set(v);
    this.charger();
  }

  pdf(d: Devis): void {
    this.telechargement.ouvrirPdf(this.service.urlPdf(d.id));
  }

  convertir(d: Devis): void {
    if (!confirm(`Convertir le devis ${d.numero} en facture ?`)) return;
    this.service.convertir(d.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }

  supprimer(d: Devis): void {
    if (!confirm(`Supprimer le devis ${d.numero} ?`)) return;
    this.service.supprimer(d.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }
}
