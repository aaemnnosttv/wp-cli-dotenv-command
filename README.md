# WP-CLI Dotenv Command

[![Travis Build](https://img.shields.io/travis/aaemnnosttv/wp-cli-dotenv-command/master.svg)](https://travis-ci.org/aaemnnosttv/wp-cli-dotenv-command)
[![Packagist](https://img.shields.io/packagist/v/aaemnnosttv/wp-cli-dotenv-command.svg)](https://packagist.org/packages/aaemnnosttv/wp-cli-dotenv-command)

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

By default, this command will only create the environment file if it does not already exist, but it can do much more.

#### `--template=<file>`
You may optionally initialize the environment file using another file as a template (eg: `.env.example`, a common convention).
Run `wp dotenv init --template=.env.example` to use that file as the basis for the new environment file.

By default, the new file will be a copy of the template, but you may also set new values on the fly interactively!
Passing the `--interactive` flag with the same command will prompt for each defined variable in the template.  You may specify a new value to use, or simply leave it blank to keep the template-defined value.  Any other lines/comments from the template are preserved.

#### `--with-salts`
Initialize the new environment file with fresh salts provided by the wordpress.org salt generator service.
Any existing keys by the same name will not be overwritten.  [See `salts`](#salts).

#### `--force`
Overwrites an existing environment file, if it exists.

## `list [<pattern>...]`
Prints out all of the key/value pairs as defined in the environment file.

You may also optionally limit the output to specific keys, or even keys that match simple patterns using glob pattern syntax.
Eg: `wp dotenv list DB_* *AWS*` would produce a list with the following hypotheical keys:
```
DB_NAME=...
DB_PASS=...
S3_AWS_ID=...
S3_AWS_SECRET=...
```

The `list` command supports all of the same options for `--format=<out>` you've known to grow and love (`table`,`json`,`csv`,..etc).  Default: `table`.

## `get <key>`
Get the value of a defined key from the environment file.

## `set <key> <value>`
Set the value of a key in the environment file.

By default the value that is set is not quoted in the file.  If you need your value to be quoted a certain way, you may optionally pass a flag for the preferred style of quote you want: `--quote-single` or `--quote-double`.

## `delete <key>...`
Remove one or more definitions for the given keys from the environment file.

## `salts`

#### `generate`
Adds variable definitions to the environment file with fresh salts provided by the [wordpress.org salt generator service](https://api.wordpress.org/secret-key/1.1/salt/).  

By default, any existing keys by the same name will not be overwritten.  However, if all of the defined salts in the environment file have the same value, then it is assumed that they are placeholders and will be updated.  It is also possible to force regenerate them using `--force`, or simply use the `regenerate` command (see below).

#### `regenerate`
Same as `generate`, but will update all keys for salts with new values.

# Installation

#### Recommended

As of WP-CLI v0.23, you may install the dotenv command using the new `package` command:
```
wp package install aaemnnosttv/wp-cli-dotenv-command
```

For installation with prior versions of WP-CLI, [see the wiki](https://github.com/aaemnnosttv/wp-cli-dotenv-command/wiki).


That's it!  Now you should see the `dotenv` command as an option when you run `wp` from any directory.
