import { HttpInterceptorFn } from '@angular/common/http';

/**
 * Ajoute le token Bearer (stocké par AuthService sous la clé `token`) à toutes
 * les requêtes sortantes.
 */
export const authInterceptor: HttpInterceptorFn = (req, next) => {
  const token = localStorage.getItem('token');
  if (!token) {
    return next(req);
  }
  return next(
    req.clone({ setHeaders: { Authorization: `Bearer ${token}` } }),
  );
};
