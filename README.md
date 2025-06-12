# BileMo API

REST catalog powered by **Symfony 6.4** & **API Platform**

[![CI](https://github.com/Adrien1988/BileMo_API/actions/workflows/ci.yml/badge.svg)](https://github.com/Adrien1988/BileMo_API/actions/workflows/ci.yml)
[![Codacy Grade](https://app.codacy.com/project/badge/Grade/cce9e0d436f04619b1af9957dce3c193)](https://app.codacy.com/gh/Adrien1988/BileMo_API/dashboard)
[![Codacy Coverage](https://app.codacy.com/project/badge/Coverage/cce9e0d436f04619b1af9957dce3c193)](https://app.codacy.com/gh/Adrien1988/BileMo_API/dashboard?utm_source=github&utm_medium=referral)

---

## 1 ▪ Installation rapide

```bash
git clone https://github.com/Adrien1988/BileMo_API.git
cd BileMo_API
composer install
cp .env .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load --no-interaction
mkdir -p config/jwt
mkdir -p config/jwt_test
php bin/console lexik:jwt:generate-keypair
php bin/console lexik:jwt:generate-keypair --env=test
symfony server:start -d
```

## 2 ▪ Points d’entrée API

* **Interface Swagger UI** : [http://localhost:8000/api](http://localhost:8000/api)  
  (ouvrez ce lien dans votre navigateur pour explorer et tester les routes)

* **Spécification OpenAPI (JSON)** : [http://localhost:8000/api/docs.jsonopenapi](http://localhost:8000/api/docs.jsonopenapi)

---

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
