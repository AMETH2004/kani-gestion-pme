import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { MouvementStock, Paginated, Produit } from '../core/models';
import { EcritureReponse, toParams } from '../core/api';

@Injectable({ providedIn: 'root' })
export class ProduitService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/produits`;

  liste(filtres: {
    recherche?: string;
    categorie_id?: number;
    stock_bas?: boolean;
    actifs_seulement?: boolean;
    par_page?: number;
  } = {}): Observable<Paginated<Produit>> {
    return this.http.get<Paginated<Produit>>(this.url, { params: toParams(filtres) });
  }

  un(id: number): Observable<Produit> {
    return this.http.get<Produit>(`${this.url}/${id}`);
  }

  alertesStock(): Observable<Produit[]> {
    return this.http.get<Produit[]>(`${this.url}/alertes-stock`);
  }

  creer(data: Partial<Produit>): Observable<EcritureReponse<Produit>> {
    return this.http.post<EcritureReponse<Produit>>(this.url, data);
  }

  modifier(id: number, data: Partial<Produit>): Observable<EcritureReponse<Produit>> {
    return this.http.put<EcritureReponse<Produit>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }

  enregistrerMouvement(
    produitId: number,
    data: {
      type: MouvementStock['type'];
      quantite: number;
      motif?: string;
      reference_document?: string;
      date_mouvement?: string;
    },
  ): Observable<{ message: string; data: MouvementStock; produit: Produit }> {
    return this.http.post<{ message: string; data: MouvementStock; produit: Produit }>(
      `${this.url}/${produitId}/mouvements`,
      data,
    );
  }
}
