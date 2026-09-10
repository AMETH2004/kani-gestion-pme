import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { StockService } from '../../services/stock.service';
import { ProduitService } from '../../services/produit.service';
import { MouvementStock, Produit, TypeMouvement } from '../../core/models';

@Component({
  selector: 'app-stock',
  imports: [DatePipe],
  templateUrl: './stock.html',
})
export class StockComponent implements OnInit {
  private service = inject(StockService);
  private produitService = inject(ProduitService);

  readonly mouvements = signal<MouvementStock[]>([]);
  readonly produits = signal<Produit[]>([]);
  readonly chargement = signal(true);
  readonly filtreProduit = signal<number | null>(null);
  readonly filtreType = signal<TypeMouvement | null>(null);

  ngOnInit(): void {
    this.produitService.liste({ par_page: 200 }).subscribe((p) => this.produits.set(p.data));
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service
      .mouvements({
        produit_id: this.filtreProduit() ?? undefined,
        type: this.filtreType() ?? undefined,
      })
      .subscribe({
        next: (page) => {
          this.mouvements.set(page.data);
          this.chargement.set(false);
        },
        error: () => this.chargement.set(false),
      });
  }

  majProduit(v: string): void {
    this.filtreProduit.set(v ? Number(v) : null);
    this.charger();
  }

  majType(v: string): void {
    this.filtreType.set((v as TypeMouvement) || null);
    this.charger();
  }

  couleurType(type: TypeMouvement): string {
    return type === 'entree'
      ? 'text-bg-success'
      : type === 'sortie'
        ? 'text-bg-danger'
        : 'text-bg-secondary';
  }
}
