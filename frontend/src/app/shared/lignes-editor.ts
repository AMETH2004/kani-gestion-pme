import { Component, Input, inject } from '@angular/core';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { Produit } from '../core/models';
import { MontantPipe } from './montant.pipe';

/**
 * Éditeur de lignes (devis / facture) : un FormArray de lignes + une remise
 * globale. Les totaux sont recalculés à la volée par `totaux()`.
 */
@Component({
  selector: 'app-lignes-editor',
  imports: [ReactiveFormsModule, MontantPipe],
  templateUrl: './lignes-editor.html',
})
export class LignesEditorComponent {
  private fb = inject(FormBuilder);

  /** FormGroup parent contenant `lignes: FormArray` et `remise: control`. */
  @Input({ required: true }) parent!: FormGroup;
  @Input() produits: Produit[] = [];
  @Input() lectureSeule = false;

  get lignes(): FormArray {
    return this.parent.get('lignes') as FormArray;
  }

  ajouterLigne(produit?: Produit): void {
    this.lignes.push(
      this.fb.nonNullable.group({
        produit_id: [produit?.id ?? null],
        designation: [produit?.designation ?? ''],
        quantite: [1],
        prix_unitaire: [produit ? Number(produit.prix_vente) : 0],
        taux_tva: [produit ? Number(produit.taux_tva) : 18],
      }),
    );
  }

  retirerLigne(i: number): void {
    this.lignes.removeAt(i);
  }

  surChoixProduit(i: number, produitId: string): void {
    const p = this.produits.find((x) => x.id === Number(produitId));
    if (!p) return;
    this.lignes.at(i).patchValue({
      designation: p.designation,
      prix_unitaire: Number(p.prix_vente),
      taux_tva: Number(p.taux_tva),
    });
  }

  ligneHt(g: unknown): number {
    const v = (g as FormGroup).getRawValue() as { quantite: number; prix_unitaire: number };
    return (Number(v.quantite) || 0) * (Number(v.prix_unitaire) || 0);
  }

  totaux(): { ht: number; remise: number; tva: number; ttc: number } {
    let ht = 0;
    let tvaBrute = 0;
    for (const ctrl of this.lignes.controls) {
      const v = ctrl.getRawValue() as {
        quantite: number;
        prix_unitaire: number;
        taux_tva: number;
      };
      const ligneHt = (Number(v.quantite) || 0) * (Number(v.prix_unitaire) || 0);
      ht += ligneHt;
      tvaBrute += ligneHt * (Number(v.taux_tva) || 0) / 100;
    }
    const remise = Math.min(Math.max(Number(this.parent.get('remise')?.value) || 0, 0), ht);
    const ratio = ht > 0 ? (ht - remise) / ht : 0;
    const tva = Math.round(tvaBrute * ratio);
    return { ht: Math.round(ht), remise: Math.round(remise), tva, ttc: Math.round(ht - remise + tva) };
  }
}
