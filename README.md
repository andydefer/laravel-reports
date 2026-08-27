Tu as raison ! Voici la correction avec l'interface obligatoire bien mise en avant :

---

# Laravel Reports

**Système de signalement polymorphique pour applications Laravel avec pattern Repository, DTOs, Enums et Value Objects**

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12.x%20%7C%2013.x%20%7C%2014.x%20%7C%2015.x-blue)](https://laravel.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📋 Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
  - [Créer vos énumérations](#créer-vos-énumérations)
  - [Configurer les enum casts](#configurer-les-enum-casts)
  - [Créer un signalement](#créer-un-signalement)
  - [Vérifier un signalement](#vérifier-un-signalement)
  - [Récupérer les signalements](#récupérer-les-signalements)
  - [Mettre à jour un signalement](#mettre-à-jour-un-signalement)
  - [Compter les signalements](#compter-les-signalements)
  - [Filtrer par date](#filtrer-par-date)
- [Référence de l'API](#référence-de-lapi)
- [Value Objects](#value-objects)
- [Structure de la base de données](#structure-de-la-base-de-données)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## ✨ Fonctionnalités

- ✅ **Double polymorphisme** - Signalez n'importe quel contenu avec n'importe quel utilisateur
- ✅ **Énumérations personnalisables** - Types et statuts de signalement entièrement configurables
- ✅ **Pattern Repository** - Séparation propre de la logique d'accès aux données
- ✅ **Support des DTOs** - Objets de transfert de données typés
- ✅ **Value Objects** - DateTime, Métadonnées
- ✅ **Support des métadonnées** - Stockez des données supplémentaires au format JSON
- ✅ **Enum Casts** - Conversion automatique entre base de données et énumérations PHP
- ✅ **Suppression douce** - Suppression sécurisée avec possibilité de restauration
- ✅ **Filtrage avancé** - Filtrez par type, statut, auteur, objet signalé
- ✅ **Tests complets** - Couverture complète des tests d'intégration

---

## 🚀 Prérequis

- PHP 8.2 ou supérieur
- Laravel 12.0, 13.0, 14.0 ou 15.0

---

## 📦 Installation

Installez le package via Composer :

```bash
composer require andydefer/laravel-reports
```

### Publier les migrations

```bash
php artisan vendor:publish --tag=reports-migrations
```

### Exécuter les migrations

```bash
php artisan migrate
```

---

## ⚙️ Configuration

### Service Provider

Le package est automatiquement découvert par Laravel. Aucune configuration supplémentaire n'est requise.

Si vous devez personnaliser le Service Provider, ajoutez-le manuellement dans `config/app.php` :

```php
'providers' => [
    // ...
    AndyDefer\LaravelReports\ReportsServiceProvider::class,
],
```

### Configuration des Enum Casts

Le package utilise le système d'`EnumCast` du package `andydefer/laravel-repository` pour convertir automatiquement les valeurs en énumérations PHP.

Créez ou modifiez le fichier `config/repository.php` :

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Enum Casts
    |--------------------------------------------------------------------------
    |
    | Define enum casts for specific tables and columns.
    | Each entry maps a table name and column to an enum class.
    |
    | The enum class must implement EnumerableInterface.
    |
    */
    'enum_casts' => [
        'reports' => [
            'type' => App\Enums\ReportType::class,
            'status' => App\Enums\ReportStatus::class,
        ],
    ],
];
```

> **⚠️ Important** : 
> - Sans cette configuration, les énumérations ne seront pas automatiquement converties
> - L'énumération **DOIT** implémenter l'interface `AndyDefer\Repository\Contracts\EnumerableInterface`
> - La méthode `getValue()` est obligatoire pour l'interface

---

## 📖 Utilisation

### Créer vos énumérations

> **⚠️ OBLIGATOIRE :** Vos énumérations DOIVENT implémenter l'interface `EnumerableInterface`

```php
<?php

namespace App\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum ReportType: string implements EnumerableInterface  // ⚠️ Interface obligatoire
{
    case SPAM = 'spam';
    case ABUSE = 'abuse';
    case OTHER = 'other';
    
    /**
     * Obligatoire - Retourne la valeur brute de l'énumération
     */
    public function getValue(): string
    {
        return $this->value;
    }
    
    /**
     * Optionnel - Méthode utilitaire pour l'affichage
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::SPAM => 'Spam',
            self::ABUSE => 'Abus',
            self::OTHER => 'Autre',
        };
    }
}
```

```php
<?php

namespace App\Enums;

use AndyDefer\Repository\Contracts\EnumerableInterface;

enum ReportStatus: string implements EnumerableInterface  // ⚠️ Interface obligatoire
{
    case PENDING = 'pending';
    case RESOLVED = 'resolved';
    case REJECTED = 'rejected';
    
    /**
     * Obligatoire - Retourne la valeur brute de l'énumération
     */
    public function getValue(): string
    {
        return $this->value;
    }
    
    /**
     * Optionnel - Méthode utilitaire pour l'affichage
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::RESOLVED => 'Résolu',
            self::REJECTED => 'Rejeté',
        };
    }
}
```

### Configurer les enum casts

```php
// config/repository.php
'enum_casts' => [
    'reports' => [
        'type' => App\Enums\ReportType::class,     // Votre enum de type
        'status' => App\Enums\ReportStatus::class, // Votre enum de statut
    ],
],
```

### Créer un signalement

```php
use AndyDefer\LaravelReports\Services\ReportService;
use App\Enums\ReportType;
use App\Enums\ReportStatus;
use AndyDefer\DomainStructures\Utils\StrictDataObject;

class PostController extends Controller
{
    public function report(ReportService $reportService, Post $post)
    {
        $user = auth()->user();
        
        // Signalement simple
        $report = $reportService->report(
            $user,                          // Utilisateur qui signale
            $post,                          // Contenu signalé
            ReportType::SPAM,               // Votre enum de type
            ReportStatus::PENDING,          // Votre enum de statut
            'Contenu promotionnel non sollicité'
        );
        
        // Signalement avec métadonnées
        $metadata = StrictDataObject::from([
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'page_url' => request()->url(),
        ]);
        
        $report = $reportService->report(
            $user,
            $post,
            ReportType::ABUSE,
            ReportStatus::PENDING,
            'Commentaires insultants',
            $metadata
        );
        
        return response()->json($report);
    }
}
```

### Vérifier un signalement

```php
// Vérifier si l'utilisateur a déjà signalé
$hasReported = $reportService->hasReported($user, $post);

if ($hasReported) {
    // L'utilisateur a déjà signalé ce contenu
}
```

### Récupérer les signalements

```php
// Récupérer tous les signalements pour un contenu
$reports = $reportService->getReportsFor($post);

// Récupérer uniquement les signalements en attente
$pendingReports = $reportService->getReportsFor($post, true);

// Récupérer les signalements d'un utilisateur
$userReports = $reportService->getReportsBy($user);

// Récupérer tous les signalements en attente
$pending = $reportService->getPendingReports();

// Récupérer les signalements par statut
$resolved = $reportService->getReportsByStatus(ReportStatus::RESOLVED);

// Récupérer les signalements par type
$spamReports = $reportService->getReportsByType(ReportType::SPAM);
```

### Mettre à jour un signalement

```php
// Mettre à jour le statut
$report = $reportService->updateStatus($reportId, ReportStatus::RESOLVED);

// Mettre à jour le type
$report = $reportService->updateType($reportId, ReportType::OTHER);

// Trouver un signalement par ID
$report = $reportService->find($reportId);

// Supprimer un signalement (soft delete)
$reportService->delete($reportId);
```

### Compter les signalements

```php
// Compter tous les signalements pour un contenu
$total = $reportService->countReports($post);

// Compter uniquement les signalements en attente
$pendingCount = $reportService->countReports($post, true);

// Compter par statut
$resolvedCount = $reportService->countByStatus(ReportStatus::RESOLVED);

// Compter par type
$spamCount = $reportService->countByType(ReportType::SPAM);
```

### Filtrer par date

```php
use AndyDefer\PhpVo\ValueObjects\DateTimeVO;

$date = DateTimeVO::from('2024-01-01 00:00:00');

// Récupérer les signalements mis à jour après une date
$recentReports = $reportService->getReportsUpdatedAfter($date);
```

---

## 📚 Référence de l'API

### ReportService

| Méthode | Description | Retourne |
|---------|-------------|----------|
| `report(Model $reporter, Model $reportable, EnumerableInterface $type, EnumerableInterface $status, string $reason, ?StrictDataObject $metadata = null)` | Crée un signalement | `Model` |
| `hasReported(Model $reporter, Model $reportable)` | Vérifie si l'utilisateur a déjà signalé | `bool` |
| `getReportsFor(Model $reportable, bool $onlyPending = false)` | Récupère les signalements d'un objet | `Collection` |
| `getReportsBy(Model $reporter)` | Récupère les signalements d'un utilisateur | `Collection` |
| `getPendingReports()` | Récupère tous les signalements en attente | `Collection` |
| `getReportsByStatus(EnumerableInterface $status)` | Récupère les signalements par statut | `Collection` |
| `getReportsByType(EnumerableInterface $type)` | Récupère les signalements par type | `Collection` |
| `getReportsUpdatedAfter(DateTimeVO $date)` | Récupère les signalements après une date | `Collection` |
| `find(int $id)` | Trouve un signalement par ID | `?Model` |
| `updateStatus(int $id, EnumerableInterface $status)` | Met à jour le statut | `Model` |
| `updateType(int $id, EnumerableInterface $type)` | Met à jour le type | `Model` |
| `countReports(Model $reportable, bool $onlyPending = false)` | Compte les signalements d'un objet | `int` |
| `countByStatus(EnumerableInterface $status)` | Compte par statut | `int` |
| `countByType(EnumerableInterface $type)` | Compte par type | `int` |
| `delete(int $id)` | Supprime un signalement | `void` |

---

## 🎯 Value Objects

Le package supporte les Value Objects suivants :

| Value Object | Description | Exemple |
|--------------|-------------|---------|
| `DateTimeVO` | Date/heure | `DateTimeVO::from('2024-01-01 12:00:00')` |
| `StrictDataObject` | Métadonnées typées | `StrictDataObject::from(['key' => 'value'])` |

### Accesseurs dans le modèle Report

```php
$report = Report::find(1);

// Accès sous forme de Value Objects
$createdAt = $report->getCreatedAt();    // DateTimeVO
$updatedAt = $report->getUpdatedAt();    // DateTimeVO
$deletedAt = $report->getDeletedAt();    // DateTimeVO
$reviewedAt = $report->getReviewedAt();  // DateTimeVO
$metadata = $report->getMetadata();      // StrictDataObject
$type = $report->getType();              // EnumerableInterface (votre enum)
$status = $report->getStatus();          // EnumerableInterface (votre enum)

// Relations
$reporter = $report->reporter;    // Auteur (User, Admin, etc.)
$reportable = $report->reportable; // Objet signalé (Post, Article, etc.)
```

---

## 📝 Structure de la base de données

```sql
CREATE TABLE reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_type VARCHAR(255) NOT NULL,   -- Type de l'auteur
    reporter_id BIGINT UNSIGNED NOT NULL,  -- ID de l'auteur
    reportable_type VARCHAR(255) NOT NULL, -- Type de l'objet signalé
    reportable_id BIGINT UNSIGNED NOT NULL,-- ID de l'objet signalé
    type VARCHAR(50) NOT NULL,             -- Type de signalement (enum)
    reason MEDIUMTEXT NOT NULL,            -- Raison du signalement
    status VARCHAR(50) NOT NULL,           -- Statut (enum)
    metadata JSON NULL,                    -- Métadonnées
    reviewed_at TIMESTAMP NULL,            -- Date d'examen
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    UNIQUE INDEX idx_unique_report (reporter_type, reporter_id, reportable_type, reportable_id),
    INDEX idx_reporter (reporter_type, reporter_id),
    INDEX idx_reportable (reportable_type, reportable_id),
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_updated_at (updated_at)
);
```

---

## 🔍 Exemple complet

```php
use AndyDefer\LaravelReports\Services\ReportService;
use App\Enums\ReportType;
use App\Enums\ReportStatus;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function store(Request $request, Post $post)
    {
        $user = $request->user();
        $type = ReportType::tryFrom($request->input('type'));
        $reason = $request->input('reason');

        if (!$type) {
            return response()->json(['error' => 'Type de signalement invalide'], 400);
        }

        try {
            $report = $this->reportService->report(
                $user,
                $post,
                $type,
                ReportStatus::PENDING,
                $reason
            );

            return response()->json([
                'message' => 'Signalement créé avec succès',
                'report' => $report,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function stats(Post $post)
    {
        return response()->json([
            'total' => $this->reportService->countReports($post),
            'pending' => $this->reportService->countReports($post, true),
        ]);
    }

    public function resolve(int $id)
    {
        $report = $this->reportService->updateStatus($id, ReportStatus::RESOLVED);

        return response()->json([
            'message' => 'Signalement résolu',
            'report' => $report,
        ]);
    }
}
```

---

## 🧪 Tests

### Exécuter les tests

```bash
composer test
```

### Exécuter uniquement les tests unitaires

```bash
composer test-unit
```

### Exécuter uniquement les tests d'intégration

```bash
composer test-integration
```

### Configuration des tests

Le package utilise `orchestra/testbench` pour les tests d'intégration avec une base de données SQLite en mémoire.

---

## 🔧 Développement

### Style de code

```bash
./vendor/bin/pint
```

### Analyse statique

```bash
./vendor/bin/phpstan analyse
./vendor/bin/psalm
```

---

## 🤝 Contribuer

1. Fork le repository
2. Créer une branche (`git checkout -b feature/amazing-feature`)
3. Commiter les changements (`git commit -m 'feat: add amazing feature'`)
4. Pusher (`git push origin feature/amazing-feature`)
5. Ouvrir une Pull Request

---

## 📦 Dépendances

- [`andydefer/laravel-repository`](https://github.com/andydefer/laravel-repository) - Implémentation du pattern Repository avec Enum Casts
- [`andydefer/php-vo`](https://github.com/andydefer/php-vo) - Value Objects
- [`andydefer/domain-structures`](https://github.com/andydefer/domain-structures) - Structures de domaine (StrictDataObject)

---

## 👨‍💻 Auteur

**Andy Kani**
- GitHub: [@andydefer](https://github.com/andydefer)
- Email: andykanidimbu@gmail.com

---

## 📄 Licence

MIT © [Andy Defer](https://github.com/andydefer)

---

**Construit avec ❤️ pour la communauté Laravel**