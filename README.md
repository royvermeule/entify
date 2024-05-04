
# Entify

Entify is a easy to use ORM like tool, that will create entities and database tables based on a yaml file.

## how to use entify:

**Create configuration file**:
In the root of you project create a file called `entify-config.php`.
The config must look something like this: 
```php 
<?php

$config = [
    'entities' => [
        'Src\\Entities' => 'src/entities'
    ],

    'schema' => '',
    'default-data' => '',
    
    'database-connection' => [
        'DB_HOST' => '',
        'DB_USER' => '',
        'DB_PASS' => '',
        'DB_NAME' => '',
        'DB_PORT' => 3306
    ]
];

return $config;
```
**Creating the schema file**: Anywhere in you project create a yaml file specify its location inside of the config under; schema.

Example of what the schema might look like: 

```yaml
users: 
  columns:
    id: {type: int, primary: true, auto_increment: true}
    username: {type: varchar, length: 12}
    password: {type: varchar, length: 255}
  relations:
    usernotes: {type: oneToMany, local: id, foreign: user_id}

notes:
  columns:
    id: {type: int, primary: true, auto_increment: true}
    title: {type: varchar, length: 255}
    content: {type: text}
  relations:
    usernotes: {type: oneToMany, local: id, foreign: note_id}

usernotes:
  columns:
    id: {type: int, primary: true, auto_increment: true}
    user_id: {type: int}
    note_id: {type: int}
  relations:
    users: {type: manyToOne, local: user_id, foreign: id}
    notes: {type: manyToOne, local: note_id, foreign: id}
```

**Adding default data to the database**: Anywhere in your project create a plain php file,
Specify the location under; default-data in your config. 

What the default-data file might look like: 

```php
<?php

require 'vendor/autoload.php';

use Src\Entities\Notes;
use Src\Entities\Usernotes;
use Src\Entities\Users;

$users = new Users();
$users->setUsername('admin');
$users->setPassword(password_hash('admin', PASSWORD_DEFAULT));
$users->create();

$note = new Notes();
$note->setTitle('Note 1');
$note->setContent('Content 1');
$note->create();

$userNote = new Usernotes();
$userNote->setUser_id($users->getId());
$userNote->setNote_id($note->getId());
$userNote->create();
```

**List of commands and what they do**:

- vendor/bin/entify entify --all (Creates the database tables and its corresponding entities)

- vendor/bin/entify entify --entities (only creates the entities)

- vendor/bin/entify entify --database (only adds the tables and relation to the database)

- vendor/bin/entify entify --drop (drops all the database tables, and deletes the entities)

- vendor/bin/entify entify --refresh (drops database and entities and recreates them)

