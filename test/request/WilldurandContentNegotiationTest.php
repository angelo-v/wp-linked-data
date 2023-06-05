<?php

namespace org\desone\wordpress\wpLinkedData;

use PHPUnit\Framework\TestCase;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'src/request/WilldurandContentNegotiation.php';

class WilldurandContentNegotiationTest extends TestCase {

    public function testNegotiateRdfXml () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml');
        self::assertEquals ('application/rdf+xml', $type);
    }

    public function testNegotiateTurtle () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/turtle');
        self::assertEquals ('text/turtle', $type);
    }

    public function testNegotiateNTriples() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/n-triples');
        self::assertEquals ('application/n-triples', $type);
    }

    public function testNegotiateJsonLd() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/ld+json');
        self::assertEquals ('application/ld+json', $type);
    }

    public function testNegotiateN3() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/n3');
        self::assertEquals ('text/n3', $type);
    }

    public function testFallBackToTurtleWhenNegotiatedTrig () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/trig');
        self::assertEquals ('text/turtle', $type);
    }

    public function testReturnTurtleIfBothAccepted () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml, text/turtle');
        self::assertEquals ('text/turtle', $type);
    }

    public function testReturnNullIfNoRdfTypeAccepted () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/html');
        self::assertEquals (null, $type);
    }

    public function testReturnNullIfAcceptHeaderIsNull () {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType(null);
        self::assertEquals (null, $type);
    }

    public function testServeHtmlIfAnythingAcceptedButHtmlPrefered() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/html,*/*;q=0.8');
        self::assertEquals (null, $type);
    }

    public function testReturnBestQualityIfManyAccepted() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml;q=0.4, text/turtle;q=0.8, text/n3;q=0.2, text/plain;q=0.1, text/html;q=0.2');
        self::assertEquals ('text/turtle', $type);
    }

    public function testFallBackToSecondBestFormatAfterTrig() {
        $contentNegotiation = new WilldurandContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml;q=0.9, text/turtle;q=0.1, application/trig;q=1');
        self::assertEquals ('application/rdf+xml', $type);
    }

}
