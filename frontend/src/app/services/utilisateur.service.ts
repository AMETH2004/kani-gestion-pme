import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { AuthUser } from '../core/models';
import { EcritureReponse } from '../core/api';

export interface UtilisateurPayload {
  name: string;
  email: string;
  telephone?: string | null;
  poste?: string | null;
  password?: string;
  role: string;
  actif?: boolean;
}

@Injectable({ providedIn: 'root' })
export class UtilisateurService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/users`;

  liste(): Observable<AuthUser[]> {
    return this.http.get<AuthUser[]>(this.url);
  }

  roles(): Observable<string[]> {
    return this.http.get<string[]>(`${environment.apiUrl}/roles`);
  }

  creer(data: UtilisateurPayload): Observable<EcritureReponse<AuthUser>> {
    return this.http.post<EcritureReponse<AuthUser>>(this.url, data);
  }

  modifier(id: number, data: Partial<UtilisateurPayload>): Observable<EcritureReponse<AuthUser>> {
    return this.http.put<EcritureReponse<AuthUser>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }
}
