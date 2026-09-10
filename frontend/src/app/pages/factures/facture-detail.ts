import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { FactureService } from '../../services/facture.service';
import { Facture } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { TelechargementService } from '../../core/telechargement.service';
import { MontantPipe } from '../../shared/montant.pipe';
import { StatutBadgeComponent } from '../../shared/statut-badge';
import { ModalComponent } from '../../shared/modal';

@Component({
  selector: 'app-facture-detail',
  imports: [
    RouterLink,
    DatePipe,
    ReactiveFormsModule,
    MontantPipe,
    StatutBadgeComponent,
    ModalComponent,
  ],
  templateUrl: './facture-detail.html',
})
export class FactureDetailComponent implements OnInit {
  private fb = inject(FormBuilder);
  private service = inject(FactureService);
  private notif = inject(NotificationService);
  private telechargement = inject(TelechargementService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  private auth = inject(AuthService);

  readonly facture = signal<Facture | null>(null);
  readonly chargement = signal(true);
  readonly action = signal(false);
  readonly modalPaiement = signal(false);

  readonly peutModifier = this.auth.aRole('Administrateur', 'Gestionnaire');

  readonly formPaiement = this.fb.nonNullable.group({
    montant: [0, [Validators.required, Validators.min(1)]],
    mode: ['especes' as 'especes' | 'cheque' | 'virement' | 'wave' | 'orange_money' | 'autre'],
    reference: [''],
    date_paiement: [new Date().toISOString().slice(0, 10)],
  });

  ngOnInit(): void {
    this.charger(Number(this.route.snapshot.paramMap.get('id')));
  }

  charger(id: number): void {
    this.chargement.set(true);
    this.service.un(id).subscribe({
      next: (f) => {
        this.facture.set(f);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.router.navigate(['/factures']);
      },
    });
  }

  pdf(): void {
    const f = this.facture();
    if (f) this.telechargement.ouvrirPdf(this.service.urlPdf(f.id));
  }

  valider(): void {
    const f = this.facture();
    if (!f || !confirm(`Valider la facture ${f.numero} ? Le stock sera décrémenté.`)) return;
    this.action.set(true);
    this.service.valider(f.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.action.set(false);
        this.charger(f.id);
      },
      error: () => this.action.set(false),
    });
  }

  annuler(): void {
    const f = this.facture();
    if (!f || !confirm(`Annuler la facture ${f.numero} ?`)) return;
    this.action.set(true);
    this.service.annuler(f.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.action.set(false);
        this.charger(f.id);
      },
      error: () => this.action.set(false),
    });
  }

  supprimer(): void {
    const f = this.facture();
    if (!f || !confirm(`Supprimer définitivement le brouillon ${f.numero} ?`)) return;
    this.service.supprimer(f.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.router.navigate(['/factures']);
      },
    });
  }

  ouvrirPaiement(): void {
    const f = this.facture();
    this.formPaiement.reset({
      montant: f?.reste_a_payer ?? 0,
      mode: 'especes',
      reference: '',
      date_paiement: new Date().toISOString().slice(0, 10),
    });
    this.modalPaiement.set(true);
  }

  enregistrerPaiement(): void {
    const f = this.facture();
    if (!f || this.formPaiement.invalid) return;
    this.action.set(true);
    this.service.encaisser(f.id, this.formPaiement.getRawValue()).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalPaiement.set(false);
        this.action.set(false);
        this.charger(f.id);
      },
      error: () => this.action.set(false),
    });
  }

  supprimerPaiement(paiementId: number): void {
    const f = this.facture();
    if (!f || !confirm('Supprimer ce paiement ?')) return;
    this.service.supprimerPaiement(f.id, paiementId).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger(f.id);
      },
    });
  }
}
