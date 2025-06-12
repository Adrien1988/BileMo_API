# BileMo API – Scénario démonstration (cURL)

Ces commandes doivent être exécutées après avoir installé le projet et lancé le serveur local.

## 🔑 1. Obtenir un JWT valide

```bash
curl -X POST http://localhost:8000/api/login_check \
     -H "Content-Type: application/json" \
     -d '{"email":"api@example.com","password":"secret"}'
```

Copier le token reçu dans les prochaines commandes.

## 🚩 2. Lister les produits (200 OK)

```bash
TOKEN=<votre_jwt>
curl http://localhost:8000/api/products \
     -H "Authorization: Bearer ${TOKEN}"
```

## 📦 3. Voir le détail du produit (id=1) (200 OK)

```bash
curl http://localhost:8000/api/products/1 \
     -H "Authorization: Bearer ${TOKEN}"
```

## 📋 4. Lister les utilisateurs du client (200 OK)

```bash
curl http://localhost:8000/api/users \
     -H "Authorization: Bearer ${TOKEN}"
```

## ✨ 5. Ajouter un utilisateur (201 Created)

```bash
curl -X POST http://localhost:8000/api/users \
     -H "Authorization: Bearer ${TOKEN}" \
     -H "Content-Type: application/json" \
     -d '{"email":"new.user@example.com","firstName":"New","lastName":"User"}'
```

## 🗑️ 6. Supprimer un utilisateur (id=42) (204 No Content)

```bash
curl -X DELETE http://localhost:8000/api/users/42 \
     -H "Authorization: Bearer ${TOKEN}"
```
