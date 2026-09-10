import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { NotificationService } from './notification.service';

/**
 * Télécharge un fichier protégé (PDF de facture / devis) : l'appel passe par
 * HttpClient pour porter le token Bearer, puis on ouvre le blob dans un onglet.
 */
@Injectable({ providedIn: 'root' })
export class TelechargementService {
  private http = inject(HttpClient);
  private notif = inject(NotificationService);

  ouvrirPdf(url: string): void {
    this.http.get(url, { responseType: 'blob' }).subscribe({
      next: (blob) => {
        const objectUrl = URL.createObjectURL(blob);
        window.open(objectUrl, '_blank');
        setTimeout(() => URL.revokeObjectURL(objectUrl), 60_000);
      },
      error: () => this.notif.erreur('Impossible de générer le PDF.'),
    });
  }
}
