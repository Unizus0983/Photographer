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
