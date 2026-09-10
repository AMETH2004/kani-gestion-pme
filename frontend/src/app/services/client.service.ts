import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Client, Paginated } from '../core/models';
import { EcritureReponse, toParams } from '../core/api';

@Injectable({ providedIn: 'root' })
export class ClientService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/clients`;

  liste(filtres: {
    recherche?: string;
    actifs_seulement?: boolean;
    par_page?: number;
  } = {}): Observable<Paginated<Client>> {
    return this.http.get<Paginated<Client>>(this.url, { params: toParams(filtres) });
  }

  un(id: number): Observable<Client> {
    return this.http.get<Client>(`${this.url}/${id}`);
  }

  creer(data: Partial<Client>): Observable<EcritureReponse<Client>> {
    return this.http.post<EcritureReponse<Client>>(this.url, data);
  }

  modifier(id: number, data: Partial<Client>): Observable<EcritureReponse<Client>> {
    return this.http.put<EcritureReponse<Client>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }
}
