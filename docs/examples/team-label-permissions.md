# Team Label Permissions für Datenbank-Tabellen

## Übersicht

Diese Funktion ermöglicht es, bei den Permissions von Datenbank-Tabellen ein Label mit einem Team als Custom-Permissions anzugeben. Dies erlaubt eine feinere Zugriffskontrolle, bei der nur Team-Mitglieder mit bestimmten Labels/Rollen auf bestimmte Tabellen zugreifen können.

## Funktionsweise

Die Appwrite-Datenbank nutzt die `Role`-Klasse aus dem `utopia-php/database` Package. Die `Role::team()` Methode unterstützt einen zweiten Parameter (Dimension), der als Label/Rolle innerhalb des Teams fungiert.

### Syntax

```php
Role::team(string $teamId, string $label = '')
```

- **$teamId**: Die ID des Teams
- **$label**: Das Label oder die Rolle innerhalb des Teams (optional)

## Verwendungsbeispiele

### Beispiel 1: Einfache Team-Label-Permission

```php
use Utopia\Database\Helpers\Permission;
use Utopia\Database\Helpers\Role;

// Erstelle eine Tabelle, die nur von Team-Mitgliedern mit dem Label "developer" gelesen werden kann
$table = $dbForProject->createDocument('databases/' . $databaseId . '/collections', new Document([
    '$id' => ID::unique(),
    'name' => 'Developer Only Table',
    '$permissions' => [
        Permission::read(Role::team($teamId, 'developer')),
        Permission::create(Role::team($teamId, 'developer')),
        Permission::update(Role::team($teamId, 'developer')),
        Permission::delete(Role::team($teamId, 'developer')),
    ],
]));
```

### Beispiel 2: Mehrere Labels für verschiedene Zugriffsebenen

```php
// Tabelle mit Read-Zugriff für alle Team-Mitglieder,
// aber nur Write-Zugriff für Mitglieder mit "admin" Label
$table = $dbForProject->createDocument('databases/' . $databaseId . '/collections', new Document([
    '$id' => ID::unique(),
    'name' => 'Shared Team Table',
    '$permissions' => [
        Permission::read(Role::team($teamId)),  // Alle Team-Mitglieder
        Permission::create(Role::team($teamId, 'admin')),  // Nur Admins
        Permission::update(Role::team($teamId, 'admin')),  // Nur Admins
        Permission::delete(Role::team($teamId, 'admin')),  // Nur Admins
    ],
]));
```

### Beispiel 3: Mehrere Teams mit unterschiedlichen Labels

```php
// Tabelle die von mehreren Teams mit spezifischen Labels zugänglich ist
$table = $dbForProject->createDocument('databases/' . $databaseId . '/collections', new Document([
    '$id' => ID::unique(),
    'name' => 'Cross-Team Table',
    '$permissions' => [
        Permission::read(Role::team($engineeringTeamId, 'developer')),
        Permission::read(Role::team($productTeamId, 'manager')),
        Permission::create(Role::team($engineeringTeamId, 'senior-developer')),
        Permission::update(Role::team($engineeringTeamId, 'senior-developer')),
        Permission::delete(Role::team($engineeringTeamId, 'lead')),
    ],
]));
```

### Beispiel 4: Verwendung in der API

```php
// POST /v1/tablesdb/{databaseId}/tables
[
    'tableId' => 'unique()',
    'name' => 'Projects',
    'permissions' => [
        'read("team:' . $teamId . '/developer")',
        'create("team:' . $teamId . '/developer")',
        'update("team:' . $teamId . '/project-manager")',
        'delete("team:' . $teamId . '/admin")',
    ]
]
```

## API-Format

Wenn Sie die Permissions direkt als Strings angeben (z.B. in REST-API-Aufrufen), verwenden Sie das folgende Format:

```
team:{teamId}/{label}
```

Beispiele:
- `team:abc123/developer` - Team-Mitglieder mit "developer" Label
- `team:xyz789/admin` - Team-Mitglieder mit "admin" Label
- `team:def456/content-creator` - Team-Mitglieder mit "content-creator" Label

## Anwendungsfälle

### Use Case 1: Entwicklungs-Teams

```php
// Separate Tabellen für verschiedene Entwickler-Rollen
Permission::read(Role::team($teamId, 'frontend-dev'))  // Frontend-Entwickler
Permission::read(Role::team($teamId, 'backend-dev'))   // Backend-Entwickler
Permission::read(Role::team($teamId, 'devops'))        // DevOps-Ingenieure
```

### Use Case 2: Content-Management

```php
// Verschiedene Zugriffsebenen für Content-Erstellung
Permission::read(Role::team($teamId, 'viewer'))        // Kann nur lesen
Permission::create(Role::team($teamId, 'author'))      // Kann erstellen
Permission::update(Role::team($teamId, 'editor'))      // Kann bearbeiten
Permission::delete(Role::team($teamId, 'admin'))       // Kann löschen
```

### Use Case 3: Projektmanagement

```php
// Projektbasierte Zugriffskontrolle
Permission::read(Role::team($teamId, 'project-alpha-member'))
Permission::update(Role::team($teamId, 'project-alpha-lead'))
```

## Wichtige Hinweise

1. **Labels sind beliebige Strings**: Sie können jeden String als Label verwenden. Es gibt keine vordefinierte Liste.

2. **Team-Mitgliedschaft erforderlich**: Ein Benutzer muss Mitglied des Teams sein UND das entsprechende Label haben.

3. **Kombination mit anderen Permissions**: Sie können Team-Label-Permissions mit anderen Permission-Typen kombinieren:
   ```php
   [
       Permission::read(Role::team($teamId, 'member')),
       Permission::read(Role::user($userId)),  // Spezifischer Benutzer
       Permission::read(Role::label('premium')),  // Label-basiert
   ]
   ```

4. **Case-Sensitive**: Labels sind case-sensitive. "Developer" ist nicht gleich "developer".

## Technische Details

Die Permission-Struktur folgt dem Format: `{permission}("{role}:{identifier}/{dimension}")`

- **permission**: read, create, update, delete
- **role**: team
- **identifier**: Team-ID
- **dimension**: Label/Rolle (optional)

Beispiel: `read("team:team123/developer")`

## Testing

Ein vollständiger Test für diese Funktionalität befindet sich in:
`tests/e2e/Services/Databases/TablesDB/Permissions/DatabasesPermissionsTeamLabelTest.php`

## Weitere Ressourcen

- [Utopia Database Documentation](https://github.com/utopia-php/database)
- [Appwrite Permissions Guide](https://appwrite.io/docs/permissions)
- [Appwrite Teams Documentation](https://appwrite.io/docs/server/teams)
