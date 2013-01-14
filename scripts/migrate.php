#!/usr/bin/php
<?php
require 'vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

// create the table for counting the current migration if it does not exist
//
// parse the arguments to determine which database(s) to migrate and which way to migrate them
//
// execute the sql

$dbh = null;
$realpath = dirname(__DIR__);
$migrations_path = $realpath . "/db/migrations";
$databases = array();
$env = isset($_ENV['SLIM_MODE'])
    ? $_ENV['SLIM_MODE']
    : 'dev';

array_shift($argv);

// Parse args
foreach ($argv as $arg) {
    if (($sep_index = strpos($arg, '=')) !== false) {
        $parts = explode('=', $arg);

        if (count($parts) !== 2)
            die("\nERROR: invalid parameters\n");

        $parts = array_map(function($val) {return strtolower($val);}, $parts);

        if (!in_array($parts[1], array('down', 'up', 'forward', 'backward')))
            die("\nERROR: invalid migration direction\n");

        $databases[$parts[0]] = $parts[1];
    } else {
        $databases[$env] = strtolower($arg);
        break;
    }
}

foreach ($databases as $env => $direction) {
    $config = Yaml::parse($realpath . '/config/' . $env . '.yml');

    // COnfigure PDO
    $db = (object) $config['db'];
    if (in_array($db->protocol, array('mysql', 'pgsql'))) {
        $longdsn = "{$db->protocol}:host={$db->host};dbname={$db->database}";

        if (isset($db->host)) {
            $longdsn .= ';host=' . $db->host;
        }
        if (isset($db->port)) {
            $longdsn .= ';port=' . $db->port;
        }
        $user = (isset($db->user)) ? $db->user : null;
        $password = (isset($db->password)) ? $db->password : null;

        $dbh = new \PDO($longdsn, $user, $password);
    } else {
        $dbh = new \PDO("{$db->protocol}:{$db->database}");
    }


    if ($dbh->query("select 1 as i_am_alive from db_migrations") === false) {
        echo "\nMigration table does not exist. Attempting to create it...";
        try {
            $dbh->exec(
                "CREATE TABLE db_migrations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    db_name TEXT(25),
                    current INTEGER(3)
                )"
            );
        } catch (\PDOException $e) {
            die("\nERROR (DATABASE): " . $e->getMessage() . "\n");
        }
    }

    try {
        $curr_stmt = $dbh->prepare("SELECT current FROM db_migrations WHERE db_name = ? LIMIT 1");
        $curr_stmt->execute(array($env));
        if (! ($current_migration = $curr_stmt->fetchColumn())) {
            $current_migration = 0;
        }
    } catch (\Exception $e) {
        $current_migration = 0;
    }
    
    $migrations = array();

    if (is_dir($migrations_path)) {
        if ($dh = opendir($migrations_path)) {
            while (($file = readdir($dh)) !== false) {
                //if ($file == '.' || $file == '..' || filetype($migrations_path . $file) == 'dir')
                if (stripos($file, 'sql') === false)
                    continue;
                
                $migration_number = strstr($file, '.', true);
                $migrations[$migration_number] = $file;
            }
        } else {
            die("\nERROR: Could not get a handle on $migrations_path. Is it readable?\n");
        } 
    } else {
        die("\nERROR: No migrations found or $migrations_path not a directory\n");
    }

    $to_exec = array();
    switch($direction) {
        case 'up': // migrate to most recent
            foreach ($migrations as $number => $filename) {
                if ($number > $current_migration) {
                    $to_exec[] = $migrations[$number];
                }
            }
            if (empty($to_exec)) {
                die("\nERROR: Database is already current.\n");
            }
            break;
        case 'down':
            die("currently unsupportd");
            break;
        case 'forward':
            if (isset($migrations[$current_migration + 1])) {
                $to_exec[] = $migrations[$current_migration + 1];
            } else {
                die("\nERROR: Database is already current.\n");
            }
            break;
        case 'backward':
            die("currently unsupportd");
            break;
        default:
            die("\nERROR: Unrecognized migration direction\n");
    }

    if(empty($to_exec)) {
        die("\nERROR: No migrations to perform\n");
    }

    foreach($to_exec as $do_migrate) {
      echo "\nRunning Migration $do_migrate...\n";
      try {
          $migration_file = "$migrations_path/$do_migrate";
          if (!file_exists($migration_file)) {
              die("\nERROR: Unable to locate migration file at: $migration_file\n");
          }

          $sql = file_get_contents($migration_file);
          if ($dbh->exec($sql) === false) {
              die("\nERROR: Migration could not be performed against the database\n");
          }

          $migration_num = (int) strstr($do_migrate, '.', true);

          $exists_stmt = $dbh->prepare("SELECT count(*) AS row_exists FROM db_migrations WHERE db_name = ?");
          $exists_stmt->execute(array($env));
          $num_records = $exists_stmt->fetchColumn();
          if ($num_records == 0) {
              $insert_stmt = $dbh->prepare("INSERT INTO db_migrations(db_name, current) VALUES(?,?)");
              $insert_stmt->execute(array($env, $migration_num));
          } else {
              $update_stmt = $dbh->prepare("UPDATE db_migrations SET current = ? WHERE db_name = ?");
              $update_stmt->execute(array($migration_num, $env));
          }
      } catch (\Exception $e) {
          die("\nERROR: Migration failed with the following message: " . $e->getMessage() . "\n");
      }
    }
}

$dbh = null;

