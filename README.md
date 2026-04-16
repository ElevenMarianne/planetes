# Planètes — Gamification Eleven Labs

Application de gamification interne : classement des équipes (planètes), attribution de points via des activités, trophées, et tableau de bord back-office.

**Stack :** PHP 8.4 · Symfony 7.4 · PostgreSQL 16 · Docker · Symfony UX / Tailwind CSS

---

## Prérequis

- [Docker](https://docs.docker.com/get-docker/) et Docker Compose v2
- [Node.js](https://nodejs.org/) ≥ 18 (uniquement pour les tests E2E Playwright)

---

## Installation

### 1. Cloner le dépôt

```bash
git clone <url-du-repo>
cd planetes
```

### 2. Configurer les variables d'environnement

Copier le fichier d'exemple et renseigner les valeurs manquantes :

```bash
cp .env .env.local
```

Éditer `.env.local` :

```dotenv
# OAuth Google (obligatoire pour la connexion)
GOOGLE_CLIENT_ID=<votre-client-id>
GOOGLE_CLIENT_SECRET=<votre-client-secret>

# Webhook Slack (optionnel — désactive les notifications si vide)
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
```

Les variables base de données ont des valeurs par défaut fonctionnelles (`planetes` / `planetes`), inutile de les modifier en local.

### 3. Construire et démarrer les conteneurs

```bash
make up
```

Cela démarre :
| Conteneur | Rôle |
|---|---|
| `php` | Application Symfony (PHP-FPM) |
| `nginx` | Serveur web → [http://localhost:8000](http://localhost:8000) |
| `postgres` | Base de données PostgreSQL 16 |
| `messenger-worker` | Consommateur de messages asynchrones (notifications Slack) |
| `cron` | Tâches planifiées (vérifications anniversaires, etc.) |

### 4. Installer les dépendances PHP

```bash
make install
```

### 5. Créer la base de données et jouer les migrations

```bash
make migrate
```

### 6. (Optionnel) Charger les fixtures de développement

```bash
make fixtures
```

### 7. Compiler les assets CSS

```bash
make tailwind
```

En mode watch (recompilation automatique à chaque modification) :

```bash
make tailwind-watch
```

---

## Accès

| URL | Description |
|---|---|
| [http://localhost:8000](http://localhost:8000) | Application (front) |
| [http://localhost:8000/back](http://localhost:8000/back) | Back-office (rôle `ROLE_ADMIN` requis) |

La connexion se fait via **Google OAuth**. Le compte doit exister préalablement en base — il n'y a pas de création automatique à la première connexion.

---

## Créer son compte

Avant de pouvoir se connecter, ton compte doit être créé en base avec ton adresse Google Eleven Labs :

```bash
make create-user firstName=Prénom lastName=Nom email=prenom.nom@eleven-labs.com
```

Par défaut le compte atterrit sur **Astéroïde** (en attente d'affectation). Pour l'affecter directement à une planète :

```bash
make create-user firstName=Prénom lastName=Nom email=prenom.nom@eleven-labs.com planet=raccoons-of-asgard
```

Planètes disponibles : `raccoons-of-asgard` · `donuts-panda` · `ducks` · `les-chatons` · `asteroide`

Pour créer un compte administrateur (accès au back-office) :

```bash
make create-user firstName=Prénom lastName=Nom email=prenom.nom@eleven-labs.com admin=1
```

Une fois le compte créé, connecte-toi via le bouton Google sur [http://localhost:8000/login](http://localhost:8000/login).

---

## Commandes utiles

Toutes les commandes sont disponibles via `make` :

```bash
make up          # Démarrer les conteneurs
make down        # Arrêter les conteneurs
make build       # Reconstruire les images (--no-cache)
make install     # composer install
make migrate     # Jouer les migrations Doctrine
make fixtures    # Charger les fixtures
make cc          # Vider le cache Symfony
make shell       # Ouvrir un shell dans le conteneur PHP
make logs        # Suivre les logs en temps réel
make test        # Lancer la suite PHPUnit
make tailwind    # Compiler les assets CSS (one-shot)
make tailwind-watch  # Compiler les assets CSS (watch)
```

Passer des arguments à `bin/console` :

```bash
make console cmd="debug:router"
```

---

## Tests

### PHPUnit

```bash
make test
# ou directement :
docker compose exec php php bin/phpunit --colors
```

### Playwright (E2E)

```bash
# Installer les dépendances Node si besoin
npm install

make e2e              # Lancer tous les tests E2E
make e2e-ui           # Interface graphique Playwright
make e2e-report       # Lancer + afficher le rapport HTML
```

---

## Structure des répertoires

```
src/
  Controller/     Controllers Symfony (Back/ et Front/)
  Entity/         Entités Doctrine
  Enum/           Enums PHP (PointsTarget, ActivityUniqueName…)
  Form/           FormTypes Symfony
  Message/        Messages Messenger (notifications asynchrones)
  MessageHandler/ Handlers Messenger
  Repository/     Repositories Doctrine
  Service/        Services métier (points, saisons, Slack…)
  Twig/           Composants Twig UX (LiveComponent)
templates/
  back/           Templates back-office
  components/     Composants Twig réutilisables
  front/          Templates front
migrations/       Migrations Doctrine
tests/
  Unit/           Tests unitaires (sans conteneur Symfony)
  Functional/     Tests fonctionnels (avec conteneur + base de données)
```
