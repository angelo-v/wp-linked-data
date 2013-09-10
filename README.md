# wp-linked-data

WordPress-Plugin to publish blog contents as Linked Data.

Version 0.3

## Installation

The plugin is available at the plugin repository. Just search for wp-linked-data in the plugins section of your workpress admin backend.

Alternatively you may copy the contents of the /src directory to wp-content/plugins/wp-linked-data directory of your WordPress installation and then activate the plugin from plugins page.

At least PHP 5.3.0 is required to use this plugin.

It is recommended that you install the pecl_http PHP extension (http://pecl.php.net/package/pecl_http). The plugin will work without it, but only with a simplified, inaccurate content negotiation.

## Usage

### Linked Data

Turtle and RDF/XML documents can be retrieved performing a HTTP GET request with an appropriate HTTP-Accept-Header set. Blog posts and pages are identified by their original document URI appended by the fragment identifier #it.

E.g. if a blog post ist available at http://example.org/2013/04/my-first-blog-post, the post itself (as an "abstract thing") is identified by http://example.org/2013/04/my-first-blog-post#it

You may use curl to retrieve Linked Data, e.g.:

curl -H 'Accept: text/turtle' http://example.org/2013/04/my-first-blog-post#it

An author, as a person, is per default identified by the author page URI appended by the fragment identifier #me.

E.g. if the authors page is http://example.org/author/alice, the person Alice is identified by http://example.org/author/alice#me

You may try curl again, to retrieve a FOAF-Profile:

curl -H 'Accept: text/turtle' http://example.org/author/alice#me

Instead of using WordPress to host the FOAF-Profile, you are able to link your existing WebID to your WordPress account. (See next section)

### WebID

The Plugin adds a WebID section to the user profile screen in the admin backend. (Note: The section is only available, when editing _your own_ profile).

#### WebID Location

You can choose, where your WebID is hosted:

1. Locally hosted WebID: The WebID is hosted within your wordpress blog at http://[your-domain]/author/[your-username]#me
2. Custom WebID: You may enter whatever your WebID URI is and your WordPress account will be linked to it.

Whatever option you choose, your wordpress account will always be identified as "http://[your-domain]\>/author/[your-username]>#account". The option only affects, how you, as a person, will be identified.

If you do not have a WebID yet, choose the first option, or get a WebID at http://my-profile.eu. More Information about WebID: http://webid.info/


#### RSA Public Key

You may enter the exponent and modulus of the public key of your WebID certificate. This will allow you to use your WordPress WebID for authentication elsewhere on the web. The wp-linked-data plugin is not yet capable of creating WebID certificates, so you will have to create the certificate with another tool (e.g. openssl) and enter the data into this section afterwards.

#### Additional RDF Triples

You may enter any RDF triples as RDF/XML, Turtle or N3. The triples will occur in the RDF representation of your WordPress profile document at at http://[your-domain]/author/[your-username]

## Contact

Please contact me for any questions & feedback: [angelo.veltens@online.de](mailto:angelo.veltens@online.de)

## Release Notes

Version 0.1

- puplishing blog post metadata as linked data
- publishing FOAF profiles for blog authors
- content negotiation supporting Turtle and RDF/XML format

Version 0.2

- distinguish users (persons) and their user accounts
- use sioc:creator_of instead of foaf:publications
- replaced inexistent dc:content by sioc:content (plain text) for blog post content
- added sioc:Weblog resource for the blog itself

Version 0.3

- choose between locally hosted WebID and custom WebID
- add RSA public key to your profile
- add custom RDF triples to your profile document

## License

GPLv3 - http://www.gnu.org/licenses/gpl-3.0
