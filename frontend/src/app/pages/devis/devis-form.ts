import { Component, OnInit, inject, signal } from '@angular/core';
import { FormArray, FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { DevisService, DevisPayload } from '../../services/devis.service';
import { ClientService } from '../../services/client.service';
import { ProduitService } from '../../services/produit.service';
import { Client, Produit } from '../../core/models';
import { NotificationService } from '../../core/notification.service';
import { LignesEditorComponent } from '../../shared/lignes-editor';

@Component({
  selector: 'app-devis-form',
  imports: [ReactiveFormsModule, RouterLink, LignesEditorComponent],
  templateUrl: './devis-form.html',
})
export class DevisFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private service = inject(DevisService);
  private clientService = inject(ClientService);
  private produitService = inject(ProduitService);
  private notif = inject(NotificationService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  readonly clients = signal<Client[]>([]);
  readonly produits = signal<Produit[]>([]);
  readonly chargement = signal(false);
  readonly enregistrement = signal(false);
  readonly editionId = signal<number | null>(null);

  readonly form = this.fb.nonNullable.group({
    client_id: [null as number | null, Validators.required],
    date_devis: [new Date().toISOString().slice(0, 10), Validators.required],
    date_validite: [''],
    notes: [''],
    remise: [0],
    lignes: this.fb.array<ReturnType<DevisFormComponent['ligneVide']>>([]),
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
    this.clientService.liste({ actifs_seulement: true, par_page: 200 }).subscribe((p) =>
      this.clients.set(p.data),
    );
    this.produitService.liste({ actifs_seulement: true, par_page: 200 }).subscribe((p) =>
      this.produits.set(p.data),
    );

    const id = this.route.snapshot.paramMap.get('id');
    if (id) {
      this.editionId.set(Number(id));
      this.chargerDevis(Number(id));
    } else {
      this.lignesArray.push(this.ligneVide());
    }
  }

  private chargerDevis(id: number): void {
    this.chargement.set(true);
    this.service.un(id).subscribe({
      next: (d) => {
        this.form.patchValue({
          client_id: d.client_id,
          date_devis: d.date_devis.slice(0, 10),
          date_validite: d.date_validite?.slice(0, 10) ?? '',
          notes: d.notes ?? '',
          remise: Number(d.montant_remise),
        });
        this.lignesArray.clear();
        for (const l of d.lignes ?? []) {
          const g = this.ligneVide();
          g.patchValue({
            produit_id: l.produit_id ?? null,
            designation: l.designation,
            quantite: Number(l.quantite),
            prix_unitaire: Number(l.prix_unitaire),
            taux_tva: Number(l.taux_tva),
          });
          this.lignesArray.push(g);
        }
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.router.navigate(['/devis']);
      },
    });
  }

  enregistrer(): void {
    if (this.form.invalid || this.lignesArray.length === 0) {
      this.form.markAllAsTouched();
      this.notif.erreur('Choisissez un client et ajoutez au moins une ligne.');
      return;
    }
    this.enregistrement.set(true);
    const v = this.form.getRawValue();
    const payload: DevisPayload = {
      client_id: v.client_id!,
      date_devis: v.date_devis,
      date_validite: v.date_validite || null,
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

    const id = this.editionId();
    const req = id ? this.service.modifier(id, payload) : this.service.creer(payload);
    req.subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.router.navigate(['/devis']);
      },
      error: () => this.enregistrement.set(false),
    });
  }
}
