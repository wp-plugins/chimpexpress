=== ChimpExpress ===
Contributors: freakedout
Donate link: http://www.chimpexpress.com
Tags: mailchimp, newsletter
Requires at least: 3
Tested up to: 3.1
Stable tag: 1.2

ChimpExpress - The MailChimp WordPress Integration

== Description ==

ChimpExpress is a MailChimp integration for WordPress. It allows you to create blog posts from existing MailChimp campaigns and create new campaign drafts from within WordPress by using your blog posts as campaign content.
If you're having trouble with the plugin visit our forums http://www.chimpexpress.com/support.html Thank you!

== Installation ==

Installing ChimpExpress is very easy. User the WordPress plugin installer to upload the zip file or simply follow the steps below.

1. Extract the package to obtain the `chimpexpress` folder
2. Upload the `chimpexpress` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the settings according to your needs through the 'Settings' > 'ChimpExpress' menu
5. Start creating campaigns or import content from MailChimp

== Upgrade Notice ==

* If you are upgrading from version 1.2 or previous please either set the owner for the following folders to the server or delete the folders via ftp after the upgrade:
* * /wp-content/plugins/chimpexpress/cache
* * /wp-content/plugins/chimpexpress/tmp
* * /archive   <= if you created landing pages do not delete this folder! Set the owner of this folder to the server instead (CHOWN).
* If you need help please visit our forums at http://chimpexpress.com !

== Frequently Asked Questions ==



== Screenshots ==

1. ChimpExpress Dashboard
2. Import your MailChimp campaigns into WordPress as blog post or landing page.
3. Create new MailChimp campaigns and include your blog posts.
4. Landing page archive. Review and edit your existing landing pages.
5. ChimpExpress Settings. Enter your MailChimp API key and you're ready to go. If your write permissions allow the plugin to write files directly to the server you don't need to enter ftp credentials. Otherwise you will be prompted to supply the credentials. You can enter your Google Analytics ID if you want to be able to track visitors on your landing pages.

== Changelog ==

= 1.0 =
* Initial release of the ChimpExpress plugin
= 1.1 =
* When inserting blog posts into campaign content, the post title is now included.
= 1.2 =
* php.js library updated to v3.24
* Bugfix: writing files via ftp failed in some cases
= 1.3 =
* FTP credentials are no longer mandatory! If the plugin is able to write files directly you don't need to enter ftp credentials anymore.
* Landing page archive - creation date now takes timezone offset from WordPress configuration into account.
* Landing page archive - delete function added.
* Updated German translations.