import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ProduitService } from '../../services/produit.service';
import { CategorieService } from '../../services/categorie.service';
import { Categorie, Produit } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { ModalComponent } from '../../shared/modal';
import { MontantPipe } from '../../shared/montant.pipe';

@Component({
  selector: 'app-produits',
  imports: [ReactiveFormsModule, ModalComponent, MontantPipe],
  templateUrl: './produits.html',
})
export class ProduitsComponent implements OnInit {
  private service = inject(ProduitService);
  private categorieService = inject(CategorieService);
  private fb = inject(FormBuilder);
  private notif = inject(NotificationService);
  private auth = inject(AuthService);

  readonly produits = signal<Produit[]>([]);
  readonly categories = signal<Categorie[]>([]);
  readonly chargement = signal(true);
  readonly recherche = signal('');
  readonly filtreStockBas = signal(false);

  readonly modalOuvert = signal(false);
  readonly editionId = signal<number | null>(null);
  readonly enregistrement = signal(false);

  readonly modalStock = signal<Produit | null>(null);
  readonly mouvementEnCours = signal(false);

  readonly peutModifier = this.auth.aRole('Administrateur', 'Magasinier');

  readonly form = this.fb.nonNullable.group({
    reference: ['', Validators.required],
    designation: ['', Validators.required],
    categorie_id: [null as number | null],
    prix_achat: [0, [Validators.min(0)]],
    prix_vente: [0, [Validators.required, Validators.min(0)]],
    taux_tva: [18, [Validators.min(0), Validators.max(100)]],
    unite: ['unité'],
    stock_actuel: [0, [Validators.min(0)]],
    stock_alerte: [0, [Validators.min(0)]],
    gerer_stock: [true],
    actif: [true],
  });

  readonly formStock = this.fb.nonNullable.group({
    type: ['entree' as 'entree' | 'sortie' | 'ajustement', Validators.required],
    quantite: [1, [Validators.required, Validators.min(0)]],
    motif: [''],
  });

  ngOnInit(): void {
    this.categorieService.liste().subscribe((c) => this.categories.set(c));
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service
      .liste({ recherche: this.recherche(), stock_bas: this.filtreStockBas() || undefined })
      .subscribe({
        next: (page) => {
          this.produits.set(page.data);
          this.chargement.set(false);
        },
        error: () => this.chargement.set(false),
      });
  }

  basculerStockBas(): void {
    this.filtreStockBas.update((v) => !v);
    this.charger();
  }

  ouvrirCreation(): void {
    this.editionId.set(null);
    this.form.reset({
      taux_tva: 18,
      unite: 'unité',
      prix_achat: 0,
      prix_vente: 0,
      stock_actuel: 0,
      stock_alerte: 0,
      gerer_stock: true,
      actif: true,
      categorie_id: null,
    });
    this.modalOuvert.set(true);
  }

  ouvrirEdition(p: Produit): void {
    this.editionId.set(p.id);
    this.form.reset({
      reference: p.reference,
      designation: p.designation,
      categorie_id: p.categorie_id ?? null,
      prix_achat: Number(p.prix_achat),
      prix_vente: Number(p.prix_vente),
      taux_tva: Number(p.taux_tva),
      unite: p.unite,
      stock_actuel: Number(p.stock_actuel),
      stock_alerte: Number(p.stock_alerte),
      gerer_stock: p.gerer_stock,
      actif: p.actif,
    });
    this.form.controls.stock_actuel.disable();
    this.modalOuvert.set(true);
  }

  enregistrer(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.enregistrement.set(true);
    const data = this.form.getRawValue();
    const id = this.editionId();
    const req = id ? this.service.modifier(id, data) : this.service.creer(data);
    req.subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalOuvert.set(false);
        this.form.controls.stock_actuel.enable();
        this.enregistrement.set(false);
        this.charger();
      },
      error: () => this.enregistrement.set(false),
    });
  }

  supprimer(p: Produit): void {
    if (!confirm(`Supprimer le produit « ${p.designation} » ?`)) return;
    this.service.supprimer(p.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }

  ouvrirStock(p: Produit): void {
    this.formStock.reset({ type: 'entree', quantite: 1, motif: '' });
    this.modalStock.set(p);
  }

  enregistrerMouvement(): void {
    const produit = this.modalStock();
    if (!produit || this.formStock.invalid) return;
    this.mouvementEnCours.set(true);
    this.service.enregistrerMouvement(produit.id, this.formStock.getRawValue()).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalStock.set(null);
        this.mouvementEnCours.set(false);
        this.charger();
      },
      error: () => this.mouvementEnCours.set(false),
    });
  }
}
