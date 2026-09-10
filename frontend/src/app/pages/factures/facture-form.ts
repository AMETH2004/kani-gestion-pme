import { Component, OnInit, inject, signal } from '@angular/core';
import { FormArray, FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { FactureService, FacturePayload } from '../../services/facture.service';
import { ClientService } from '../../services/client.service';
import { ProduitService } from '../../services/produit.service';
import { Client, Produit } from '../../core/models';
import { NotificationService } from '../../core/notification.service';
import { LignesEditorComponent } from '../../shared/lignes-editor';

@Component({
  selector: 'app-facture-form',
  imports: [ReactiveFormsModule, RouterLink, LignesEditorComponent],
  templateUrl: './facture-form.html',
})
export class FactureFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private service = inject(FactureService);
  private clientService = inject(ClientService);
  private produitService = inject(ProduitService);
  private notif = inject(NotificationService);
  private router = inject(Router);

  readonly clients = signal<Client[]>([]);
  readonly produits = signal<Produit[]>([]);
  readonly enregistrement = signal(false);

  readonly form = this.fb.nonNullable.group({
    client_id: [null as number | null, Validators.required],
    date_facture: [new Date().toISOString().slice(0, 10), Validators.required],
    date_echeance: [new Date(Date.now() + 30 * 864e5).toISOString().slice(0, 10)],
    notes: [''],
    remise: [0],
    lignes: this.fb.array<ReturnType<FactureFormComponent['ligneVide']>>([]),
  });

  get lignesArray(): FormArray {
    return this.form.get('lignes') as FormArray;
  }

  ligneVide() {
    return this.fb.nonNullable.group({
      produit_id: [null as number | null],
      designation: [''],
      quantite: [1],
      prix_unitaire: [0],
      taux_tva: [18],
    });
  }

  ngOnInit(): void {
    this.clientService
      .liste({ actifs_seulement: true, par_page: 200 })
      .subscribe((p) => this.clients.set(p.data));
    this.produitService
      .liste({ actifs_seulement: true, par_page: 200 })
      .subscribe((p) => this.produits.set(p.data));
    this.lignesArray.push(this.ligneVide());
  }

  enregistrer(): void {
    if (this.form.invalid || this.lignesArray.length === 0) {
      this.form.markAllAsTouched();
      this.notif.erreur('Choisissez un client et ajoutez au moins une ligne.');
      return;
    }
    this.enregistrement.set(true);
    const v = this.form.getRawValue();
    const payload: FacturePayload = {
      client_id: v.client_id!,
      date_facture: v.date_facture,
      date_echeance: v.date_echeance || null,
      notes: v.notes || null,
      remise: Number(v.remise) || 0,
      lignes: v.lignes.map((l) => ({
        produit_id: l.produit_id ?? null,
        designation: l.designation || 'Article',
        quantite: Number(l.quantite),
        prix_unitaire: Number(l.prix_unitaire),
        taux_tva: Number(l.taux_tva),
      })),
    };

    this.service.creer(payload).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.router.navigate(['/factures', res.data.id]);
      },
      error: () => this.enregistrement.set(false),
    });
  }
}
