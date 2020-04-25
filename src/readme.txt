=== wp-linked-data ===
Contributors: aveltens
Author URI: https://angelo.veltens.org/profile/card#me
Tags: linked data, rdf, semantic web, webid, solid
Requires at least: 3.5.1
Tested up to: 5.4
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0

Publishes blog post & author data as Linked Data.

== Description ==

The plugin publishes Linked Data about your blog contents and helps you hosting or connecting your WebID.

= Linked Data =

Turtle and RDF/XML documents can be retrieved performing a HTTP GET request with an appropriate HTTP-Accept-Header set. Blog posts and pages are identified by their original document URI appended by the fragment identifier #it.

E.g. if a blog post ist available at http://example.org/2013/04/my-first-blog-post, the post itself (as an "abstract thing") is identified by http://example.org/2013/04/my-first-blog-post#it

You may use curl to retrieve Linked Data, e.g.:

curl -H 'Accept: text/turtle' http://example.org/2013/04/my-first-blog-post#it

An author, as a person, is per default identified by the author page URI appended by the fragment identifier #me.

E.g. if the authors page is http://example.org/author/alice, the person Alice is identified by http://example.org/author/alice#me

You may try curl again, to retrieve a FOAF-Profile:

curl -H 'Accept: text/turtle' http://example.org/author/alice#me

Instead of using WordPress to host the FOAF-Profile, you are able to link your existing WebID to your WordPress account. (See next section)

= WebID =

The Plugin adds a WebID section to the user profile screen in the admin backend. (Note: The section is only available, when editing _your own_ profile).

**WebID Location**

You can choose, where your WebID is hosted:

1. Locally hosted WebID: The WebID is hosted within your wordpress blog at http://[your-domain]/author/[your-username]#me
2. Custom WebID: You may enter whatever your WebID URI is and your WordPress account will be linked to it.

Whatever option you choose, your wordpress account will always be identified as "http://[your-domain]\>/author/[your-username]>#account". The option only affects, how you, as a person, will be identified.

If you do not have a WebID yet, choose the first option, or get a WebID at https://solid.community. More Information about WebID: http://webid.info/

**RSA Public Key**

You may enter the exponent and modulus of the public key of your WebID certificate. This will allow you to use your WordPress WebID for authentication elsewhere on the web. The wp-linked-data plugin is not yet capable of creating WebID certificates, so you will have to create the certificate with another tool (e.g. openssl) and enter the data into this section afterwards.

**Additional RDF**

You may enter any RDF triples as RDF/XML, Turtle or N3. The triples will occur in the RDF representation of your WordPress profile document at at http://[your-domain]/author/[your-username]

== Installation ==

Just copy the contents of this directory to wp-content/plugins/wp-linked-data directory of your WordPress installation and then activate the plugin from plugins page.

At least PHP 5.3.0 is required to use this plugin.

== Changelog ==

= 0.5.1 =

* Fix: Serve HTML if HTML is preferred, even if RDF other formats are accepted via wildcard

= 0.5 =

* better support for content-negotiation. No need to intstall pecl_http anymore
* add foaf:homepage to the Weblog resource
* no able to serve the following content types:
  * application/ld+json
  * text/turtle
  * text/n3
  * application/n-triples
  * application/rdf+xml

= 0.4 =

* add Access-Control-Allow-Origin header to allow linked data clients to fetch the data
* Link to https://solid.community to get a WebID

= 0.3 =
* choose between locally hosted WebID and custom WebID
* add RSA public key to your profile
* add custom RDF triples to your profile document

= 0.2 =
* distinguish users (persons), and their user accounts
* use sioc:creator_of instead of foaf:publications
* replaced inexistent dc:content by sioc:content (plain text) for blog post content
* added sioc:Weblog resource for the blog itself

= 0.1 =
* publishing blog post metadata as linked data
* publishing FOAF profiles for blog authors
* content negotiation supporting Turtle and RDF/XML format
