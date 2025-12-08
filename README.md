[#PHOTOGRAPHER](../../Downloads/photographer-20251120T093028Z-1-001/photographer/README.md)

[lien vers figma](https://www.figma.com/design/xN4vMpYPqH2074DQ9ANA8b/Projet-Artisan-phographe?node-id=29-4&t=axJwRWDk4jx4UhoA-0)

## 🔐 État actuel et améliorations prévues

### Configuration actuelle (développement)

````php
// app/includes/connect.php
$user = 'root';      // Utilisateur par défaut XAMPP
$password = '';      // Pas de mot de passe en local


`### 🔧 Améliorations de sécurité prévues
> "Actuellement, le projet utilise la configuration XAMPP par défaut parce que je suis en développement local. Je sais que pour la mise en production, il faudra :
> 1. Créer un utilisateur MySQL avec seulement les droits nécessaires
> 2. Mettre les identifiants dans un fichier `.env` qui n'est pas dans Git
> 3. Chiffrer la connexion avec SSL si possible"

1. **Base de données**
   - [ ] Créer un utilisateur dédié (ex: `photographer_app`)
   - [ ] Donner uniquement les droits nécessaires (SELECT, INSERT, UPDATE, DELETE)
   - [ ] Jamais les droits DROP ou GRANT

2. **Configuration**
   - [ ] Créer un fichier `.env` à la racine :
     ```
     DB_HOST=localhost
     DB_NAME=photographer
     DB_USER=photographer_app
     DB_PASS=MonMot2PasseComplexe!
     ```
   - [ ] Ajouter `.env` dans `.gitignore`

3. **Code**
   - [ ] Modifier `connect.php` pour lire le `.env`
   - [ ] Ajouter un fallback pour le développement local``

votre-projet/
├── .env                    ⬅️ FICHIER (caché, commence par un point)
├── app/
├── includes/
└── index.html
````

# .env - Fichier séparé

DB_USER=mon_utilisateur
DB_PASS=mon_mot_de_passe_secret
// connect.php - Maintenant il lit le .env
$user = $_ENV['DB_USER'];        // ← Lit depuis .env
$password = $\_ENV['DB_PASS']; // ← Lit depuis .env

### 👥 Politique de contrôle d'accès

L'application implémente une gestion des rôles à deux niveaux :

#### Rôle "admin"

-   **Accès** : Tableau de bord, gestion articles, gestion documents
-   **Actions** : Créer/modifier/supprimer du contenu
-   **Exemple** : `documents.php` - Tous les admins peuvent uploader

#### Rôle "superadmin"

-   **Accès** : Toutes les fonctionnalités admin
-   **Actions** : Gestion des comptes administrateurs
-   **Exemple** : `gestion_admin.php` - Uniquement superadmin

#### Justification

Cette séparation permet :

-   **Délégation** : Des admins peuvent gérer le contenu sans accès sensible
-   **Sécurité** : La gestion des comptes reste réservée
-   **Flexibilité** : Attribution des droits selon les besoins

### deploiement

O2Switch - Hébergeur français

PHP 8.2+ supporté

Extension fileinfo activée

Panel Plesk intuitif

Support technique français

Environ 5€/mois HT

🔧 Outils nécessaires
FileZilla (gratuit) : transfert des fichiers

phpMyAdmin (inclus) : gestion base de données

Éditeur texte : modification configuration

📂 Structure de transfert
text
ordinateur → FileZilla → O2Switch
PHOTOGRAPHER/
├── 📁 admin/ # Interface d'administration
│ ├── 📁 includes/ # Fichiers de configuration
│ │ ├── config.php # Configuration BDD
│ │ ├── auth.php # Authentification
│ │ └── head.php # En-tête commun
│ ├── 📄 articles.php # Gestion des articles
│ ├── 📄 modify_article.php # Modification article
│ ├── 📄 update_article.php # Traitement modification
│ ├── 📄 delete_article.php # Suppression article
│ └── 📄 dashboard.php # Tableau de bord
│
├── 📁 assets/ # Ressources frontend
│ ├── 📁 js/
│ │ ├── gestion_articles.js
│ │ ├── index.js
│ │ ├── modal_doc.js
│ │ └── uploads.js
│ └── 📁 style/ # Feuilles de style
│
├── 📁 uploads/ # Images uploadées
├── 📁 images/ # Images statiques
│
├── 📁 bdd/ fichier qui sera protégé # Documentation base
│ ├── bdd_unizusPhoto.txt # Structure SQL
│ ├── bdd-photographer-mcd.jpg
│ ├── bdd-photographer-mld.jpg
│ └── bdd-photographer-uml.jpg
│
├── 📄 index.html # Page d'accueil
├── 📄 .htaccess # Configuration Apache
├── 📄 accessibilite.html # Page accessibilité
├── 📄 droits-auteurs.html # Droits d'auteur
├── 📄 mentions.html # Mentions légales
└── 📄 README.md # Cette documentation
⚙️ Configuration PHP (O2Switch)
Dans includes/config.php :

php
$host = "localhost";           // Toujours localhost chez O2Switch
$dbname = "votre_base"; // Nom donné dans Plesk
$user = "votre_utilisateur";   // Utilisateur MySQL
$password = "votre_mdp"; // Mot de passe MySQL
🗄️ Base de données
Dans Plesk → Bases de données

Créer une nouvelle base MySQL

Noter : nom base, utilisateur, mot de passe

Dans phpMyAdmin → Importer database.sql

✅ Vérifications après déploiement
Site accessible : https://votredomaine.com

Interface admin : /admin/

Upload d'images fonctionnel

Capitalisation titres active

Mobile responsive OK

📞 Support
O2Switch : support@o2switch.fr - 04 44 44 60 40

FileZilla : filezilla-project.org

⏱️ Temps estimé
Transfert fichiers : 10-15 minutes

Configuration BDD : 5 minutes

Tests : 15 minutes

Total : ~30-45 minutes
