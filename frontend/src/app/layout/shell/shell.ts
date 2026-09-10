import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink, RouterLinkActive, RouterOutlet, Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';

interface LienMenu {
  libelle: string;
  route: string;
  icone: string;
  roles?: string[];
}

@Component({
  selector: 'app-shell',
  imports: [RouterOutlet, RouterLink, RouterLinkActive],
  templateUrl: './shell.html',
  styleUrl: './shell.scss',
})
export class ShellComponent implements OnInit {
  private auth = inject(AuthService);
  private router = inject(Router);

  readonly user = this.auth.user;
  readonly role = this.auth.role;
  readonly menuOuvert = signal(false);

  ngOnInit(): void {
    // Revalide le token au chargement ; l'errorInterceptor redirige si 401.
    this.auth.rafraichir().subscribe({ error: () => {} });
  }

  private readonly liens: LienMenu[] = [
    { libelle: 'Tableau de bord', route: '/tableau-de-bord', icone: 'bi-speedometer2' },
    { libelle: 'Clients', route: '/clients', icone: 'bi-people' },
    { libelle: 'Devis', route: '/devis', icone: 'bi-file-earmark-text' },
    { libelle: 'Factures', route: '/factures', icone: 'bi-receipt' },
    { libelle: 'Produits', route: '/produits', icone: 'bi-box-seam' },
    { libelle: 'Catégories', route: '/categories', icone: 'bi-tags' },
    { libelle: 'Stock', route: '/stock', icone: 'bi-arrow-left-right' },
    {
      libelle: 'Paramètres',
      route: '/parametres',
      icone: 'bi-gear',
      roles: ['Administrateur'],
    },
  ];

  get liensVisibles(): LienMenu[] {
    return this.liens.filter((l) => !l.roles || this.auth.aRole(...l.roles));
  }

  basculerMenu(): void {
    this.menuOuvert.update((v) => !v);
  }

  fermerMenu(): void {
    this.menuOuvert.set(false);
  }

  deconnexion(): void {
    this.auth.logout();
    this.router.navigate(['/connexion']);
  }
}
