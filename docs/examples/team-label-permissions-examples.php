<?php
/**
 * Practical Example: Using Team Labels for Database Table Permissions
 *
 * This example demonstrates how to create database tables with team label-based
 * permissions for a project management application.
 */

use Utopia\Database\Helpers\ID;
use Utopia\Database\Helpers\Permission;
use Utopia\Database\Helpers\Role;

/**
 * Scenario: A project management application with different access levels
 *
 * Teams:
 * - Engineering Team (team_engineering)
 * - Product Team (team_product)
 *
 * Labels/Roles within teams:
 * - developer: Can write code and view technical docs
 * - senior-developer: Can review and approve code
 * - manager: Can view all data and manage resources
 * - intern: Limited read access
 */

// Example 1: Create a "Code Reviews" table
// Only senior developers can create/update, all developers can read
$codeReviewsTable = [
    'databaseId' => 'main',
    'tableId' => ID::unique(),
    'name' => 'Code Reviews',
    'permissions' => [
        // All developers can read code reviews
        Permission::read(Role::team('team_engineering', 'developer')),
        Permission::read(Role::team('team_engineering', 'senior-developer')),

        // Only senior developers can create and manage code reviews
        Permission::create(Role::team('team_engineering', 'senior-developer')),
        Permission::update(Role::team('team_engineering', 'senior-developer')),
        Permission::delete(Role::team('team_engineering', 'senior-developer')),
    ],
];

// Example 2: Create a "Product Requirements" table
// Product managers can write, developers can read
$productRequirementsTable = [
    'databaseId' => 'main',
    'tableId' => ID::unique(),
    'name' => 'Product Requirements',
    'permissions' => [
        // Developers can read requirements
        Permission::read(Role::team('team_engineering', 'developer')),
        Permission::read(Role::team('team_engineering', 'senior-developer')),

        // Product managers can manage requirements
        Permission::read(Role::team('team_product', 'manager')),
        Permission::create(Role::team('team_product', 'manager')),
        Permission::update(Role::team('team_product', 'manager')),
        Permission::delete(Role::team('team_product', 'manager')),
    ],
];

// Example 3: Create an "Internal Documentation" table
// All team members can read, only managers can write
$documentationTable = [
    'databaseId' => 'main',
    'tableId' => ID::unique(),
    'name' => 'Internal Documentation',
    'permissions' => [
        // Everyone in both teams can read
        Permission::read(Role::team('team_engineering')),
        Permission::read(Role::team('team_product')),

        // Only managers from any team can create/edit docs
        Permission::create(Role::team('team_engineering', 'manager')),
        Permission::create(Role::team('team_product', 'manager')),
        Permission::update(Role::team('team_engineering', 'manager')),
        Permission::update(Role::team('team_product', 'manager')),
        Permission::delete(Role::team('team_engineering', 'manager')),
        Permission::delete(Role::team('team_product', 'manager')),
    ],
];

// Example 4: Create a "Restricted Project Data" table
// Only for senior staff across teams
$restrictedDataTable = [
    'databaseId' => 'main',
    'tableId' => ID::unique(),
    'name' => 'Restricted Project Data',
    'permissions' => [
        Permission::read(Role::team('team_engineering', 'senior-developer')),
        Permission::read(Role::team('team_engineering', 'manager')),
        Permission::read(Role::team('team_product', 'manager')),

        Permission::create(Role::team('team_engineering', 'manager')),
        Permission::create(Role::team('team_product', 'manager')),
        Permission::update(Role::team('team_engineering', 'manager')),
        Permission::update(Role::team('team_product', 'manager')),
        Permission::delete(Role::team('team_engineering', 'manager')),
    ],
];

// Example 5: Create an "Intern Sandbox" table
// Interns can practice with full access, but it's isolated
$internSandboxTable = [
    'databaseId' => 'main',
    'tableId' => ID::unique(),
    'name' => 'Intern Sandbox',
    'permissions' => [
        // Interns have full access to their sandbox
        Permission::read(Role::team('team_engineering', 'intern')),
        Permission::create(Role::team('team_engineering', 'intern')),
        Permission::update(Role::team('team_engineering', 'intern')),
        Permission::delete(Role::team('team_engineering', 'intern')),

        // Managers can supervise
        Permission::read(Role::team('team_engineering', 'manager')),
        Permission::update(Role::team('team_engineering', 'manager')),
    ],
];

/**
 * REST API Examples
 *
 * When creating tables via REST API, use permission strings:
 */

// POST /v1/tablesdb/{databaseId}/tables
$apiExample1 = [
    'tableId' => 'unique()',
    'name' => 'Sprint Planning',
    'permissions' => [
        'read("team:team_engineering/developer")',
        'read("team:team_product/manager")',
        'create("team:team_product/manager")',
        'update("team:team_product/manager")',
        'delete("team:team_product/manager")',
    ]
];

// POST /v1/tablesdb/{databaseId}/tables
$apiExample2 = [
    'tableId' => 'unique()',
    'name' => 'Bug Reports',
    'permissions' => [
        'read("team:team_engineering")',  // All engineering team members
        'create("team:team_engineering/developer")',  // Developers can create
        'update("team:team_engineering/senior-developer")',  // Senior devs can update
        'delete("team:team_engineering/manager")',  // Only managers can delete
    ]
];

/**
 * Best Practices
 */

// 1. Use descriptive label names
$good = Role::team('team_123', 'senior-developer');  // Clear and descriptive
$bad = Role::team('team_123', 'sd');  // Unclear abbreviation

// 2. Combine with document-level permissions for even finer control
$tableWithRowSecurity = [
    'tableId' => ID::unique(),
    'name' => 'Personal Notes',
    'rowSecurity' => true,  // Enable document-level permissions
    'permissions' => [
        // Table-level: All team members can read the table
        Permission::read(Role::team('team_engineering')),
        // Document-level permissions will be set per row
    ],
];

// 3. Use consistent naming conventions across your application
$conventions = [
    'admin',           // Administrative access
    'manager',         // Management access
    'senior-X',        // Senior role for profession X
    'junior-X',        // Junior role for profession X
    'read-only',       // Explicitly read-only access
    'project-X-owner', // Project-specific ownership
];

// 4. Document your team structure and labels
/**
 * Team Structure Documentation:
 *
 * Engineering Team (team_engineering):
 * - intern: Read-only access to learning materials
 * - developer: Can create and update code
 * - senior-developer: Can review and approve
 * - manager: Full administrative access
 *
 * Product Team (team_product):
 * - analyst: Read-only access to data
 * - manager: Can create and manage product requirements
 * - director: Full administrative access
 */

echo "Examples created successfully!\n";
echo "See the documentation for more information on implementing team label permissions.\n";
