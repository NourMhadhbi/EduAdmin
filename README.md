# EduAdmin (OnLearn) — Plateforme de Gestion des Présences

Application web PHP (architecture MVC "maison") de gestion pédagogique permettant à un établissement de gérer les utilisateurs (administrateurs, enseignants, étudiants), les cours, les séances et le suivi des présences — avec une option de pointage par **reconnaissance faciale** (Python/DeepFace).

## ✨ Fonctionnalités principales

- **Authentification & comptes** : inscription, connexion, mot de passe oublié / réinitialisation par email, vérification par code, gestion du profil.
- **Trois rôles** : Administrateur, Enseignant, Étudiant, chacun avec son propre tableau de bord (`Vue/Administrateur`, `Vue/Enseignant`, `Vue/Etudiants`).
- **Gestion des cours** : création/attribution de cours, inscription des étudiants aux cours (`inscriptionCours`).
- **Gestion des séances et présences** :
  - Marquage manuel présent/absent par séance.
  - Reconnaissance faciale pour le pointage automatique (script Python `check_face.py` avec la librairie **DeepFace**).
  - Historique des présences et taux de participation par étudiant.
  - **Export des présences** en Excel et en PDF (via **Dompdf**).
- **Notifications** aux utilisateurs.
- **Emails automatiques** (via **PHPMailer** + SMTP Gmail) pour la vérification de compte et la réinitialisation de mot de passe.
- **Administration** : liste des utilisateurs, suivi et contrôle des comptes.

## 🏗️ Architecture

Le projet suit un modèle **MVC simplifié** :

```
projet_PHP_TP/
├── Vue/                 # Interfaces (une vue par rôle/fonctionnalité)
│   ├── Administrateur/
│   ├── Enseignant/
│   ├── Etudiants/
│   ├── Authentification/
│   ├── Profil/
│   └── NavBar/
├── Controller/          # Logique métier (un contrôleur par entité)
├── Model/               # Accès aux données (PDO)
├── Config/
│   └── database.php     # Connexion PDO en Singleton
├── Connexion/
├── python/
│   └── check_face.py    # Reconnaissance faciale (DeepFace)
├── Css/ · Javascript/ · Assets/ · Footer/
├── sql_templates.sql    # Modèles SQL pour créer des utilisateurs/cours de test
├── setup_test_data.php  # Script d'initialisation de données de test
└── set_password.php     # Outil pour définir/hacher un mot de passe
```

Autres fichiers à la racine :
- `composer.json` / `composer.lock` / `vendor/` : dépendances PHP (PHPMailer, Dompdf, etc.)
- `dbonlearn (3).sql` : export complet de la base de données

## 🗄️ Base de données

Base MySQL nommée par défaut `dbonlearn`, avec les tables suivantes :

| Table | Rôle |
|---|---|
| `utilisateur` | Table centrale des comptes (nom, email, mot de passe, rôle...) |
| `administrateur`, `enseignant`, `etudiant` | Spécialisation par rôle |
| `cours` | Cours proposés |
| `inscriptioncours` | Inscription des étudiants aux cours |
| `seance` | Séances associées à un cours |
| `presence` | Présence/absence d'un étudiant à une séance |
| `notification` | Notifications utilisateurs |
| `systemereconnaissance` | Données liées à la reconnaissance faciale |

Le fichier `dbonlearn (3).sql` contient le schéma complet et peut être importé tel quel (ex. via phpMyAdmin ou `mysql -u root -p dbonlearn < "dbonlearn (3).sql"`).

## ⚙️ Prérequis

- PHP ≥ 7.4 (extension PDO MySQL activée)
- Serveur MySQL/MariaDB
- Composer
- Python 3 + la librairie `deepface` (uniquement si la reconnaissance faciale est utilisée)
- Un serveur web local (XAMPP/WAMP/MAMP ou `php -S`)

## 🚀 Installation

1. **Cloner / copier le projet** dans le dossier de votre serveur web (ex. `htdocs/` pour XAMPP).
2. **Installer les dépendances PHP** :
   ```bash
   composer install
   ```
3. **Créer la base de données** et l'importer :
   ```bash
   mysql -u root -p -e "CREATE DATABASE dbonlearn"
   mysql -u root -p dbonlearn < "dbonlearn (3).sql"
   ```
4. **Configurer la connexion** à la base de données (`Config/database.php`) via variables d'environnement, sinon les valeurs par défaut sont utilisées :
   - `DB_HOST` (défaut : `localhost`)
   - `DB_NAME` (défaut : `dbonlearn`)
   - `DB_USER` (défaut : `root`)
   - `DB_PASS` (défaut : vide)
5. **(Optionnel) Reconnaissance faciale** : installer les dépendances Python
   ```bash
   pip install deepface
   ```
6. **Lancer le serveur** et accéder à la page de connexion :
   ```
   http://localhost/projet_PHP_TP/projet_PHP_TP/Vue/Authentification/connexion.php
   ```
7. **(Optionnel) Générer des données de test** avec `setup_test_data.php` et/ou les modèles fournis dans `sql_templates.sql`. Utiliser ensuite `set_password.php` pour définir un mot de passe (haché) aux comptes créés.

## ⚠️ Points de sécurité à corriger avant mise en production

- Les identifiants SMTP (email et mot de passe d'application Gmail) sont **codés en dur** dans `Controller/configEmail.php`. Il est recommandé de les déplacer vers des variables d'environnement, comme cela est déjà fait pour la base de données dans `Config/database.php`.
- Vérifier que `debug_log.txt` (présent dans `Controller/`) n'est pas exposé publiquement et ne contient pas de données sensibles.
- Les mots de passe doivent être stockés hachés (via `password_hash`) — `set_password.php` semble dédié à cela ; s'assurer qu'aucun mot de passe en clair n'est inséré directement en base (voir les `$2y$10$placeholder` dans `sql_templates.sql`, à remplacer par de vrais hachages).

## 👥 Rôles et comptes

Trois rôles gérés : `administrateur`, `enseignant`, `etudiant`. Le fichier `sql_templates.sql` fournit des exemples prêts à l'emploi pour créer rapidement plusieurs étudiants, enseignants et cours de test.

## 📄 Licence

Projet académique (TP) — aucune licence spécifique fournie. Les dépendances tierces (PHPMailer, Dompdf, php-svg-lib, php-font-lib, sabberworm/php-css-parser, masterminds/html5) conservent leurs licences respectives (voir `vendor/*/README.md`).
