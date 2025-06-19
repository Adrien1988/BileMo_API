# BileMo API

REST catalog powered by **Symfony 6.4** & **API Platform**

[![CI](https://github.com/Adrien1988/BileMo_API/actions/workflows/ci.yml/badge.svg)](https://github.com/Adrien1988/BileMo_API/actions/workflows/ci.yml)
[![Codacy Grade](https://app.codacy.com/project/badge/Grade/cce9e0d436f04619b1af9957dce3c193)](https://app.codacy.com/gh/Adrien1988/BileMo_API/dashboard)
[![Codacy Coverage](https://app.codacy.com/project/badge/Coverage/cce9e0d436f04619b1af9957dce3c193)](https://app.codacy.com/gh/Adrien1988/BileMo_API/dashboard?utm_source=github&utm_medium=referral)

---

## 🚀 Installation locale du projet

### ⚙️ Prérequis

# Assurez-vous d’avoir installé localement :

# - PHP >= 8.1
# - Composer 2.x
# - Symfony CLI
# - MySQL (ou MariaDB)
# - OpenSSL (pour JWT)
# - Git

# 🚀 Installation pas-à-pas :

# 1. Cloner le dépôt
```bash
git clone https://github.com/Adrien1988/BileMo_API.git
cd BileMo_API
```

# 2. Installer les dépendances PHP via Composer
```bash
composer install
```

# 3. Copier le fichier .env et le personnaliser si nécessaire
```bash
cp .env .env.local
```

# ➤ Modifier dans .env.local si besoin :
# DATABASE_URL="mysql://root@127.0.0.1:3306/bilemo?serverVersion=8.0"
# JWT_PASSPHRASE="votre-passphrase"

# 4. Créer la base de données
```bash
php bin/console doctrine:database:create
```

# 5. Appliquer les migrations
```bash
php bin/console doctrine:migrations:migrate
```

# 6. Charger les données de démonstration (fixtures)
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

# 7. Générer les clés JWT (pour dev et test)
```bash
mkdir -p config/jwt
mkdir -p config/jwt_test
php bin/console lexik:jwt:generate-keypair
php bin/console lexik:jwt:generate-keypair --env=test
```

# 8. Démarrer le serveur Symfony en arrière-plan
```bash
symfony server:start -d
```

## 2 ▪ Points d’entrée API

* **Interface Swagger UI** : [http://localhost:8000/api](http://localhost:8000/api)  
  (ouvrez ce lien dans votre navigateur pour explorer et tester les routes)

* **Spécification OpenAPI (JSON)** : [http://localhost:8000/api/docs.jsonopenapi](http://localhost:8000/api/docs.jsonopenapi)

🔐 Accès à /api/clients/{id}/users (utilisateurs d’un client)
Cette route permet de consulter les utilisateurs rattachés à un client BileMo.

ROLE_SUPER_ADMIN :
Doit fournir un id client dans l’URL (ex. : /api/clients/1/users) pour accéder aux utilisateurs d’un client donné.

ROLE_ADMIN :
Le clientId passé dans l’URL est techniquement requis, mais il est automatiquement ignoré côté serveur.
L’API utilise toujours le client lié à l’utilisateur connecté via JWT.
➤ Exemple : un admin rattaché au client 79 peut faire GET /api/clients/999/users, mais ne verra que les utilisateurs du client 79.

🛑 Si un ROLE_ADMIN essaie d’accéder aux utilisateurs d’un autre client que celui auquel il est rattaché, l’API retourne un 403 Forbidden.

---

# 👤 Profils de test disponibles (adresse mail / mot de passe) :

# SuperAdmin → superadmin@example.com / supersecret       (aucun client associé)
# Admin      → admin@acme.com       / adminsecret         (client ID = 90)
# User API   → api@example.com      / secret              (client ID = 90)


## 3 ▪ Architecture : diagrammes

### UML – Diagramme de classes
* Fichier : `docs/diagrams/DiagramClasse.png`  
* Aperçu :  
![Class diagram](docs/diagrams/DiagramClasse.png)

### UML – Séquence « Create User »
* Fichier : `docs/diagrams/DiagramSequence.png`  
* Aperçu :  
![Sequence diagram](docs/diagrams/DiagramSequence.png)

### Entity-Relationship (MLD)
* Fichier : `docs/diagrams/MLD.png`  
* Aperçu :  
![MLD diagram](docs/diagrams/MLD.png)



## 4 ▪ Authentification JWT

Voir [DEMO.md](DEMO.md) pour des exemples pratiques détaillés avec JWT.

### Régénérer les clés RSA

```bash
php bin/console lexik:jwt:generate-keypair
php bin/console lexik:jwt:generate-keypair --env=test
```

Variables indispensables dans `.env.local` ou `.env.test.local` :

env de dev : 
```bash
JWT_PASSPHRASE=votre-passphrase
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
```

env de test : 
```bash
JWT_PASSPHRASE=""  # pas besoin de passphrase pour l'environnement de test
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt_test/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt_test/public.pem
```
## 5 ▪ Lancer les tests fonctionnels

```bash
composer run test
```

Les tests vérifient notamment :

- `/api/products` renvoie **401** sans JWT.
- Le même endpoint renvoie **200** avec un JWT valide (fixtures chargées).

Le workflow CI s’exécute à chaque pull-request.
