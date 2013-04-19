# wp-linked-data

Wordpress-Plugin to publish blog contents as Linked Data.

Version 0.1

## Installation

The plugin is not yet available at the plugin repository.

Just copy the contents of the /src directory to wp-content/plugins/wp-linked-data directory of your WordPress installation and then activate the plugin from plugins page.

At least PHP 5.3.0 is required to use this plugin.

It is recommended that you install the pecl_http PHP extension (http://pecl.php.net/package/pecl_http). The plugin will work without it, but only with a simplified, inaccurate content negotiation.

## Usage

Turtle and RDF/XML documents can be retrieved performing a HTTP GET request with an appropriate HTTP-Accept-Header set. Blog posts and pages are identified by their original document URI appended by the fragment identifier #it.

E.g. if a blog post ist available at http://example.org/2013/04/my-first-blog-post, the post itself (as an "abstract thing") is identified by http://example.org/2013/04/my-first-blog-post#it

You may use curl to retrieve Linked Data, e.g.:

curl -H 'Accept: text/turtle' http://example.org/2013/04/my-first-blog-post#me

An author, as a person, is identified by the author page URI appended by the fragment identifier #me.

E.g. if the authors page is http://example.org/author/alice, the person Alice is identified by http://example.org/author/alice#me

You may try curl again, to retrieve a FOAF-Profile:

curl -H 'Accept: text/turtle' http://example.org/author/alice#me

## Contact

Please contact me for any questions & feedback: [angelo.veltens@online.de](mailto:angelo.veltens@online.de)

## Release Notes

Version 0.1

- puplishing blog post metadata as linked data
- publishing FOAF profiles for blog authors
- content negotiation supporting Turtle and RDF/XML format


## License

GPLv3 - http://www.gnu.org/licenses/gpl-3.0