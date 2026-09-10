import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Devis, Facture, LigneDocument, Paginated } from '../core/models';
import { EcritureReponse, toParams } from '../core/api';

export interface DevisPayload {
  client_id: number;
  date_devis: string;
  date_validite?: string | null;
  statut?: string;
  notes?: string | null;
  remise?: number;
  lignes: LigneDocument[];
}

@Injectable({ providedIn: 'root' })
export class DevisService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/devis`;

  liste(filtres: { statut?: string; client_id?: number; par_page?: number } = {}): Observable<
    Paginated<Devis>
  > {
    return this.http.get<Paginated<Devis>>(this.url, { params: toParams(filtres) });
  }

  un(id: number): Observable<Devis> {
    return this.http.get<Devis>(`${this.url}/${id}`);
  }

  creer(data: DevisPayload): Observable<EcritureReponse<Devis>> {
    return this.http.post<EcritureReponse<Devis>>(this.url, data);
  }

  modifier(id: number, data: DevisPayload): Observable<EcritureReponse<Devis>> {
    return this.http.put<EcritureReponse<Devis>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }

  convertir(id: number): Observable<EcritureReponse<Facture>> {
    return this.http.post<EcritureReponse<Facture>>(`${this.url}/${id}/convertir`, {});
  }

  urlPdf(id: number): string {
    return `${this.url}/${id}/pdf`;
  }
}
