# Team Label Permissions - Visuelles Konzept

## Überblick

```
┌─────────────────────────────────────────────────────────────────┐
│                    DATENBANK-TABELLE                            │
│                   "Developer Documents"                         │
│                                                                 │
│  Permissions:                                                   │
│  • read("team:engineering/developer")                          │
│  • create("team:engineering/developer")                        │
│  • update("team:engineering/senior-developer")                 │
│  • delete("team:engineering/admin")                            │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │
                    ┌─────────┴─────────┐
                    │                   │
                    ▼                   ▼
        ┌─────────────────────┐  ┌─────────────────────┐
        │   Team: Engineering │  │   Team: Marketing   │
        ├─────────────────────┤  ├─────────────────────┤
        │ Members:            │  │ Members:            │
        │                     │  │                     │
        │ • Alice             │  │ • David             │
        │   Label: developer  │  │   Label: manager    │
        │   ✅ READ           │  │   ❌ NO ACCESS      │
        │   ✅ CREATE         │  │                     │
        │   ❌ UPDATE         │  └─────────────────────┘
        │   ❌ DELETE         │
        │                     │
        │ • Bob               │
        │   Label: senior-dev │
        │   ✅ READ           │
        │   ✅ CREATE         │
        │   ✅ UPDATE         │
        │   ❌ DELETE         │
        │                     │
        │ • Charlie           │
        │   Label: admin      │
        │   ✅ READ           │
        │   ✅ CREATE         │
        │   ✅ UPDATE         │
        │   ✅ DELETE         │
        └─────────────────────┘
```

## Zugriffsprüfung Flow

```
┌──────────────────────────────────────────────────────────────┐
│                    USER REQUEST                              │
│        Alice möchte "Developer Documents" lesen              │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                  STEP 1: Team Check                          │
│  Ist Alice Mitglied des Teams "engineering"?                 │
│                     ✅ JA                                    │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                  STEP 2: Label Check                         │
│  Hat Alice das Label "developer" im Team?                    │
│                     ✅ JA                                    │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                  STEP 3: Permission Check                    │
│  Gibt es eine read Permission für "team:engineering/dev"?    │
│                     ✅ JA                                    │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
                    ✅ ACCESS GRANTED


┌──────────────────────────────────────────────────────────────┐
│                    USER REQUEST                              │
│        David möchte "Developer Documents" lesen              │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────────┐
│                  STEP 1: Team Check                          │
│  Ist David Mitglied des Teams "engineering"?                 │
│                     ❌ NEIN                                  │
└──────────────────────────────────────────────────────────────┘
                          │
                          ▼
                    ❌ ACCESS DENIED
```

## Permission-String Format

```
┌────────────────────────────────────────────────────────┐
│                                                        │
│  read("team:engineering/developer")                   │
│   │     │    │            │                           │
│   │     │    │            └─ Label/Role im Team       │
│   │     │    └────────────── Team-ID                  │
│   │     └─────────────────── Role-Typ (team)         │
│   └───────────────────────── Action (read)           │
│                                                        │
└────────────────────────────────────────────────────────┘
```

## Hierarchie-Beispiel

```
Organization
    │
    ├─ Team: Engineering
    │    ├─ Label: intern        → ⬜ Nur Lesen von Tutorials
    │    ├─ Label: developer     → ✅ Lesen + Schreiben Code
    │    ├─ Label: senior-dev    → ✅✅ + Code Review
    │    └─ Label: admin         → ✅✅✅ Volle Kontrolle
    │
    └─ Team: Product
         ├─ Label: analyst       → ⬜ Nur Lesen von Daten
         ├─ Label: manager       → ✅ Schreiben Requirements
         └─ Label: director      → ✅✅ Volle Kontrolle
```

## Vergleich: Verschiedene Permission-Typen

