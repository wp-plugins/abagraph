=== ABAgraph ===
Contributors: pacius, rayholland 
Donate link: http://abacms.org/
Tags: ABA graph, Applied Behavioral Analysis 
Requires at least: 2.0
Tested up to: 2.7 
Stable tag: 0.9.1

Graphing for Applied Behavioral Analysis (ABA) for Autism. 

== Description ==

[Howto Video](http://abacms.org/?page_id=34). There are a number of preset variables such as Generalization, Pretest, Baseline. Activities can be added/deleted and will be graphed as different colored lines. As data is entered graph appears below. If mistake made entering data, data point can be deleted and graph is redrawn. When complete right-click graph and copy location, go to Editor "Add Image" and paste in location or "Browse Server" for file. Graph is drawn as .png file in (wp-content/uploads). Requires php-image-graph, php-image-canvas, php-gd, php-pear, Image_Color. Calgraph grew out of Simple Graph by Pasi Matilainen.

== Installation ==

1. Unzip abagraph.zip in `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Check `/wp-content/uploads/` exists and is writeable by webserver (permissions of 755 sufficient if owned by webserver user)
1. Requires php-image-graph, php-image-canvas, php-gd, php-pear, Image_Color installed (See below). 

= To Install Requirements: =
On OS like Ubuntu:

\# apt-get install php-image-graph php-image-canvas
(_Will also install php-pear and php-gd as dependencies_)

\# pear install Image_Color


== Frequently Asked Questions ==

= The graph is not drawn or redrawn with the new data I just added or deleted =
Your browser may be caching older image: clear cache. You can also set browser to check site for new image on each visit instead of using its cache.

= How can I create a graph for an ABA student =
Create a username for the student, include First and Last name as these will appear in graph title. Login with student username and create graph.

= I logged in and can't find my graph =
Graphs are created per user and named "username""graph#"\_abagraph.png (like admin1\_abagraph.png). You can't view other users graphs from CALgraph page. You can though publish other users graphs if you know username and graph# - "Add Image" in Editor and "Browse Server" for file. 

= Possible Errors =

When drawing graph:

Warning: imagepng() [function.imagepng]: Unable to open '../wp-content/uploads/admin1_abagraph.png' for writing: Permission denied in /usr/share/php/Image/Canvas/GD/PNG.php on line 119

Solution: Make sure uploads directory exists and is writeable by webserver.

== Screenshots ==

1. Data entered here.
2. Graph appearing below. Above graph: list of data points entered.
