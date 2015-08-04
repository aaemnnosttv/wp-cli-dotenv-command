WP-CLI Dotenv Command
=====================

```
NAME

  wp dotenv

DESCRIPTION

  Manage a .env file

SYNOPSIS

  wp dotenv <command>

SUBCOMMANDS

  delete      Delete a definition from the environment file
  get         Get the value for a given key from the environment file
  init        Initialize the environment file
  list        List the defined variables from the environment file
  salts       Manage WordPress salts in .env format
  set         Set a value in the environment file for a given key.
```

> All `dotenv` commands accept a `--file=<path>` parameter to specify the location of the environment file.  
Defaults to `.env`.  
If used, this parameter can be an absolute or relative path to the environment file, but which must include the file name (it does not have to be `.env`).

## `init`
Initializes a new environment file.

This command will only run if an environment file does not already exist.  By default, it will create an empty file.

#### `--template=<file>`
You may optionally initialize the environment file using another file as a template (eg. `.env.example` is a common convention).
Run `wp dotenv init --template=.env.example` to use that file (assuming it exists) as the basis for the new environment file.
By default, the new file will be a copy of the template, but you may also set your new values on the fly interactively!
Pass `--interactive` with the same command to be prompted for each defined variable.  You may specify a new value to use, or simply leave it blank to keep the template-defined value.  Any other lines/comments from the template are preserved.

#### `--with-salts`
Initialize the environment file with some fresh salts provided by the wordpress.org salt generator service.  Any existing keys by the same name will not be overridden.  See `wp dotenv salts`.

## `list`
Prints out all of the key/value pairs as defined in the environment file.  
Supports all of the same options for `--format=<out>` you've known to grow and love (`table`,`json`,`csv`,..etc).  Default: `table`.

## `get <key>`
Get the value of a defined key from the environment file.

## `set <key> <value>`
Set the value of a key in the environment file.

## `delete <key1> <key2> <key3>`
Remove lines for the given keys from the environment file.

## `salts`

#### `generate`
Initialize the environment file with some fresh salts provided by the wordpress.org salt generator service.  Any existing keys by the same name will not be overridden.

#### `regenerate`
Same as `generate`, but will update all keys for salts with new values.

# Installation
Due to the nature of this command, it cannot be installed as a plugin and thus would not be useful to install as a project dependency.  Instead, the Dotenv Command is installed as a Composer package, and loaded by the local user's wp-cli config.

 
Create the wp-cli user directory, if it doesn't already exist
```
mkdir ~/.wp-cli && cd ~/.wp-cli
```
Require the dotenv command package
```
composer require --no-dev --prefer-dist aaemnnosttv/wp-cli-dotenv-command:"^0.1"
```
Create the wp-cli config file, if it doesn't exist yet
```
touch config.yml
```
Load composer.  Edit the `config.yml` file and make sure `vendor/autoload.php` is being loaded under `require` like so
```
require:
  - vendor/autoload.php
```

That's it!  Now you should see the `dotenv` command as an option when you run `wp` from any directory.
