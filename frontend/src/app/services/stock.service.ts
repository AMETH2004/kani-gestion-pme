import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { MouvementStock, Paginated, TypeMouvement } from '../core/models';
import { toParams } from '../core/api';

@Injectable({ providedIn: 'root' })
export class StockService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/stock`;

  mouvements(filtres: {
    produit_id?: number;
    type?: TypeMouvement;
    par_page?: number;
  } = {}): Observable<Paginated<MouvementStock>> {
    return this.http.get<Paginated<MouvementStock>>(`${this.url}/mouvements`, {
      params: toParams(filtres),
    });
  }
}
