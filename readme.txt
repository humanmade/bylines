=== Bylines ===
Contributors: danielbachhuber
Tags: authors, bylines, multiple authors, multi-author, publishing
Requires at least: 4.4
Tested up to: 4.8
Stable tag: 0.2.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Modern multi-author publishing for WordPress.

== Description ==

Assign multiple bylines to posts, pages, and any custom post type that supports authors. Create guest bylines for contributors who don't need WordPress user accounts.

On the frontend, use the template tags to list bylines anywhere you'd normally list the post author.

== Installation ==

The Bylines plugin can be installed much like any other WordPress plugin.

1. Upload the plugin ZIP archive file via "Plugins" -> "Add New" in the WordPress admin, or extract the files and upload them via FTP.
2. Activate the Bylines plugin through the "Plugins" list in the WordPress admin.

Integrate the template tags into your theme to display assigned bylines on the frontend.

== WP-CLI Commands ==

This plugin implements a variety of [WP-CLI](https://wp-cli.org) commands. All commands are grouped into the `wp byline` namespace.

    $ wp help byline

    NAME
    
      wp byline
    
    DESCRIPTION
    
      Manage bylines.
    
    SYNOPSIS
    
      wp byline <command>
    
    SUBCOMMANDS
    
      convert-coauthor         Convert co-authors to bylines.
      convert-post-author      Convert post authors to bylines.

Use `wp help byline <command>` to learn more about each command.

== Changelog ==

= 0.2.0 (June 19, 2017) =
* Introduces the `wp byline convert-coauthor` WP-CLI command for converting coauthors to bylines.
* Introduces the `wp byline convert-post-author` WP-CLI command for converting post authors to bylines.
* Includes an 'image' type as a potential byline editor field.
* Makes byline editor fields filterable, to allow registration of new fields.
* Adds user row actions to create and edit bylines associated with users.
* Filters `the_archive_title()` to ensure correct title is displayed on author archives.
* Adds `the_byline_links()` template tag for displaying bylines with their links.
* Restores use of `get_the_terms()` in the `get_bylines()` function, which offers caching.
* Bug fix: When searching bylines, ignore user in result set if user has a byline.
* Bug fix: Defaults to wildcard search when searching users.

= 0.1.0 (May 10, 2017) =
* Initial release.
