import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Facture, LigneDocument, Paginated, Paiement } from '../core/models';
import { EcritureReponse, toParams } from '../core/api';

export interface FacturePayload {
  client_id: number;
  date_facture: string;
  date_echeance?: string | null;
  notes?: string | null;
  remise?: number;
  lignes: LigneDocument[];
}

export interface PaiementPayload {
  montant: number;
  mode: Paiement['mode'];
  reference?: string;
  date_paiement?: string;
  notes?: string;
}

@Injectable({ providedIn: 'root' })
export class FactureService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/factures`;

  liste(
    filtres: { statut?: string; client_id?: number; impayees?: boolean; par_page?: number } = {},
  ): Observable<Paginated<Facture>> {
    return this.http.get<Paginated<Facture>>(this.url, { params: toParams(filtres) });
  }

  un(id: number): Observable<Facture> {
    return this.http.get<Facture>(`${this.url}/${id}`);
  }

  creer(data: FacturePayload): Observable<EcritureReponse<Facture>> {
    return this.http.post<EcritureReponse<Facture>>(this.url, data);
  }

  modifier(id: number, data: FacturePayload): Observable<EcritureReponse<Facture>> {
    return this.http.put<EcritureReponse<Facture>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }

  valider(id: number): Observable<EcritureReponse<Facture>> {
    return this.http.post<EcritureReponse<Facture>>(`${this.url}/${id}/valider`, {});
  }

  annuler(id: number): Observable<EcritureReponse<Facture>> {
    return this.http.post<EcritureReponse<Facture>>(`${this.url}/${id}/annuler`, {});
  }

  encaisser(
    id: number,
    data: PaiementPayload,
  ): Observable<{ message: string; data: Paiement; facture: Facture }> {
    return this.http.post<{ message: string; data: Paiement; facture: Facture }>(
      `${this.url}/${id}/paiements`,
      data,
    );
  }

  supprimerPaiement(factureId: number, paiementId: number): Observable<{ message: string; facture: Facture }> {
    return this.http.delete<{ message: string; facture: Facture }>(
      `${this.url}/${factureId}/paiements/${paiementId}`,
    );
  }

  urlPdf(id: number): string {
    return `${this.url}/${id}/pdf`;
  }
}
