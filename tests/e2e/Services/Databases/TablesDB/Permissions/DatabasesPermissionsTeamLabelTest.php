<?php

namespace Tests\E2E\Services\Databases\TablesDB\Permissions;

use Tests\E2E\Client;
use Tests\E2E\Scopes\ProjectCustom;
use Tests\E2E\Scopes\Scope;
use Tests\E2E\Scopes\SideClient;
use Utopia\Database\Helpers\ID;
use Utopia\Database\Helpers\Permission;
use Utopia\Database\Helpers\Role;

/**
 * Test that demonstrates using labels with team roles for database table permissions.
 * This allows fine-grained access control where team members with specific labels
 * can access database tables.
 */
class DatabasesPermissionsTeamLabelTest extends Scope
{
    use ProjectCustom;
    use SideClient;
    use DatabasesPermissionsScope;

    public array $tables = [];
    public string $databaseId = 'testteamlabeldb';

    public function createTeams(): array
    {
        return [
            'team1' => $this->createTeam('team1', 'Engineering Team'),
            'team2' => $this->createTeam('team2', 'Marketing Team'),
        ];
    }

    public function createUsers(): array
    {
        return [
            'user1' => $this->createUser('user1', 'developer@example.com'),
            'user2' => $this->createUser('user2', 'designer@example.com'),
            'user3' => $this->createUser('user3', 'marketer@example.com'),
        ];
    }

