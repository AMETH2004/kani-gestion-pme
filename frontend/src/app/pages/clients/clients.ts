import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ClientService } from '../../services/client.service';
import { Client } from '../../core/models';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { ModalComponent } from '../../shared/modal';
import { MontantPipe } from '../../shared/montant.pipe';

@Component({
  selector: 'app-clients',
  imports: [ReactiveFormsModule, ModalComponent, MontantPipe],
  templateUrl: './clients.html',
})
export class ClientsComponent implements OnInit {
  private service = inject(ClientService);
  private fb = inject(FormBuilder);
  private notif = inject(NotificationService);
  private auth = inject(AuthService);

  readonly clients = signal<Client[]>([]);
  readonly chargement = signal(true);
  readonly recherche = signal('');
  readonly modalOuvert = signal(false);
  readonly enregistrement = signal(false);
  readonly editionId = signal<number | null>(null);

  readonly peutModifier = this.auth.aRole('Administrateur', 'Gestionnaire');

  readonly form = this.fb.nonNullable.group({
    nom: ['', Validators.required],
    type: ['particulier' as 'particulier' | 'entreprise', Validators.required],
    telephone: [''],
    email: [''],
    adresse: [''],
    ninea: [''],
    notes: [''],
    actif: [true],
  });

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service.liste({ recherche: this.recherche() }).subscribe({
      next: (page) => {
        this.clients.set(page.data);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  ouvrirCreation(): void {
    this.editionId.set(null);
    this.form.reset({ type: 'particulier', actif: true });
    this.modalOuvert.set(true);
  }

  ouvrirEdition(c: Client): void {
    this.editionId.set(c.id);
    this.form.reset({
      nom: c.nom,
      type: c.type,
      telephone: c.telephone ?? '',
      email: c.email ?? '',
      adresse: c.adresse ?? '',
      ninea: c.ninea ?? '',
      notes: c.notes ?? '',
      actif: c.actif,
    });
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

  supprimer(c: Client): void {
    if (!confirm(`Supprimer le client « ${c.nom} » ?`)) return;
    this.service.supprimer(c.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }
}
