import { Component, OnInit, inject, signal } from '@angular/core';
import { DecimalPipe } from '@angular/common';
import { RouterLink } from '@angular/router';
import { DashboardService } from '../../services/dashboard.service';
import { DashboardData } from '../../core/models';
import { MontantPipe } from '../../shared/montant.pipe';
import { StatutBadgeComponent } from '../../shared/statut-badge';

@Component({
  selector: 'app-dashboard',
  imports: [RouterLink, DecimalPipe, MontantPipe, StatutBadgeComponent],
  templateUrl: './dashboard.html',
})
export class DashboardComponent implements OnInit {
  private service = inject(DashboardService);

  readonly data = signal<DashboardData | null>(null);
  readonly chargement = signal(true);

  ngOnInit(): void {
    this.service.charger().subscribe({
      next: (d) => {
        this.data.set(d);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  hauteurBarre(montant: number): number {
    const max = Math.max(...(this.data()?.ca_6_mois.map((m) => m.montant) ?? [1]), 1);
    return Math.round((montant / max) * 100);
  }
}
