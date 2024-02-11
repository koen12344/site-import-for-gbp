=== Site Import for Google Business Profile (Google My Business) ===
Contributors: koen12344
Donate link: https://koenreus.com
Tags: google my business, google business profile, gbp, gmb, import
Requires at least: 5.0
Tested up to: 6.4.3
Requires PHP: 7.2
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily import all posts and images from your Google Business profile website into WordPress

== Description ==

Preserve your online presence! As Google phases out its Business Websites feature in **March 2024**, don't let your valuable content fade away. **Site Import for GBP** offers a lifeline for small business owners, enabling a smooth and effortless migration of posts and images directly into your WordPress website.

* **Effortless Import:** With just a few clicks, secure your digital footprint by transferring your GBP posts into your WordPress site, ensuring your content remains visible online.
* **Gallery Integration:** Not just your posts, but your entire GBP image gallery is seamlessly imported into your WordPress site.
* **Future-Proof Your Content:** Transition to WordPress, the leading content management system, and enjoy endless customization, control, and growth potential for your business online.
* **Peace of Mind:** Designed specifically for small business owners, **Site Import for GBP** simplifies the technicalities, allowing you to focus on what you do best—running your business.

Don't wait for the clock to run out on your Google Business Website. Install **Site Import for GBP** and step into a world of possibilities with WordPress.

[![Twitter URL](https://img.shields.io/twitter/url/https/twitter.com/KoenReus.svg?style=social&label=Follow%20%40KoenReus)](https://twitter.com/KoenReus)

== Installation ==

Installing **Site Import for GBP** and importing your Google Business website posts and images is easy!

1. Download [the latest version](https://github.com/koen12344/site-import-for-gbp/releases/latest) of the plugin directly or find it in the WordPress plugin repository by searching for "Site Import for Google Business Profile" within Plugins > Add New
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to the Site Import for GBP section and click "Connect to Google Business Profile"
1. Connect the Google account that contains the business location that you want to import and proceed through the authentication wizard
1. You will now be redirected back to your WordPress site
1. The list of locations within your Google account will now show up. Click "Start GBP Website import"
1. You can now pick either Posts or Images, or both! Posts will be imported as regular WordPress posts, and images will be imported into your Media library.

== Frequently Asked Questions ==

= Will importing my site be possible after March 2024? =

There will be a short time window after March 2024 to import your site data. But why risk waiting.

= Why does the plugin require such extensive permissions on my Google Business Profile? =

The Google Business Profile API only has a single access level, it is either all or nothing. This is why the plugin asks for such extensive permissions. Ideally we'd ask for as little permissions as possible for the plugin to do the job, but unfortunately there is no way around it. Only endpoints required to read your location data, and import ports/images are implemented. Your access tokens are safely stored within your own WordPress website only.

= What are the limits of this tool? =

The plugin is capped at importing your most recent 100 gallery images and 50 posts from your Google Business Profile

== Screenshots ==

1. Connect your Google account
2. Select a location to import from
3. Select what to import

== Changelog ==

= 0.1.0 =

* Initial release

== Upgrade Notice ==

