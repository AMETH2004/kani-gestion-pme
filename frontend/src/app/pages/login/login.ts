import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  templateUrl: './login.html',
  styleUrl: './login.scss',
})
export class LoginComponent {
  private fb = inject(FormBuilder);
  private auth = inject(AuthService);
  private router = inject(Router);
  private notif = inject(NotificationService);

  readonly enCours = signal(false);

  readonly form = this.fb.nonNullable.group({
    email: ['admin@kani.test', [Validators.required, Validators.email]],
    password: ['password', [Validators.required]],
  });

  soumettre(): void {
    if (this.form.invalid || this.enCours()) {
      this.form.markAllAsTouched();
      return;
    }
    this.enCours.set(true);
    const { email, password } = this.form.getRawValue();

    this.auth.login(email, password).subscribe({
      next: () => {
        this.notif.succes('Bienvenue sur Kani.');
        this.router.navigateByUrl(this.auth.routeAccueil());
      },
      error: () => {
        this.enCours.set(false);
        this.notif.erreur('Identifiants incorrects.');
      },
    });
  }
}
