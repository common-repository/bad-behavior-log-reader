=== Bad Behaviour Log Reader ===
Tags: Bad Behaviour, Logs, antispam, reader
Requires at least: 2.2.1
Tested up to: 2.7
Stable tag: 1.7

This plugin allows you to view the full Bad Behaviour logs from within Wordpress.

== Description ==

This plugin allows you to view the full Bad Behaviour logs from within Wordpress. 
It adds a couple of menu pages BBLR Options and BB Log Reader into the Options menu bar from where you can access the relevant functionality. 
Project URL : http://www.misthaven.org.uk/blog/projects/bblogreader/

Since version 1.3 it allows the viewing of the log without the warnings, and (I think) should localise the times.

Bad Behavior plugin now has its own log reader within the admin interface, so there is less need for this now.
It pretty much comes down to which interface you prefer, and if you want the extra plugin..

== Installation ==

1. Upload `bblr.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Optionally set a "number of log entries per page" value in the Options -> BBLR Options menu.

Can also (optionally) be installed through the WordPress plugin installer.

== Frequently Asked Questions ==

What version of Bad Behaviour does this work with?

I have tested it with version 2.0.10 + 2.0.16, 2.0.23, 2.0.25 - It would work with any other version of Bad Behaviour that uses
the same database table structure.

What's the point?

It lets you view the logs that Bad Behaviour keeps for you without you having to leave Wordpress 
and examine your mysql database.  Handy if you don't know SQL or don't want to leave the Wordpress area.

What you choose to do with that information, is, of course, pretty much up to you.

I've noticed in the current (2.0.23) version of Bad Behavior that there is a log reader included, so I probably will
be developing this only for my own amusement, but there you go.


== Background Info ==

This plugin was originally developed by a chap called Simon Elvery on an old version of WP and BB and then
updated by Jonathan Murray (http://jonathanmurray.com/wordpress/2006/07/08/wordpress-plugins/#more-893) to work with newer versions.   That was a while ago.  Since then I started to use Wordpress (well, this weekend actually), installed Bad Behaviour, and thought "I really need to be able to see these logs without using phpmyadmin".  I then decided to write a plugin that would do that, but did a bit of hunting around first and these guys' plugins were what I found, so I expanded on them, and this is the result.


== Changelog ==

V1.0 	Initial Release
v1.1	Moved main plugin page under Plugins in menu structure
        Added link from activity box.
v1.3    Included checkbox for filtering out warnings
        Localisation of time values.
v1.4	Only shows dashboard entry for authorised users.
	Couple of bits of cleanup.
v1.5	Fix to location of plugin with auto-install.
	Brought key info up to date.
v1.6	Various fixes, code cleanup, ready for WP 2.7
v1.7    Fix up activation/deactivation

