import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Entreprise } from '../core/models';
import { EcritureReponse } from '../core/api';

@Injectable({ providedIn: 'root' })
export class EntrepriseService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/entreprise`;

  charger(): Observable<Entreprise> {
    return this.http.get<Entreprise>(this.url);
  }

  enregistrer(data: Partial<Entreprise>): Observable<EcritureReponse<Entreprise>> {
    return this.http.put<EcritureReponse<Entreprise>>(this.url, data);
  }
}
