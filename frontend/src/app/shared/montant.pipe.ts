import { Pipe, PipeTransform } from '@angular/core';

/** Formate un montant en francs CFA : 16 762 FCFA. */
@Pipe({ name: 'montant' })
export class MontantPipe implements PipeTransform {
  transform(valeur: number | string | null | undefined, devise = 'FCFA'): string {
    const n = Number(valeur ?? 0);
    return `${n.toLocaleString('fr-FR', { maximumFractionDigits: 0 })} ${devise}`;
  }
}
