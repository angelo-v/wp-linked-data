<?php

namespace org\desone\wordpress\wpLinkedData;

use PHPUnit\Framework\TestCase;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'src/request/WilldurandContentNegotiation.php';

class WilldurandContentNegotiationTest extends TestCase {

    public function testNegotiateRdfXml () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml');
        $this->assertEquals (RdfType::RDF_XML, $type);
    }

    public function testNegotiateTurtle () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/turtle');
        $this->assertEquals (RdfType::TURTLE, $type);
    }

    public function testReturnTurtleIfBothAccepted () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml, text/turtle');
        $this->assertEquals (RdfType::TURTLE, $type);
    }

    public function testReturnNullIfNoRdfTypeAccepted () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/html');
        $this->assertEquals (null, $type);
    }

    public function testReturnBestQualityIfBothAccepted() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml;q=0.4, text/turtle;q=0.8, text/n3;q=0.2, text/plain;q=0.1');
        $this->assertEquals (RdfType::TURTLE, $type);
    }

}
