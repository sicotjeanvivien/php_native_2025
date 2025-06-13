# 🐘 AWSD ORM – Native PHP 8.4 ORM

AWSD ORM est un ORM maison moderne, développé **sans framework**, conçu pour explorer en profondeur le langage **PHP 8.4**, les annotations, la réflexion, et les générateurs de requêtes SQL typés.

Ce projet suit le principe :

> **Design for PostgreSQL, degrade for the rest**  
> (la conception cible PostgreSQL comme dialecte principal, avec compatibilité partielle MySQL/SQLite)

---

## 🧱 Architecture

```

/
├── lib/
│   ├── Database/           → Connexion PDO + QueryExecutor
│   ├── Schema/             → Générateurs SQL, composants de requête
│   ├── Script/             → Scripts CLI (migrations, tests)
│   ├── ORM/                → Générateur SQL générique
│   └── Utils/              → Helpers (StringHelper, Log, etc.)
├── src/Model/              → Entités métier (annotées avec #\[Type], #\[Trigger]...)
├── migrations/             → Fichiers .sql générés automatiquement
├── bin/console.php         → Entrée CLI (dispatcher)

````

---

## ✨ Fonctionnalités principales

- Génération automatique de :
  - `CREATE TABLE`
  - `INDEX`
  - `TRIGGER` (PostgreSQL uniquement)
- Mappers SQL orientés SGBD (PostgreSQL, MySQL, SQLite)
- Construction modulaire des requêtes `SELECT` (`WhereComponent`, `OrderByComponent`, etc.)
- Système de migration basé sur des fichiers `.sql` versionnés
- Exécution typée des requêtes avec hydratation automatique (et casting)
- CLI minimaliste extensible

---

## 🚀 Commandes disponibles (CLI)

```bash
php bin/console.php migration:generate   # Génère les fichiers de migration SQL à partir des entités
php bin/console.php migration:run        # Exécute les migrations non encore appliquées
php bin/console.php test:query           # Teste des requêtes manuelles (à personnaliser)
````

---

## 🧩 Conventions

* Tous les noms de colonnes doivent être en `snake_case`
* Chaque entité doit être décorée avec des attributs PHP 8+ :

  * `#[Type]` pour le mapping SQL
  * `#[Trigger]` pour les mises à jour automatiques
  * `#[Index]` (à venir)
* Les fichiers `.sql` générés sont versionnés sous `migrations/` avec horodatage

---

## 🧪 Exécution manuelle

Exemple pour tester une entité `User` :

```php
$executor = new \AWSD\Database\QueryExecutor(User::class);
$users = $executor->executeQuery('SELECT * FROM users WHERE active = true');
```

---

## ⚙️ Environnement attendu

* PHP >= 8.4
* PostgreSQL recommandé (MySQL/SQLite support partiel)
* Fichier `.env` avec au minimum `DB_DRIVER`, `DB_NAME`, `DB_USER`, etc.

---

## 📚 À venir

* Support des relations (`hasMany`, `belongsTo`, etc.)
* Générateur de rollback de migration
* Console interactive type artisan/symfony
* Tests automatisés

---

## 👤 Auteur

Projet personnel développé par **Jean-Vivien Sicot**.

---

## 📄 Licence

Ce projet est librement utilisable à des fins d’apprentissage. Licence à définir.
