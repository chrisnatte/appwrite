# Team Label Permissions Implementation

## Zusammenfassung (Summary in German)

Diese Implementation ermöglicht es, bei den Permissions von Datenbank-Tabellen ein Label mit einem Team als Custom-Permissions anzugeben.

## Was wurde implementiert? (What was implemented?)

Die Funktionalität existiert bereits in der zugrunde liegenden `utopia-php/database` Bibliothek. Diese Implementation dokumentiert und demonstriert, wie die Funktion verwendet wird.

### Kern-Funktionalität

Die `Role::team()` Methode aus der Utopia Database Bibliothek unterstützt einen zweiten Parameter, der als "Label" oder "Dimension" fungiert:

```php
Role::team(string $teamId, string $label = '')
```

Dies erzeugt Permissions im Format: `team:{teamId}/{label}`

## Dateien

### 1. Test-Datei
**Pfad**: `tests/e2e/Services/Databases/TablesDB/Permissions/DatabasesPermissionsTeamLabelTest.php`

Umfassender End-to-End-Test, der demonstriert:
- Erstellen von Tabellen mit Team-Label-Permissions
- Benutzer mit verschiedenen Labels in Teams
- Zugriffskontrollen basierend auf Team-Mitgliedschaft UND Label
- Read- und Write-Operationen mit verschiedenen Permission-Kombinationen

### 2. Deutsche Dokumentation
**Pfad**: `docs/examples/team-label-permissions.md`

Vollständige Dokumentation auf Deutsch mit:
- Übersicht der Funktionalität
- Syntax-Erklärung
- 4 detaillierte Verwendungsbeispiele
- API-Format-Beschreibung
- 3 praktische Anwendungsfälle
- Wichtige Hinweise und Best Practices
- Technische Details

### 3. Praktische Code-Beispiele
**Pfad**: `docs/examples/team-label-permissions-examples.php`

Realistische Beispiele für:
- Projekt-Management-Anwendungen
- Verschiedene Team-Strukturen
- Mehrere Zugriffsebenen
- REST API Verwendung
- Best Practices

## Verwendung (Usage)

### Grundlegendes Beispiel

```php
use Utopia\Database\Helpers\Permission;
use Utopia\Database\Helpers\Role;

// Tabelle erstellen, die nur von Team-Mitgliedern mit "developer" Label gelesen werden kann
$table = $dbForProject->createDocument('databases/' . $databaseId . '/collections', new Document([
    '$id' => ID::unique(),
    'name' => 'Entwickler-Tabelle',
    '$permissions' => [
        Permission::read(Role::team($teamId, 'developer')),
        Permission::create(Role::team($teamId, 'developer')),
        Permission::update(Role::team($teamId, 'senior-developer')),
        Permission::delete(Role::team($teamId, 'admin')),
    ],
]));
```

### Via REST API

```bash
POST /v1/tablesdb/{databaseId}/tables
{
    "tableId": "unique()",
    "name": "My Table",
    "permissions": [
        "read(\"team:team123/developer\")",
        "create(\"team:team123/developer\")",
        "update(\"team:team123/admin\")",
        "delete(\"team:team123/admin\")"
    ]
}
```

## Wie funktioniert es? (How does it work?)

1. **Team-Mitgliedschaft**: Ein Benutzer muss Mitglied des angegebenen Teams sein
2. **Label/Rolle**: Der Benutzer muss das spezifische Label/Rolle innerhalb des Teams haben
3. **Permission-Check**: Beide Bedingungen müssen erfüllt sein für Zugriff

### Beispiel-Szenario

```php
// Team erstellen
$team = createTeam('engineering', 'Engineering Team');

// Benutzer zu Team hinzufügen mit Label
addUserToTeam($userId, $team['$id'], ['developer']);

// Tabelle mit Permission erstellen
$table = createTable([
    'permissions' => [
        Permission::read(Role::team($team['$id'], 'developer'))
    ]
]);

// ✓ Der Benutzer KANN lesen (ist im Team UND hat das Label "developer")
```

## Test-Abdeckung

Der Test in `DatabasesPermissionsTeamLabelTest.php` deckt ab:

- ✓ Tabellen-Erstellung mit Team-Label-Permissions
- ✓ Mehrere Teams mit verschiedenen Labels
- ✓ Lesezugriff basierend auf Team-Label
- ✓ Schreibzugriff basierend auf Team-Label
- ✓ Zugriffsverweigerung bei fehlendem Label
- ✓ Zugriffsverweigerung bei falscher Team-Mitgliedschaft

## Vorteile

1. **Feinere Zugriffskontrolle**: Nicht nur Team-basiert, sondern auch Label-basiert innerhalb von Teams
2. **Flexibilität**: Beliebige Label-Namen können verwendet werden
3. **Kombinierbar**: Kann mit anderen Permission-Typen kombiniert werden
4. **Standards-konform**: Nutzt die vorhandene Utopia Database Infrastruktur

## Kompatibilität

- ✓ Funktioniert mit der aktuellen `utopia-php/database` Version (4.3.0)
- ✓ Kompatibel mit bestehenden Permission-Systemen
- ✓ Keine Breaking Changes
- ✓ Abwärtskompatibel (Label-Parameter ist optional)

## Nächste Schritte

Um die Tests auszuführen:

```bash
# Vollständige Test-Suite für Permissions
vendor/bin/phpunit tests/e2e/Services/Databases/TablesDB/Permissions/

# Nur der Team-Label Test
vendor/bin/phpunit tests/e2e/Services/Databases/TablesDB/Permissions/DatabasesPermissionsTeamLabelTest.php
```

## Weitere Informationen

- [Utopia Database auf GitHub](https://github.com/utopia-php/database)
- [Appwrite Permissions Dokumentation](https://appwrite.io/docs/permissions)
- [Appwrite Teams Dokumentation](https://appwrite.io/docs/server/teams)

## Autor

Implementation und Dokumentation erstellt für das Appwrite-Projekt.
