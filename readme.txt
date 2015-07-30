=== Plugin Name ===
Contributors: wonderboymusic
Tags: media, attachments, admin, images, image, videos, video, audio, cms, gallery, jquery, manage, music, photo, photos, thumbnail, upload
Requires at least: 4.2
Tested up to: 4.3
Stable Tag: 0.5

Shuffle modifies/improves your Media Library in a number of ways

== Description ==

 Shuffle lets you:

1. Attach an item (Image, Audio, Video) to anything (Post, Page, Custom Post Type, another Attachment)!

Use these functions in your Theme to get your re-ordered Media:
<code>
<?php
//In the Loop:
get_images();

//Outside of the Loop:
get_images( get_the_ID() );

// Audio
get_audio();

// Video
get_video();

?>
</code>

Read more: http://scotty-t.com/2010/11/15/my-first-plugin-shuffle/

== Screenshots ==

1. In your Post library, rollover actions have added to conveniently point you to all of your item's attachments

2. You can now attach an attachment to ANYTHING, why not another attachment! Since all we are doing is setting post_parent, and attachments are Posts in the database, it is now easy to manage your associations. We also make sure you can't attach an attachment to itself :)

== Changelog ==

= 0.5 =
* Update the code, remove things that are now in WordPress core

= 0.4 =
* Re-wrote the whole thing

= 0.3 =
* Removed <code>=&</code> wherever it was unnecessarily being used, important so no Notices / Errors will not be thrown when <code>error_reporting(-1)</code>

= 0.2 =
* get_images() will now exclude the post's featured image / post thumbnail by default
* all Theme functions will now take query_posts() params as the only argument if desired, 'and_featured' => true will force the return of the post thumnbail in the result

= 0.1 =
* Initial release

== Upgrade Notice ==
* Please upgrade to the latest release of Shuffle, view the [Changelog](http://wordpress.org/extend/plugins/shuffle/changelog/ "Release Notes") for Release Notes