    public function createTables($teams)
    {
        $db = $this->client->call(Client::METHOD_POST, '/tablesdb', $this->getServerHeader(), [
            'databaseId' => $this->databaseId,
            'name' => 'Test Database for Team Labels',
        ]);
        $this->assertEquals(201, $db['headers']['status-code']);

        // Table 1: Accessible by team1 members with 'developer' label
        $table1 = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables', $this->getServerHeader(), [
            'tableId' => ID::custom('table1'),
            'name' => 'Developer Table',
            'permissions' => [
                Permission::read(Role::team($teams['team1']['$id'], 'developer')),
                Permission::create(Role::team($teams['team1']['$id'], 'developer')),
                Permission::update(Role::team($teams['team1']['$id'], 'developer')),
                Permission::delete(Role::team($teams['team1']['$id'], 'developer')),
            ],
        ]);

        $this->assertEquals(201, $table1['headers']['status-code']);
        $this->tables['table1'] = $table1['body']['$id'];

        $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table1'] . '/columns/string', $this->getServerHeader(), [
            'key' => 'title',
            'size' => 256,
            'required' => true,
        ]);

        // Table 2: Accessible by team1 members with 'designer' label
        $table2 = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables', $this->getServerHeader(), [
            'tableId' => ID::custom('table2'),
            'name' => 'Designer Table',
            'permissions' => [
                Permission::read(Role::team($teams['team1']['$id'], 'designer')),
                Permission::create(Role::team($teams['team1']['$id'], 'designer')),
                Permission::update(Role::team($teams['team1']['$id'], 'designer')),
                Permission::delete(Role::team($teams['team1']['$id'], 'designer')),
            ]
        ]);

        $this->assertEquals(201, $table2['headers']['status-code']);
        $this->tables['table2'] = $table2['body']['$id'];

        $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table2'] . '/columns/string', $this->getServerHeader(), [
            'key' => 'title',
            'size' => 256,
            'required' => true,
        ]);

        // Table 3: Accessible by team2 members with 'content-creator' label
        $table3 = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables', $this->getServerHeader(), [
            'tableId' => ID::custom('table3'),
            'name' => 'Marketing Content Table',
            'permissions' => [
                Permission::read(Role::team($teams['team2']['$id'], 'content-creator')),
                Permission::create(Role::team($teams['team2']['$id'], 'content-creator')),
                Permission::update(Role::team($teams['team2']['$id'], 'content-creator')),
                Permission::delete(Role::team($teams['team2']['$id'], 'content-creator')),
            ]
        ]);

        $this->assertEquals(201, $table3['headers']['status-code']);
        $this->tables['table3'] = $table3['body']['$id'];

        $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table3'] . '/columns/string', $this->getServerHeader(), [
            'key' => 'title',
            'size' => 256,
            'required' => true,
        ]);

        sleep(2);

        return $this->tables;
    }

    /*
     * $success = can $user read from $table
     * [$user, $table, $success]
     */
    public function readRowsProvider(): array
    {
        return [
            // user1 is in team1 with 'developer' label - can access table1
            ['user1', 'table1', true],
            // user1 cannot access table2 (needs 'designer' label)
            ['user1', 'table2', false],
            // user1 cannot access table3 (wrong team)
            ['user1', 'table3', false],

            // user2 is in team1 with 'designer' label - cannot access table1
            ['user2', 'table1', false],
            // user2 can access table2
            ['user2', 'table2', true],
            // user2 cannot access table3 (wrong team)
            ['user2', 'table3', false],

            // user3 is in team2 with 'content-creator' label
            ['user3', 'table1', false],
            ['user3', 'table2', false],
            ['user3', 'table3', true],
        ];
    }

    /*
     * $success = can $user write to $table
     * [$user, $table, $success]
     */
    public function writeRowsProvider(): array
    {
        return [
            // user1 is in team1 with 'developer' label - can write to table1
            ['user1', 'table1', true],
            // user1 cannot write to table2 (needs 'designer' label)
            ['user1', 'table2', false],
            // user1 cannot write to table3 (wrong team)
            ['user1', 'table3', false],

            // user2 is in team1 with 'designer' label - cannot write to table1
            ['user2', 'table1', false],
            // user2 can write to table2
            ['user2', 'table2', true],
            // user2 cannot write to table3 (wrong team)
            ['user2', 'table3', false],

            // user3 is in team2 with 'content-creator' label
            ['user3', 'table1', false],
            ['user3', 'table2', false],
            ['user3', 'table3', true],
        ];
    }

    /**
     * Setup database
     *
     * Data providers lose object state
     * so explicitly pass $users to each iteration
     * @return array $users
     */
    public function testSetupDatabase(): array
    {
        $this->createUsers();
        $this->createTeams();

        // Add user1 to team1 with 'developer' label/role
        $this->addToTeam('user1', 'team1', ['developer']);

        // Add user2 to team1 with 'designer' label/role
        $this->addToTeam('user2', 'team1', ['designer']);

        // Add user3 to team2 with 'content-creator' label/role
        $this->addToTeam('user3', 'team2', ['content-creator']);

        $this->createTables($this->teams);

        // Create test rows in each table
        $response = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table1'] . '/rows', $this->getServerHeader(), [
            'rowId' => ID::unique(),
            'data' => [
                'title' => 'Developer Content',
            ],
        ]);
        $this->assertEquals(201, $response['headers']['status-code']);

        $response = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table2'] . '/rows', $this->getServerHeader(), [
            'rowId' => ID::unique(),
            'data' => [
                'title' => 'Designer Content',
            ],
        ]);
        $this->assertEquals(201, $response['headers']['status-code']);

        $response = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $this->tables['table3'] . '/rows', $this->getServerHeader(), [
            'rowId' => ID::unique(),
            'data' => [
                'title' => 'Marketing Content',
            ],
        ]);
        $this->assertEquals(201, $response['headers']['status-code']);

        return $this->users;
    }

    /**
     * Data provider params are passed before test dependencies
     * @depends testSetupDatabase
     * @dataProvider readRowsProvider
     */
    public function testReadRows($user, $table, $success, $users)
    {
        $rows = $this->client->call(Client::METHOD_GET, '/tablesdb/' . $this->databaseId . '/tables/' . $table  . '/rows', [
            'origin' => 'http://localhost',
            'content-type' => 'application/json',
            'x-appwrite-project' => $this->getProject()['$id'],
            'cookie' => 'a_session_' . $this->getProject()['$id'] . '=' . $users[$user]['session'],
        ]);

        if ($success) {
            $this->assertEquals(200, $rows['headers']['status-code']);
            $this->assertCount(1, $rows['body']['rows']);
        } else {
            $this->assertEquals(401, $rows['headers']['status-code']);
        }
    }

    /**
     * @depends testSetupDatabase
     * @dataProvider writeRowsProvider
     */
    public function testWriteRows($user, $table, $success, $users)
    {
        $rows = $this->client->call(Client::METHOD_POST, '/tablesdb/' . $this->databaseId . '/tables/' . $table  . '/rows', [
            'origin' => 'http://localhost',
            'content-type' => 'application/json',
            'x-appwrite-project' => $this->getProject()['$id'],
            'cookie' => 'a_session_' . $this->getProject()['$id'] . '=' . $users[$user]['session'],
        ], [
            'rowId' => ID::unique(),
            'data' => [
                'title' => 'Test Content',
            ],
        ]);

        if ($success) {
            $this->assertEquals(201, $rows['headers']['status-code']);
        } else {
            // 401 if user is a part of team, 404 otherwise
            $this->assertContains($rows['headers']['status-code'], [401, 404]);
        }
    }
}
