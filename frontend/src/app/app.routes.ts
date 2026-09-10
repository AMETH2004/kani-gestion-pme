import { Routes } from '@angular/router';
import { authGuard, roleGuard } from './core/guards';

export const routes: Routes = [
  {
    path: 'connexion',
    loadComponent: () => import('./pages/login/login').then((m) => m.LoginComponent),
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layout/shell/shell').then((m) => m.ShellComponent),
    children: [
      { path: '', pathMatch: 'full', redirectTo: 'tableau-de-bord' },
      {
        path: 'tableau-de-bord',
        loadComponent: () =>
          import('./pages/dashboard/dashboard').then((m) => m.DashboardComponent),
      },
      {
        path: 'clients',
        loadComponent: () => import('./pages/clients/clients').then((m) => m.ClientsComponent),
      },
      {
        path: 'categories',
        loadComponent: () =>
          import('./pages/categories/categories').then((m) => m.CategoriesComponent),
      },
      {
        path: 'produits',
        loadComponent: () => import('./pages/produits/produits').then((m) => m.ProduitsComponent),
      },
      {
        path: 'stock',
        loadComponent: () => import('./pages/stock/stock').then((m) => m.StockComponent),
      },
      {
        path: 'devis',
        loadComponent: () => import('./pages/devis/devis-liste').then((m) => m.DevisListeComponent),
      },
      {
        path: 'devis/nouveau',
        loadComponent: () => import('./pages/devis/devis-form').then((m) => m.DevisFormComponent),
      },
      {
        path: 'devis/:id',
        loadComponent: () => import('./pages/devis/devis-form').then((m) => m.DevisFormComponent),
      },
      {
        path: 'factures',
        loadComponent: () =>
          import('./pages/factures/factures-liste').then((m) => m.FacturesListeComponent),
      },
      {
        path: 'factures/nouveau',
        loadComponent: () =>
          import('./pages/factures/facture-form').then((m) => m.FactureFormComponent),
      },
      {
        path: 'factures/:id',
        loadComponent: () =>
          import('./pages/factures/facture-detail').then((m) => m.FactureDetailComponent),
      },
      {
        path: 'parametres',
        canActivate: [roleGuard],
        data: { roles: ['Administrateur'] },
        loadComponent: () =>
          import('./pages/parametres/parametres').then((m) => m.ParametresComponent),
      },
    ],
  },
  { path: '**', redirectTo: '' },
];
