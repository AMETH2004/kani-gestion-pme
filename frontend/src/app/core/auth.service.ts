import { Injectable, computed, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { environment } from '../../environments/environment';
import { AuthUser, LoginResponse } from './models';

const TOKEN_KEY = 'token';
const USER_KEY = 'user';
const ROLE_KEY = 'user_role';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly apiUrl = environment.apiUrl;

  /** État réactif consommé par le layout (menu, garde de rôle...). */
  readonly user = signal<AuthUser | null>(this.lireUser());
  readonly role = signal<string | null>(localStorage.getItem(ROLE_KEY));
  readonly estConnecte = computed(() => this.user() !== null);

  constructor(private http: HttpClient) {}

  login(email: string, password: string): Observable<LoginResponse> {
    return this.http
      .post<LoginResponse>(`${this.apiUrl}/login`, { email, password })
      .pipe(tap((res) => this.enregistrerSession(res)));
  }

  logout(): void {
    this.http.post(`${this.apiUrl}/logout`, {}).subscribe({
      next: () => this.effacerSession(),
      error: () => this.effacerSession(),
    });
  }

  /** Recharge l'utilisateur depuis l'API (au démarrage de l'app). */
  rafraichir(): Observable<{ user: AuthUser; role: string }> {
    return this.http
      .get<{ user: AuthUser; role: string }>(`${this.apiUrl}/user`)
      .pipe(
        tap(({ user, role }) => {
          this.user.set(user);
          this.role.set(role);
          localStorage.setItem(USER_KEY, JSON.stringify(user));
          localStorage.setItem(ROLE_KEY, role);
        }),
      );
  }

  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  aRole(...roles: string[]): boolean {
    const r = this.role();
    return r !== null && roles.includes(r);
  }

  /** Route d'atterrissage après connexion (identique pour tous les rôles ici). */
  routeAccueil(): string {
    return '/tableau-de-bord';
  }

  private enregistrerSession(res: LoginResponse): void {
    localStorage.setItem(TOKEN_KEY, res.token);
    localStorage.setItem(USER_KEY, JSON.stringify(res.user));
    localStorage.setItem(ROLE_KEY, res.role);
    this.user.set(res.user);
    this.role.set(res.role);
  }

  private effacerSession(): void {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    localStorage.removeItem(ROLE_KEY);
    this.user.set(null);
    this.role.set(null);
  }

  private lireUser(): AuthUser | null {
    const raw = localStorage.getItem(USER_KEY);
    return raw ? (JSON.parse(raw) as AuthUser) : null;
  }
}
