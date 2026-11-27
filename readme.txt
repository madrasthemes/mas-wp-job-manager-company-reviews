=== MAS Company Reviews For WP Job Manager ===
Contributors: madrasthemes, yousufansa, farookibrahim, ibndawood
Tags: company review, company reviews, company listings review, company management, company manager, job, job listing
Requires at least: 5.0
Tested up to: 7.2
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

MAS Company Reviews For WP Job Manager is a free plugin that allow you to review companies in multiple review categories and controlling star count.

== Description ==

MAS Company Reviews For WP Job Manager plugin is an extension of MAS WP Job Manager Company plugin that allow your users to review companies in multiple review categories.

Also user can post images on thier reviews.

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater (PHP 7.0 or greater is recommended)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of MAS Company Reviews For WP Job Manager, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “MAS Company Reviews For WP Job Manager” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

= Configuration =

You can configure the company review settings on Job Listings -> Settings -> Company Reviews (Tab).

== Frequently Asked Questions ==

= Will MAS Company Reviews For WP Job Manager work with my theme? =

Yes; MAS Company Reviews For WP Job Manager will work with any theme having active WP Job Manager & MAS WP Job Manager Company plugins.

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in our support forum or preferably on the [MAS Company Reviews For WP Job Manager GitHub repository](https://github.com/madrasthemes/mas-wp-job-manager-company-reviews/issues).

= Can I contribute? =

Yes you can! Join in on our [GitHub repository](https://github.com/madrasthemes/mas-wp-job-manager-company-reviews) :)

== Screenshots ==

1. Review Form.
2. Review Dashboard Page.
3. Review Dashboard Page Content.
4. Review Settings.

== Changelog ==
= 1.0.3 - 2026-11-27 =
* Compatibility: PHP 8.4 and WordPress 7.2 compatibility updates.
* Fix: Fixed undefined variable, dynamic properties, and potential division-by-zero / str_repeat ValueError on PHP 8+.
* Fix: Fixed review display when review title is empty or disabled, and fixed comment ID lookup in display filter.
* Fix: Fixed recalculation redirect status, average rating cache clearing when all reviews are removed, and permanent comment deletion cache sync.
* Security: Enforced guest and duplicate-review restrictions server-side, validated rating bounds, verified attachment ownership on delete, and hardened output escaping and nonce/capability checks.
* Tweak: Updated jQuery ready handlers for jQuery 4 compatibility and loaded textdomain on init.

= 1.0.2 - 2021-03-29 =
* Tested upto 5.7

= 1.0.1 - 2020-05-21 =
* Fix – Guest user login link text escape issue fixed

= 1.0.0 - 2019-11-18 =
* Initial Release