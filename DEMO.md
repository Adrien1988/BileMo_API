# BileMo API – Scénario démonstration (cURL)

Ces commandes doivent être exécutées après avoir installé le projet et lancé le serveur local.

## Profils de test

- SuperAdmin → superadmin@example.com / supersecret (pas de client)
- Admin principal → admin@acme.com / adminsecret (Client 79)
- User API → api@example.com / secret (Client 79)

👉 Remarque : avant chaque test, bien penser à générer un token JWT avec le bon utilisateur.

## 🔑 1. Obtenir un JWT valide

```bash
ROLE_SUPER_ADMIN
curl -X POST http://localhost:8000/api/login_check -H "Content-Type: application/json" -d "{\"email\":\"superadmin@example.com\",\"password\":\"supersecret\"}"

ROLE_ADMIN
curl -X POST http://localhost:8000/api/login_check -H "Content-Type: application/json" -d "{\"email\":\"admin@acme.com\",\"password\":\"adminsecret\"}"
```

# Copier le token reçu et l’exporter :
```bash
set TOKEN=<votre_jwt>
```

## 🚩 2. Lister les produits (200 OK)

```bash
curl http://localhost:8000/api/products -H "Authorization: Bearer %TOKEN%"
```

## 📦 3. Voir le détail du produit (id=1) (200 OK)

```bash
curl http://localhost:8000/api/products/1534 -H "Authorization: Bearer %TOKEN%"
```

## 📋 4. Lister les utilisateurs du client (200 OK)

```bash
SuperAdmin : 
curl http://localhost:8000/api/users -H "Authorization: Bearer %TOKEN%"

Admin Acme :
curl http://localhost:8000/api/users -H "Authorization: Bearer %TOKEN%"
```

## ✨ 5. Ajouter un utilisateur

```bash
SuperAdmin (201 Created) :

curl -X POST http://localhost:8000/api/users -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/ld+json" -d "{\"email\": \"new.superadmin@example.com\", \"firstName\": \"New\", \"lastName\": \"SuperAdmin\", \"password\": \"Password123\", \"role\": \"ROLE_USER\", \"isActive\": true, \"client\": \"/api/clients/79\"}"
```
```bash
Admin (correct - 201 Created) :

curl -X POST http://localhost:8000/api/users -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/ld+json" -d "{\"email\": \"new.admin@example.com\", \"firstName\": \"New\", \"lastName\": \"Admin\", \"password\": \"Password123\", \"role\": \"ROLE_USER\", \"isActive\": true}"
```
```bash
Admin (essai interdit - 403) :

curl -X POST http://localhost:8000/api/users -H "Authorization: Bearer %TOKEN%" -H "Content-Type: application/ld+json" -d "{\"email\": \"hack.admin@example.com\", \"firstName\": \"Hack\", \"lastName\": \"Admin\", \"password\": \"Password123\", \"role\": \"ROLE_USER\", \"isActive\": true, \"client\": \"/api/clients/2\"}"
```
## 🗑️ 6. Supprimer un utilisateur (204 No Content ou 404)

```bash
SuperAdmin (supprime n’importe quel user) :

curl -X DELETE http://localhost:8000/api/users/14 -H "Authorization: Bearer %TOKEN%"
```
```bash
Admin (supprime user de son client - 204 OK) :

curl -X DELETE http://localhost:8000/api/users/9 -H "Authorization: Bearer %TOKEN%"
```
```bash
Admin (tente de supprimer user d’un autre client - 404 attendu) :

curl -X DELETE http://localhost:8000/api/users/12 -H "Authorization: Bearer %TOKEN%"
```

Avec ce scénario, vous pouvez démontrer les 6 cas suivants :

✅ Consulter la liste des produits
✅ Consulter le détail d’un produit
✅ Consulter la liste des utilisateurs liés à un client
✅ Consulter le détail d’un utilisateur lié à un client
✅ Ajouter un nouvel utilisateur lié à un client
✅ Supprimer un utilisateur ajouté par un client