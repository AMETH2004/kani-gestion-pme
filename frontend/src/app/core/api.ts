import { HttpParams } from '@angular/common/http';

/** Construit un HttpParams en ignorant null / undefined / ''. */
export function toParams(obj: Record<string, unknown>): HttpParams {
  let params = new HttpParams();
  for (const [cle, valeur] of Object.entries(obj)) {
    if (valeur !== null && valeur !== undefined && valeur !== '') {
      params = params.set(cle, String(valeur));
    }
  }
  return params;
}

/** Enveloppe d'écriture renvoyée par l'API : { message, data }. */
export interface EcritureReponse<T> {
  message: string;
  data: T;
}
