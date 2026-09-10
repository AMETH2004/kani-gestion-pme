import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { DashboardData } from '../core/models';

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private http = inject(HttpClient);

  charger(): Observable<DashboardData> {
    return this.http.get<DashboardData>(`${environment.apiUrl}/dashboard`);
  }
}
