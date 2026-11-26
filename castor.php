<?php

// The tasks of the application: run `castor` to list them. Tested with Castor 1.9.
// https://castor.jolicode.com
//
// The tests, the coverage, the lints, the static analysis, the fixers, the local continuous integration and the git
// hooks are the common tasks of phil/dev-tools, imported below and set by PHIL_CASTOR (see
// vendor/phil/dev-tools/doc/castor.md): run `composer install` before the first task. This file keeps the tasks of
// this application alone: its servers, its database, its environments, the check of its migrations and of its
// Doctrine mapping.

declare(strict_types=1);

// Every task runs from the directory of the project, whichever directory castor is called from.
defined('CASTOR_USE_CHDIR') || define('CASTOR_USE_CHDIR', true);

use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\exit_code;
use function Castor\import;
use function Castor\io;
use function Phil\DevTools\Castor\aborted;
use function Phil\DevTools\Castor\aggregate;
use function Phil\DevTools\Castor\success;
use function Phil\DevTools\Castor\title;

const PHIL_CASTOR = [
    'suites' => ['Unit', 'Integration', 'Api', 'Functional', 'E2E'],
    'coverage_threshold' => 100,
    'yaml' => ['.github', 'config', 'compose.yaml', 'compose.override.yaml', 'translations'],
    // The tasks of this file that `castor lint` and `castor ci` run after their common steps.
    'lint_also' => ['lint_doctrine'],
    'ci_also' => ['test_schema'],
];

import(__DIR__.'/vendor/phil/dev-tools/resources/castor/tasks.php');

// Servers ————————————————————————————————————————————————————————————————————

#[AsTask(namespace: 'symfony', description: 'Serve the application with the Symfony binary and SQLite', aliases: ['start'])]
function start(): int
{
    title('symfony:start');
    $exitCode = aggregate(secret(), reset_database());
    if ($exitCode !== 0) {
        aborted('The database could not be prepared: the web server has NOT been started.');

        return $exitCode;
    }

    return exit_code('symfony serve --daemon');
}

#[AsTask(namespace: 'symfony', description: 'Stop the web server of the Symfony binary', aliases: ['stop'])]
function stop(): int
{
    title('symfony:stop');
    $exitCode = exit_code('symfony server:stop');
    resetDatabase();

    return $exitCode;
}

#[AsTask(namespace: 'docker', description: 'Start FrankenPHP in worker mode and PostgreSQL with Docker Compose', aliases: ['up'])]
function up(): int
{
    title('docker:up');
    $exitCode = exit_code('docker compose up --detach --wait --build');
    if ($exitCode !== 0) {
        return success($exitCode);
    }

    return success(exit_code('docker compose exec php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration'));
}

#[AsTask(namespace: 'docker', description: 'Stop the Docker Compose services', aliases: ['down'])]
function down(): int
{
    title('docker:down');

    return success(exit_code('docker compose down --remove-orphans'));
}

#[AsTask(namespace: 'symfony', description: 'Switch to the production environment', aliases: ['go-prod'])]
function go_prod(): int
{
    title('symfony:go-prod');
    if (io()->confirm('Are you sure you want to switch to the production environment? This will overwrite your .env.local file.', false)) {
        return success(aggregate(exit_code('cp .env.local.dist .env.local'), exit_code('bin/console asset-map:compile')));
    }

    aborted();

    return 0;
}

#[AsTask(namespace: 'symfony', description: 'Switch to the development environment', aliases: ['go-dev'])]
function go_dev(): int
{
    title('symfony:go-dev');
    if (io()->confirm('Are you sure you want to switch to the development environment? This will delete your .env.local file.', false)) {
        return success(aggregate(exit_code('rm -f .env.local'), exit_code('rm -rf ./public/assets/*')));
    }

    aborted();

    return 0;
}

#[AsTask(namespace: 'symfony', description: 'Purge all Symfony cache and logs', aliases: ['purge'])]
function purge(): int
{
    title('symfony:purge');

    return success(exit_code('rm -rf ./var/cache/* ./var/log/* ./var/coverage/*'));
}

// Database ———————————————————————————————————————————————————————————————————

#[AsTask(namespace: 'app', description: 'Reset the SQLite database and run the migrations', aliases: ['reset-database'])]
function reset_database(): int
{
    title('app:reset-database');
    resetDatabase();
    io()->note('Running the migrations...');

    return success(exit_code('bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration'));
}

#[AsTask(namespace: 'app', description: 'Reset the SQLite database, run the migrations and load the fixtures', aliases: ['load-fixtures'])]
function load_fixtures(): int
{
    title('app:load-fixtures');
    $exitCode = reset_database();
    if ($exitCode !== 0) {
        return $exitCode;
    }

    io()->note('Loading the fixtures: the accounts "admin" and "member", and ten more members...');

    return success(exit_code('bin/console foundry:load-fixtures --env=dev --no-interaction'));
}

#[AsTask(namespace: 'app', description: 'Write a random APP_SECRET to .env.local, unless it holds one already', aliases: ['secret'])]
function secret(): int
{
    title('app:secret');
    $file = '.env.local';
    $content = is_file($file) ? (string) file_get_contents($file) : '';
    if (preg_match('/^APP_SECRET=.+$/m', $content) === 1) {
        io()->note('.env.local already holds an APP_SECRET: nothing changed.');

        return success(0);
    }

    file_put_contents($file, rtrim($content, "\n").($content === '' ? '' : "\n").'APP_SECRET='.bin2hex(random_bytes(32))."\n");

    return success(0);
}

function resetDatabase(): void
{
    io()->note('Resetting the SQLite database...');
    exit_code('rm -f ./var/data.db');
}

// The migrations and the Doctrine mapping ————————————————————————————————————

/** The SQLite database on which test:schema runs the migrations, removed afterwards. */
const SCHEMA_DATABASE = 'var/data_schema.db';

#[AsTask(name: 'schema', namespace: 'test', description: 'Run the migrations on a fresh SQLite database, then check that its schema matches the Doctrine mapping', aliases: ['test-schema'])]
function test_schema(): int
{
    title('test:schema');
    $context = context()->withEnvironment(['DATABASE_URL' => 'sqlite:///%kernel.project_dir%/'.SCHEMA_DATABASE]);
    exit_code('rm -f ./'.SCHEMA_DATABASE);
    io()->note('Running the migrations on '.SCHEMA_DATABASE.'...');
    $exitCode = exit_code('bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration', context: $context);
    if ($exitCode === 0) {
        io()->note('Comparing the schema with the mapping...');
        $exitCode = exit_code('bin/console doctrine:schema:validate', context: $context);
    }

    exit_code('rm -f ./'.SCHEMA_DATABASE);

    return success($exitCode);
}

#[AsTask(name: 'doctrine', namespace: 'lint', description: 'Check the Doctrine mapping', aliases: ['lint-doctrine'])]
function lint_doctrine(): int
{
    title('lint:doctrine');

    return success(exit_code('bin/console doctrine:schema:validate --skip-sync'));
}

// Helpers ————————————————————————————————————————————————————————————————————

#[AsTask(name: 'check-requirements', namespace: 'helpers', description: 'Check the requirements of Symfony', aliases: ['check-requirements'])]
function check_requirements(): int
{
    title('helpers:check-requirements');

    return success(exit_code('vendor/bin/requirements-checker'));
}
