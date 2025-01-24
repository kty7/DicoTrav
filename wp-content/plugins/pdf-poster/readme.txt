=== PDF Poster - PDF Embedder Plugin ===
Contributors: abuhayat, shehabulislam, freemius,bplugins
Tags: PDF Embedder, embed pdf, pdf viewer, pdf, pdf plugin
Donate link: https://www.buymeacoffee.com/abuhayat/
Requires at least: 5.0
Tested up to: 6.7.1
Stable tag: 2.2.0
Requires PHP: 7.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily embed PDFs in WordPress with our PDF Poster plugin. Lightweight, user-friendly, with Gutenberg PDF Viewer Block.

== Description ==

This WordPress PDF Embedder plugin makes it simple to customize your PDFs. You can adjust the width and height, making it fit your layout perfectly. It lets users print PDFs easily and shows the filename clearly at the top. Plus, there's a handy full-screen button for better viewing.

With the download button placed conveniently at the top, users can get the PDF with just one click. If you use the Gutenberg block editor, you'll find integrating PDFs a breeze with this WordPress PDF plugin. There are also extra blocks available for added flexibility.

The pro version offers even more features. You can embed PDFs without any distracting frames, protect your content from copying, and save time with preset viewer settings. There's also a shortcode for easy PDF embedding and tools for quick embedding in the classic editor.

Users can navigate PDFs more smoothly with a sidebar menu, and they have control over download and full-screen buttons, including translation options. You can even set a specific page number to jump to in the PDF.

Overall, this plugin for PDFs is perfect for anyone who wants to showcase documents, protect their content, or improve engagement with PDFs on their WordPress site.

