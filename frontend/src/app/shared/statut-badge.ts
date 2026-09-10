import { Component, Input } from '@angular/core';

const COULEURS: Record<string, string> = {
  brouillon: 'text-bg-secondary',
  envoye: 'text-bg-info',
  envoyee: 'text-bg-info',
  accepte: 'text-bg-success',
  refuse: 'text-bg-danger',
  expire: 'text-bg-warning',
  converti: 'text-bg-primary',
  partiellement_payee: 'text-bg-warning',
  payee: 'text-bg-success',
  en_retard: 'text-bg-danger',
  annulee: 'text-bg-dark',
};

@Component({
  selector: 'app-statut-badge',
  template: `<span class="badge badge-statut" [class]="couleur">{{ libelle }}</span>`,
})
export class StatutBadgeComponent {
  @Input({ required: true }) statut!: string;

  get couleur(): string {
    return COULEURS[this.statut] ?? 'text-bg-light';
  }

  get libelle(): string {
    return this.statut.replace(/_/g, ' ');
  }
}
