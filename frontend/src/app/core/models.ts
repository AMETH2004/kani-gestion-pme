/** Types partagés avec l'API Laravel (kani-backend). */

export interface Role {
  id: number;
  name: string;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  telephone?: string | null;
  poste?: string | null;
  actif: boolean;
  roles?: Role[];
}

export interface LoginResponse {
  status: boolean;
  message: string;
  token: string;
  user: AuthUser;
  role: string;
}

/** Réponse paginée standard de Laravel (`->paginate()`). */
export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface Categorie {
  id: number;
  nom: string;
  description?: string | null;
  produits_count?: number;
}

export interface Client {
  id: number;
  code: string;
  nom: string;
  type: 'particulier' | 'entreprise';
  telephone?: string | null;
  email?: string | null;
  adresse?: string | null;
  ninea?: string | null;
  notes?: string | null;
  actif: boolean;
  solde?: number;
  factures_count?: number;
  devis_count?: number;
}

export interface Produit {
  id: number;
  reference: string;
  designation: string;
  description?: string | null;
  categorie_id?: number | null;
  categorie?: Categorie | null;
  prix_achat: number;
  prix_vente: number;
  taux_tva: number;
  unite: string;
  stock_actuel: number;
  stock_alerte: number;
  gerer_stock: boolean;
  actif: boolean;
  stock_bas?: boolean;
}

export type TypeMouvement = 'entree' | 'sortie' | 'ajustement';

export interface MouvementStock {
  id: number;
  produit_id: number;
  produit?: Pick<Produit, 'id' | 'reference' | 'designation' | 'unite'>;
  type: TypeMouvement;
  quantite: number;
  stock_avant: number;
  stock_apres: number;
  motif?: string | null;
  reference_document?: string | null;
  user?: { id: number; name: string };
  date_mouvement: string;
}

export interface LigneDocument {
  id?: number;
  produit_id?: number | null;
  designation: string;
  quantite: number;
  prix_unitaire: number;
  taux_tva: number;
  montant_ht?: number;
}

export type StatutDevis =
  | 'brouillon'
  | 'envoye'
  | 'accepte'
  | 'refuse'
  | 'expire'
  | 'converti';

export interface Devis {
  id: number;
  numero: string;
  client_id: number;
  client?: Client;
  date_devis: string;
  date_validite?: string | null;
  statut: StatutDevis;
  montant_ht: number;
  montant_remise: number;
  montant_tva: number;
  montant_ttc: number;
  notes?: string | null;
  lignes?: LigneDocument[];
  lignes_count?: number;
  facture?: { id: number; numero: string } | null;
}

export type StatutFacture =
  | 'brouillon'
  | 'envoyee'
  | 'partiellement_payee'
  | 'payee'
  | 'en_retard'
  | 'annulee';

export interface Paiement {
  id: number;
  facture_id: number;
  montant: number;
  mode: 'especes' | 'cheque' | 'virement' | 'wave' | 'orange_money' | 'autre';
  reference?: string | null;
  date_paiement: string;
  notes?: string | null;
  user?: { id: number; name: string };
}

export interface Facture {
  id: number;
  numero: string;
  client_id: number;
  client?: Client;
  devis_id?: number | null;
  date_facture: string;
  date_echeance?: string | null;
  statut: StatutFacture;
  montant_ht: number;
  montant_remise: number;
  montant_tva: number;
  montant_ttc: number;
  montant_paye: number;
  reste_a_payer?: number;
  notes?: string | null;
  lignes?: LigneDocument[];
  lignes_count?: number;
  paiements?: Paiement[];
}

export interface Entreprise {
  id: number;
  raison_sociale: string;
  ninea?: string | null;
  rccm?: string | null;
  adresse?: string | null;
  telephone?: string | null;
  email?: string | null;
  site_web?: string | null;
  devise: string;
  taux_tva_defaut: number;
  pied_de_page?: string | null;
}

export interface DashboardData {
  ca_mois: number;
  encaisse_mois: number;
  impayes_total: number;
  nb_clients: number;
  nb_produits: number;
  nb_factures_impayees: number;
  produits_stock_bas: number;
  dernieres_factures: Facture[];
  ca_6_mois: { mois: string; montant: number }[];
}
