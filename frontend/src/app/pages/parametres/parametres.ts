import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { EntrepriseService } from '../../services/entreprise.service';
import { UtilisateurService, UtilisateurPayload } from '../../services/utilisateur.service';
import { AuthUser } from '../../core/models';
import { NotificationService } from '../../core/notification.service';
import { ModalComponent } from '../../shared/modal';

@Component({
  selector: 'app-parametres',
  imports: [ReactiveFormsModule, ModalComponent],
  templateUrl: './parametres.html',
})
export class ParametresComponent implements OnInit {
  private fb = inject(FormBuilder);
  private entrepriseService = inject(EntrepriseService);
  private utilisateurService = inject(UtilisateurService);
  private notif = inject(NotificationService);

  readonly ongletUtilisateurs = signal(false);
  readonly enregistrementEntreprise = signal(false);

  readonly formEntreprise = this.fb.nonNullable.group({
    raison_sociale: ['', Validators.required],
    ninea: [''],
    rccm: [''],
    adresse: [''],
    telephone: [''],
    email: [''],
    site_web: [''],
    devise: ['XOF', Validators.required],
    taux_tva_defaut: [18, [Validators.required, Validators.min(0), Validators.max(100)]],
    pied_de_page: [''],
  });

  readonly utilisateurs = signal<AuthUser[]>([]);
  readonly roles = signal<string[]>([]);
  readonly modalUser = signal(false);
  readonly editionUserId = signal<number | null>(null);
  readonly enregistrementUser = signal(false);

  readonly formUser = this.fb.nonNullable.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    telephone: [''],
    poste: [''],
    password: [''],
    role: ['Gestionnaire', Validators.required],
    actif: [true],
  });

  ngOnInit(): void {
    this.entrepriseService.charger().subscribe((e) =>
      this.formEntreprise.patchValue({
        raison_sociale: e.raison_sociale,
        ninea: e.ninea ?? '',
        rccm: e.rccm ?? '',
        adresse: e.adresse ?? '',
        telephone: e.telephone ?? '',
        email: e.email ?? '',
        site_web: e.site_web ?? '',
        devise: e.devise,
        taux_tva_defaut: Number(e.taux_tva_defaut),
        pied_de_page: e.pied_de_page ?? '',
      }),
    );
    this.utilisateurService.roles().subscribe((r) => this.roles.set(r));
    this.chargerUtilisateurs();
  }

  chargerUtilisateurs(): void {
    this.utilisateurService.liste().subscribe((u) => this.utilisateurs.set(u));
  }

  enregistrerEntreprise(): void {
    if (this.formEntreprise.invalid) {
      this.formEntreprise.markAllAsTouched();
      return;
    }
    this.enregistrementEntreprise.set(true);
    this.entrepriseService.enregistrer(this.formEntreprise.getRawValue()).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.enregistrementEntreprise.set(false);
      },
      error: () => this.enregistrementEntreprise.set(false),
    });
  }

  ouvrirCreationUser(): void {
    this.editionUserId.set(null);
    this.formUser.reset({ role: 'Gestionnaire', actif: true });
    this.formUser.controls.password.addValidators(Validators.required);
    this.formUser.controls.password.updateValueAndValidity();
    this.modalUser.set(true);
  }

  ouvrirEditionUser(u: AuthUser): void {
    this.editionUserId.set(u.id);
    this.formUser.reset({
      name: u.name,
      email: u.email,
      telephone: u.telephone ?? '',
      poste: u.poste ?? '',
      password: '',
      role: u.roles?.[0]?.name ?? 'Gestionnaire',
      actif: u.actif,
    });
    this.formUser.controls.password.clearValidators();
    this.formUser.controls.password.updateValueAndValidity();
    this.modalUser.set(true);
  }

  enregistrerUser(): void {
    if (this.formUser.invalid) {
      this.formUser.markAllAsTouched();
      return;
    }
    this.enregistrementUser.set(true);
    const v = this.formUser.getRawValue();
    const payload: Partial<UtilisateurPayload> = {
      name: v.name,
      email: v.email,
      telephone: v.telephone || null,
      poste: v.poste || null,
      role: v.role,
      actif: v.actif,
    };
    if (v.password) payload.password = v.password;

    const id = this.editionUserId();
    const req = id
      ? this.utilisateurService.modifier(id, payload)
      : this.utilisateurService.creer(payload as UtilisateurPayload);

    req.subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalUser.set(false);
        this.enregistrementUser.set(false);
        this.chargerUtilisateurs();
      },
      error: () => this.enregistrementUser.set(false),
    });
  }

  supprimerUser(u: AuthUser): void {
    if (!confirm(`Supprimer l'utilisateur ${u.name} ?`)) return;
    this.utilisateurService.supprimer(u.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.chargerUtilisateurs();
      },
    });
  }
}
