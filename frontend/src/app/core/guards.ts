import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { AuthService } from './auth.service';

export const authGuard: CanActivateFn = () => {
  const auth = inject(AuthService);
  const router = inject(Router);

  if (auth.getToken()) {
    return true;
  }
  return router.createUrlTree(['/connexion']);
};

/**
 * Garde de rôle : `data.roles` liste les rôles autorisés.
 * Utilisée sur les routes d'administration et de paramétrage.
 */
export const roleGuard: CanActivateFn = (route) => {
  const auth = inject(AuthService);
  const router = inject(Router);
  const autorises = (route.data?.['roles'] as string[] | undefined) ?? [];

  if (autorises.length === 0 || auth.aRole(...autorises)) {
    return true;
  }
  return router.createUrlTree(['/tableau-de-bord']);
};
