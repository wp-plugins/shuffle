=== Plugin Name ===
Contributors: wonderboymusic
Tags: media, attachments, admin, images, image, videos, video, audio, cms, gallery, jquery, manage, music, photo, photos, thumbnail, upload
Requires at least: 3.0
Tested up to: 3.0

Shuffle modifies/improves your Media Library in a number of ways

== Description ==

 Shuffle modifies/improves your Media Library in a number of ways. Shuffle lets you:

1. Attach an item (Image, Audio, Video) to anything (Post, Page, Custom Post Type, another Attachment)!
1. Reorder an item's attachments using a simple Drag and Drop UI
1. Detach an attachment from an item without deleting the attachment
1. View all attachments attached to an item

All of these things should already be in WordPress... but they're not!

This plugin is especially useful if you are using WordPress as a CMS and/or have a media-heavy site.

Use these functions in your Theme to get your re-ordered Media:
`<?php 
//In the Loop:
get_images();

//Outside of the Loop:
get_images(get_the_id());

// Audio
get_audio();

// Video
get_video();

?>`

== Screenshots ==

1. In your Post library, rollover actions have added to conveniently point you to all of your item's attachments
 
2. A simple Drag and Drop UI makes Re-ordering your attachments EASY (currently available for Images, Audio, and Video)

3. You can now attach an attachment to ANYTHING, why not another attachment! Since all we are doing is setting post_parent, and attachments are Posts in the database, it is now easy to manage your associations. We also make sure you can't attach an attachment to itself :)

== Changelog ==

= 0.1 =
* Initial release

== Upgrade Notice ==

