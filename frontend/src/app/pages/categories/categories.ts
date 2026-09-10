import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { CategorieService } from '../../services/categorie.service';
import { Categorie } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { ModalComponent } from '../../shared/modal';

@Component({
  selector: 'app-categories',
  imports: [ReactiveFormsModule, ModalComponent],
  templateUrl: './categories.html',
})
export class CategoriesComponent implements OnInit {
  private service = inject(CategorieService);
  private fb = inject(FormBuilder);
  private notif = inject(NotificationService);
  private auth = inject(AuthService);

  readonly categories = signal<Categorie[]>([]);
  readonly chargement = signal(true);
  readonly modalOuvert = signal(false);
  readonly editionId = signal<number | null>(null);
  readonly enregistrement = signal(false);

  readonly peutModifier = this.auth.aRole('Administrateur', 'Magasinier');

  readonly form = this.fb.nonNullable.group({
    nom: ['', Validators.required],
    description: [''],
  });

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service.liste().subscribe({
      next: (c) => {
        this.categories.set(c);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  ouvrirCreation(): void {
    this.editionId.set(null);
    this.form.reset();
    this.modalOuvert.set(true);
  }

  ouvrirEdition(c: Categorie): void {
    this.editionId.set(c.id);
    this.form.reset({ nom: c.nom, description: c.description ?? '' });
    this.modalOuvert.set(true);
  }

  enregistrer(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.enregistrement.set(true);
    const data = this.form.getRawValue();
    const id = this.editionId();
    const req = id ? this.service.modifier(id, data) : this.service.creer(data);
    req.subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalOuvert.set(false);
        this.enregistrement.set(false);
        this.charger();
      },
      error: () => this.enregistrement.set(false),
    });
  }

  supprimer(c: Categorie): void {
    if (!confirm(`Supprimer la catégorie « ${c.nom} » ?`)) return;
    this.service.supprimer(c.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }
}
