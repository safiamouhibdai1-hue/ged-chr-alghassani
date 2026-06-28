<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class GedSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n\e[33m🌱 Début du seeding GED Médicale…\e[0m\n";

        // Désactiver les FK pour le truncate
        DB::statement('SET session_replication_role = replica');
        DB::table('log_activites')->truncate();
        DB::table('documents')->truncate();
        DB::table('patients')->truncate();
        DB::table('utilisateurs')->truncate();
        DB::statement('SET session_replication_role = DEFAULT');

        // Utilisateurs
        echo "  \e[34m👤 Création des utilisateurs…\e[0m\n";

        $adminId = DB::table('utilisateurs')->insertGetId([
            'nom'      => 'Benjelloun',
            'prenom'   => 'Ahmed',
            'email'    => 'a.benjelloun@chr.ma',
            'password' => Hash::make('admin123'),
            'role'     => 'administratif',
            'actif'    => true,
        ], 'id_utilisateur');

        $medecinId = DB::table('utilisateurs')->insertGetId([
            'nom'      => 'El Fassi',
            'prenom'   => 'Youssef',
            'email'    => 'elfassi@chr.ma',
            'password' => Hash::make('medecin123'),
            'role'     => 'medecin',
            'actif'    => true,
        ], 'id_utilisateur');

        $infirmierId = DB::table('utilisateurs')->insertGetId([
            'nom'      => 'Benali',
            'prenom'   => 'Fatima',
            'email'    => 'fatima.benali@chr.ma',
            'password' => Hash::make('infirmier123'),
            'role'     => 'infirmier',
            'actif'    => true,
        ], 'id_utilisateur');

        echo "     \e[32m✓ 3 utilisateurs créés\e[0m\n";

        // Patients
        echo "  \e[34m🏥 Création des patients…\e[0m\n";

        // [ipp, nom, prenom, cin, numero_dossier, date_naissance, service]
        $patients = [
            [1001, 'BENMOUSSA', 'Khalid',  'F890341', 'DOSS-2024-0001', '1978-03-15', 'Cardiologie'],
            [1002, 'ALAOUI',    'Meryem',  'M234567', 'DOSS-2024-0002', '1985-07-22', 'Neurologie'],
            [1003, 'TAZI',      'Hassan',  'P234567', 'DOSS-2024-0003', '1960-11-08', 'Chirurgie'],
            [1004, 'BERRADA',   'Amina',   'N456712', 'DOSS-2024-0004', '1992-04-30', 'Pediatrie'],
            [1005, 'CHERKAOUI', 'Rachid',  'G789023', 'DOSS-2024-0005', '1955-09-12', 'Cardiologie'],
            [1006, 'KETTANI',   'Nadia',   'H345612', 'DOSS-2024-0006', '1988-01-25', 'Laboratoire'],
            [1007, 'MANSOURI',  'Omar',    'L901234', 'DOSS-2024-0007', '1972-06-17', 'Radiologie'],
            [1008, 'ZIANI',     'Karima',  'S456789', 'DOSS-2024-0008', '1995-12-03', 'Urgences'],
            [1009, 'BENSOUDA',  'Amine',   'J123098', 'DOSS-2024-0009', '1968-08-20', 'Chirurgie'],
            [1010, 'LAHLOU',    'Siham',   'K678934', 'DOSS-2024-0010', '1980-05-14', 'Neurologie'],
            [1011, 'ALI',       'Mohib',   'D567821', 'DOSS-2026-0011', '2000-10-12', 'Radiologie'],
        ];

        $ipps = [];
        foreach ($patients as $p) {
            DB::table('patients')->insert([
                'ipp'            => $p[0],
                'nom'            => $p[1],
                'prenom'         => $p[2],
                'cin'            => $p[3],
                'numero_dossier' => $p[4],
                'date_naissance' => $p[5],
                'service'        => $p[6],
                'date_creation'  => now()->subMonths(rand(1, 18))->toDateString(),
            ]);
            $ipps[] = $p[0];
        }

        echo "     \e[32m✓ 10 patients créés\e[0m\n";

        // Documents
        echo "  \e[34m📁 Création des documents…\e[0m\n";

        $docs = [
            [$ipps[0], $medecinId,   'Bilan cardiologique complet',          'rapport_consultation',    'Cardiologie', '2024-10-15'],
            [$ipps[0], $medecinId,   'ECG Holter 24h',                       'resultat_laboratoire',    'Cardiologie', '2024-11-02'],
            [$ipps[1], $medecinId,   'IRM cérébrale — résultat',             'resultat_radiologie',     'Neurologie',  '2024-09-18'],
            [$ipps[2], $adminId,     'Compte rendu opératoire — appendicite','compte_rendu_operatoire', 'Chirurgie',   '2024-08-30'],
            [$ipps[3], $infirmierId, 'Ordonnance pédiatrique',               'ordonnance',              'Pediatrie',   '2025-09-14'],
            [$ipps[4], $medecinId,   'Rapport consultation cardiologie',     'rapport_consultation',    'Cardiologie', '2025-03-05'],
            [$ipps[5], $infirmierId, 'Résultat NFS + bilan complet',         'resultat_laboratoire',    'Laboratoire', '2024-10-28'],
            [$ipps[6], $medecinId,   'Radio thorax face + profil',           'resultat_radiologie',     'Radiologie',  '2025-06-22'],
            [$ipps[7], $infirmierId, 'Compte rendu urgences',                'rapport_consultation',    'Urgences',    '2025-11-07'],
            [$ipps[8], $adminId,     'Courrier médical transfert',           'courrier_medical',        'Chirurgie',   '2024-09-05'],
        ];

        $docIds = [];
        foreach ($docs as $d) {
            $docIds[] = DB::table('documents')->insertGetId([
                'ipp'            => $d[0],
                'id_utilisateur' => $d[1],
                'titre'          => $d[2],
                'typedocument'   => $d[3],
                'service'        => $d[4],
                'date_import'    => $d[5],
                'chemin_fichier' => 'documents/' . $d[0] . '/2024/' . str_replace(' ', '_', strtolower($d[2])) . '.pdf',
            ], 'id_docum');
        }

        echo "     \e[32m✓ 10 documents créés\e[0m\n";

        // Journal d'audit
        echo "  \e[34m📋 Création du journal d'audit…\e[0m\n";

        $logs = [
            [$adminId,    null,       'CONNEXION administrateur',                          '127.0.0.1', '2024-12-10 08:14:22'],
            [$medecinId,  null,       'CONNEXION médecin Dr. El Fassi',                   '192.168.1.10', '2024-12-10 08:47:05'],
            [$medecinId,  $docIds[0], 'CONSULTATION document #'.$docIds[0],              '192.168.1.10', '2024-12-10 09:02:33'],
            [$medecinId,  $docIds[0], 'UPLOAD document "Bilan cardiologique complet"',   '192.168.1.10', '2024-10-15 08:22:15'],
            [$medecinId,  $docIds[2], 'UPLOAD document "IRM cérébrale"',                 '192.168.1.10', '2024-09-18 09:48:03'],
            [$infirmierId,null,       'CONNEXION infirmière Fatima Benali',               '192.168.1.20', '2024-12-09 08:05:51'],
            [$infirmierId,$docIds[4], 'UPLOAD document "Ordonnance pédiatrique"',        '192.168.1.20', '2024-12-05 13:27:44'],
            [$adminId,    $docIds[3], 'UPLOAD document "CR opératoire appendicite"',     '127.0.0.1', '2024-08-30 15:12:08'],
            [$medecinId,  null,       'RECHERCHE documents service=Cardiologie',          '192.168.1.10', '2024-12-09 09:40:12'],
            [$adminId,    null,       'CONNEXION administrateur',                          '127.0.0.1', '2024-12-09 11:38:27'],
            [$medecinId,  $docIds[5], 'CONSULTATION document #'.$docIds[5],              '192.168.1.10', '2024-11-21 09:15:36'],
            [$infirmierId,$docIds[6], 'UPLOAD document "Résultat NFS"',                  '192.168.1.20', '2024-10-28 14:42:53'],
            [$medecinId,  $docIds[7], 'CONSULTATION document #'.$docIds[7],              '192.168.1.10', '2024-12-02 16:08:21'],
            [$adminId,    null,       'CREATION dossier patient #'.$ipps[7],             '127.0.0.1', '2024-12-10 09:31:17'],
            [$medecinId,  $docIds[1], 'CONSULTATION document #'.$docIds[1],              '192.168.1.10', '2024-11-03 10:33:47'],
            [$infirmierId,$docIds[8], 'UPLOAD document "CR urgences"',                   '192.168.1.20', '2024-12-10 17:05:12'],
            [$adminId,    $docIds[9], 'UPLOAD document "Courrier médical"',              '127.0.0.1', '2024-09-05 12:19:38'],
        ];

        foreach ($logs as $l) {
            DB::table('log_activites')->insert([
                'id_utilisateur' => $l[0],
                'id_docum'       => $l[1],
                'description'    => $l[2],
                'adresse_ip'     => $l[3],
                'date_action'    => $l[4],
            ]);
        }

        echo "     \e[32m✓ " . \count($logs) . " entrées d'audit créées\e[0m\n";

        echo "\n\e[32m✅ Seeding terminé avec succès !\e[0m\n\n";

        // Afficher les comptes
        $this->command->table(
            ['Compte', 'Email', 'Mot de passe', 'Rôle'],
            [
                ['Admin',        'a.benjelloun@chr.ma',  'admin123',     'Administratif'],
                ['Dr. El Fassi', 'elfassi@chr.ma',       'medecin123',   'Médecin'],
                ['Inf. Benali',  'fatima.benali@chr.ma', 'infirmier123', 'Infirmier(e)'],
            ]
        );
    }
}
