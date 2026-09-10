import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Categorie } from '../core/models';
import { EcritureReponse } from '../core/api';

@Injectable({ providedIn: 'root' })
export class CategorieService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/categories`;

  liste(): Observable<Categorie[]> {
    return this.http.get<Categorie[]>(this.url);
  }

  creer(data: Partial<Categorie>): Observable<EcritureReponse<Categorie>> {
    return this.http.post<EcritureReponse<Categorie>>(this.url, data);
  }

  modifier(id: number, data: Partial<Categorie>): Observable<EcritureReponse<Categorie>> {
    return this.http.put<EcritureReponse<Categorie>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }
}
