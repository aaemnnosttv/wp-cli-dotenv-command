<?php namespace WP_CLI_Dotenv_Command;

/*
Copyright (C) 2015  Evan Mattson

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if ( ! defined('WP_CLI') ) return;

\WP_CLI::add_command( 'dotenv', __NAMESPACE__ . '\\Dotenv_Command' );
\WP_CLI::add_command( 'dotenv salts', __NAMESPACE__ . '\\Dotenv_Salts_Command' );
