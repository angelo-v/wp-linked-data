<?php

namespace org\desone\wordpress\wpLinkedData;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'src/rdf/RdfType.php';

class RdfTypeTest extends \PHPUnit_Framework_TestCase {

    public function testGetMimeTypeRdfXml () {
        $this->assertEquals ('application/rdf+xml', RdfType::getMimeType(RdfType::RDF_XML));
    }

    public function testGetMimeTypeTurtle () {
        $this->assertEquals ('text/turtle', RdfType::getMimeType(RdfType::TURTLE));
    }

    public function testGetMimeTypeUnknownFormat () {
        $this->assertEquals ('text/plain', RdfType::getMimeType('unknown'));
    }

    public function testGetByMimeTypeRdfXml () {
        $this->assertEquals (RdfType::RDF_XML, RdfType::getByMimeType('application/rdf+xml'));
    }

    public function testGetByMimeTypeTurtle () {
        $this->assertEquals (RdfType::TURTLE, RdfType::getByMimeType('text/turtle'));
    }

    public function testGetByMimeTypeUnknown () {
        $this->assertNull (RdfType::getByMimeType('text/html'));
    }

}