**[See Live Demo](https://bplugins.com/products/pdf-poster/#demos "Demo")** 
**[Buy The Pro](https://bplugins.com/products/pdf-poster/#pricing "Buy Pro version")** 


### How to Use PDF Poster?

See how easy PDF Poster plugin is to use! 

https://www.youtube.com/watch?v=PcYaAw7gX7w

### PDF Poster Features
- Add width and height to customize the layout of the PDF.
- Enable printing of the PDF file for the user.
- Show the filename at the top of the file.
- Include a full-screen button with dynamic text.
- Place the download button at the top for easy access to download the PDF.
 - Gutenberg block to integrate with the block editor.

### PDF Poster Pro version features
- Raw PDF viewer allows you to embed PDF files without the black viewer frame. (Only pdf)
- Protect your content by preventing copies and downloads.
- Save your time by presetting your viewer preferences. (Settings page )
- [pdf_embed] shortcode allows you to embed pdf without listing. 
- Additional Gutenberg blocks (2 blocks)
- Quick Embed And ShortCode generator In classic editor.
- Sidebar toggle menu in viewer
- Control over the download button.
- Control Over View full-screen Button.
- Translate Download and View Full-Screen buttons 
- Set Jump to the page number to show a specific page of the pdf file.
- Improved performance. 
Get The PRO -> :  [BUY The PDF Poster PRO ](https://gum.co/zUvK "BUY NOW")  

== Shortcode Usage ==

### Shortcode

[pdf_embed url="https://example.com/document.pdf" width="100%" height="842px" print="true" title="My PDF Document" download_btn="true" fullscreen_btn_text="View in Fullscreen"]


### Attributes

* **url** (required): The URL of the PDF file.
  * Default: `null`
  * Example: `url="https://example.com/document.pdf"`

* **width** (optional): Width of the PDF viewer.
  * Default: `"100%"`
  * Example: `width="80%"`

* **height** (optional): Height of the PDF viewer.
  * Default: `"842px"`
  * Example: `height="600px"`

* **print** (optional): Display a print button.
  * Accepted values: `"true"`, `"false"`
  * Default: `"false"`
  * Example: `print="true"`

* **title** (optional): Title displayed above the PDF viewer.
  * Default: `null`
  * Example: `title="Document Title"`

* **download_btn** (optional): Display a download button.
  * Accepted values: `"true"`, `"false"`
  * Default: `"false"`
  * Example: `download_btn="true"`

* **fullscreen_btn_text** (optional): Text for the fullscreen button.
  * Default: `"View Fullscreen"`
  * Example: `fullscreen_btn_text="Open Fullscreen"`
  


### How to use PDF Poster- step-by-step guide
- After installation, you can see a sidebar menu in the WordPress dashboard called "PDF Poster"
- Add one or more Documents from there. 
- You will get a Shortcode for every PDF In The Editor Screen and PDF Lists.
- Copy Shortcode 
- Past the shortcode in post, page, and widget areas To publish them. if you want to publish a player in a template file use <?php echo do_shortcode('PLAYER_SHORTCODE') ?>
- Enjoy!


### Gutenberg Block to manage PDF files 
- This plugin adds a Gutenberg Block Called PDF Poster Layout Elements Category 
- Go to your WordPress Admin interface and open a post or page editor.
- Click the plus button in the top left corner or in the body of the post/page.
- Search or See in Layout Elements Category and select PDF Poster.
- Click the Icon to add it.
- Select A PDF document  
- Publish and Enjoy! 



### User Feedback

#### ⭐⭐⭐⭐⭐ [Weronika Zielinska – Empp](https://wordpress.org/support/topic/weronika-zielinska-empp/)

❛❛***A great plugin that makes it easy to add the necessary posters and pdfs! It makes it very easy to quickly create a website. I definitely recommend it and we will definitely use it always.***❜❜

***-[empp](https://wordpress.org/support/users/empp/)***


#### ⭐⭐⭐⭐⭐ [good plugin](https://wordpress.org/support/topic/good-plugin-6365/)

❛❛***This pdf poster plugin does really solve my requirement.***❜❜

***-[a2zdoctors](https://wordpress.org/support/users/a2zdoctors/)***


#### - Did you like this plugin? Dislike it? Have a feature request? [Please share your feedback with us](mailto:support@bplugins.com 'Send feedback')

= ⭐ Check out our other WordPress Plugins = 

🔥 **[Html5 Audio Player](https://audioplayerwp.com/)** – Best audio player plugin for WordPress.

🔥 **[Html5 Video Player](https://wpvideoplayer.com/)** – Best video player plugin for WordPress.

🔥 **[StreamCast](https://wordpress.org/plugins/streamcast)** – A fully-featured Radio Player Plugin for WordPresss.

🔥 **[3D Viewer](https://3d-viewer.bplugins.com/)** – Display interactive 3D models on the webs.



== Installation ==

This section describes how to install the plugin and get it working.
e.g.
1. Upload `plugin-directory` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Use shortcode on page, post, or in widgets.
4. If you want a news ticker in your theme php, Place `<?php echo do_shortcode('YOUR_SHORTCODE'); ?>` in your templates

== Frequently Asked Questions ==

= How many PDF files can I embed using PDF Poster plugin? =

There are no limitations! you can embed an unlimited number of PDF files.

= Can I embed MS Office document? =

No, You can't. But we have another plugin for embedding Office Doc Called [Document Embedder](https://wordpress.org/plugins/document-emberdder "Document Embedder")

= Can I embed a PDF with a download option? =

Yes, You can allow / Disallow users to download the file, it's totally in your control.

= Will this plugin work/be compatible with the theme I use? =

This plugin is compatible with most themes. But, unfortunately, we cannot check it with all third-party themes (especially paid ones) for compatibility, therefore there are cases when this plugin does not work with a third-party theme. We constantly check this plugin for compatibility with third-party themes. If we find that this plugin is incompatible with a third-party theme, and if we can fix it on our part, we release an update of our plugin to fix the problem.

If you find a conflict between our plugin and a third-party theme, please let us know and we will definitely release an update of our plugin to fix the problem.
 
= Will this plugin work/be compatible with other plugins that I use? =
This plugin is compatible with most plugins. But, unfortunately, we cannot check it with all third-party plugins (especially paid ones) for compatibility, therefore there are cases when this plugin does not work with a third-party plugin. We constantly check this plugin for compatibility with third-party plugins. If we find that this plugin is incompatible with a third-party plugin, and if we can fix it on our part, we release an update of our plugin to fix the problem.

If you find a conflict between our plugin and a third-party plugin, please let us know and we will definitely release an update of our plugin to fix the problem.



== Screenshots ==

1. Sidebar menu
2. Adding a pdf file in dashboard area.
3. Output / Frontend preview
4. Full Screen preview 

== Changelog ==

= 2.2.0 - 06 Oct, 2024 =
* Fixed: Overlapping content

= 2.1.25 - 24 July, 2024 =
* Fixed: 404 Not Found - PDFPro\Helper\Pipe::checkPipe() 

= 2.1.24 - 29 June, 2024 =
* Fixed: iPad/iPhone issue

= 2.1.23 - 27 June, 2024 =
* Fixed: directive error

= 2.1.22 - 24 June, 2024 =
* Fixed: Vulnerability


= 2.1.21 - 25 March, 2024 =
* Fixed: Avada Builder style broken

= 2.1.20 - 19 Feb, 2024 =
* Fixed: Deprecated issue

= 2.1.19 - 9 Jan, 2024 =
* Feature: Show Download button

= 2.1.18 - 24 Dec, 2023 =
* Security: Improved

= 2.1.17 - 4 Dec, 2023 =
* Removed Ads

= 2.1.16 - 28 Nov, 2023 =
* Fixed: Can't Edit Newsletter

= 2.1.15 - 24 Nov, 2023 =
* Fixed: Removed admin ber add

= 2.1.14 - 24 Nov, 2023 =
* Fixed: Sidebar open all time

= 2.1.12 - 17 Sep, 2023 =
* Fixed SSL issue

= 2.0.10 - 4/5/2022 =
* solved - "Oops! You forgot to select a pdf file."

= 2.0.9 - 4/4/2022 =
* Fixed SSL issue

= 2.0.8 - 1/26/2022 =
* Fixed Responsive issue

= 2.0.7 =
* Forbidden issue fixed

= 2.0.6 = 
* Disable Gutenberg as default shortcode generator

= 2.0.3 = 
* Option to enable/disable Gutenberg Shortcode Generator

= 2.0.0 =
* Added Advanced Shortcode Generator
* Improved Security Performance
* Fixed Height issue on mobile device

= 1.6 =
* Fix isuue and make compatible with Wordpress 5.5 Version

= 1.5 =
* Fix Gutenberg block issue
* Fix toggle menu in viewer
 
= 1.4 =
* Add support for Gutenberg Block

= 1.3 =
* Fix an issue 

= 1.2 =
* removed an ad.
* fix issue which causes centralize the content.
* Improved performance. 

= 1.1 =
* fix an issue with pdf positions

= 1.0 =
* Initial Release

== Upgrade Notice ==

= 2.1.21 - 25 March, 2024 =
* Fixed: Avada Builder style broken