```
┌─────────────────────────────────────────────────────────────┐
│  Permission-Typ          │  Beispiel                        │
├─────────────────────────────────────────────────────────────┤
│  1. Jeder (any)          │  read("any")                     │
│                          │  → Alle Benutzer                 │
├─────────────────────────────────────────────────────────────┤
│  2. Spezifischer User    │  read("user:alice123")           │
│                          │  → Nur Alice                     │
├─────────────────────────────────────────────────────────────┤
│  3. Ganzes Team          │  read("team:engineering")        │
│                          │  → Alle im Engineering-Team      │
├─────────────────────────────────────────────────────────────┤
│  4. Team mit Label       │  read("team:engineering/dev")    │
│     ⭐ NEU!             │  → Nur Developers im Team        │
├─────────────────────────────────────────────────────────────┤
│  5. Label (global)       │  read("label:premium")           │
│                          │  → Alle mit Premium-Label        │
└─────────────────────────────────────────────────────────────┘
```

## Praktisches Szenario: Content-Management-System

```
┌───────────────────────────────────────────────────────────────┐
│                    TABELLE: "blog_posts"                      │
│                                                               │
│  Permissions:                                                 │
│  • read("any")                           → Jeder kann lesen  │
│  • create("team:content/author")         → Authors schreiben │
│  • update("team:content/editor")         → Editors editieren │
│  • delete("team:content/admin")          → Nur Admins löschen│
└───────────────────────────────────────────────────────────────┘
                              │
                              │
              ┌───────────────┴───────────────┐
              │                               │
              ▼                               ▼
    ┌─────────────────┐             ┌─────────────────┐
    │ User: Emma      │             │ User: Frank     │
    │ Team: content   │             │ Team: content   │
    │ Label: author   │             │ Label: editor   │
    │                 │             │                 │
    │ ✅ Kann lesen   │             │ ✅ Kann lesen   │
    │ ✅ Kann erstell │             │ ❌ Kein Create  │
    │ ❌ Kein Update  │             │ ✅ Kann Update  │
    │ ❌ Kein Delete  │             │ ❌ Kein Delete  │
    └─────────────────┘             └─────────────────┘
```

## Code-Beispiel mit Visualisierung

```php
// 1. Team erstellen
$team = createTeam('engineering', 'Engineering Team');

// 2. Benutzer mit Labels hinzufügen
addUserToTeam('alice', $team, ['developer']);      // 🟢 Developer
addUserToTeam('bob', $team, ['senior-developer']); // 🔵 Senior
addUserToTeam('charlie', $team, ['admin']);        // 🔴 Admin

// 3. Tabelle mit gestaffelten Permissions erstellen
$table = createTable([
    'name' => 'Code Repository',
    'permissions' => [
        // 🟢 Developer kann lesen und schreiben
        Permission::read(Role::team($team, 'developer')),
        Permission::create(Role::team($team, 'developer')),

        // 🔵 Senior Developer kann zusätzlich reviewen
        Permission::update(Role::team($team, 'senior-developer')),

        // 🔴 Nur Admin kann löschen
        Permission::delete(Role::team($team, 'admin')),
    ]
]);

// Ergebnis:
// Alice (🟢):   READ ✅  CREATE ✅  UPDATE ❌  DELETE ❌
// Bob (🔵):     READ ✅  CREATE ✅  UPDATE ✅  DELETE ❌
// Charlie (🔴): READ ✅  CREATE ✅  UPDATE ✅  DELETE ✅
```

## Wichtige Konzepte

```
┌────────────────────────────────────────────────────────────┐
│ KONZEPT 1: UND-Verknüpfung                                 │
│                                                            │
│  Team-Mitgliedschaft  UND  Label  =  Zugriff              │
│         ✅            AND    ✅    =    ✅                 │
│         ✅            AND    ❌    =    ❌                 │
│         ❌            AND    ✅    =    ❌                 │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ KONZEPT 2: Mehrere Permissions = ODER-Verknüpfung         │
│                                                            │
│  permissions: [                                            │
│    read("team:eng/dev"),    ← Condition 1                │
│    read("team:eng/admin"),  ← Condition 2                │
│  ]                                                         │
│                                                            │
│  User ist Mitglied mit "dev" ODER "admin" = Zugriff ✅   │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ KONZEPT 3: Label-Namen sind frei wählbar                  │
│                                                            │
│  • "developer", "designer", "manager" ✅                  │
│  • "project-alpha-lead" ✅                                │
│  • "senior-backend-engineer" ✅                           │
│  • Jeder String ist möglich!                              │
│  • Case-sensitive: "Developer" ≠ "developer"             │
└────────────────────────────────────────────────────────────┘
```
