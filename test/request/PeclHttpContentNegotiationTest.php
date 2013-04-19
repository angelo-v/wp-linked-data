<?php

namespace org\desone\wordpress\wpLinkedData;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'src/request/PeclHttpContentNegotiation.php';

$mocked_http_negotiate_content_type_result = null;

function http_negotiate_content_type ($supported_types, &$results) {
    global $mocked_http_negotiate_content_type_result;
    if ($supported_types == array('application/rdf+xml', 'text/turtle')) {
        if ($mocked_http_negotiate_content_type_result != null) {
            array_push($results, $mocked_http_negotiate_content_type_result);
            return $mocked_http_negotiate_content_type_result;
        }
        return 'application/rdf+xml';
    }
    return 'unexpected supported types';
}

class PeclHttpContentNegotiationTest extends \PHPUnit_Framework_TestCase {

    public function testNegotiateRdfXml () {
        global $mocked_http_negotiate_content_type_result;
        $mocked_http_negotiate_content_type_result = 'application/rdf+xml';
        $contentNegotiation = new PeclHttpContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('application/rdf+xml');
        $this->assertEquals (RdfType::RDF_XML, $type);
    }

    public function testReturnNullIfNegotiationDidNotReturnResults () {
        $contentNegotiation = new PeclHttpContentNegotiation();
        $type = $contentNegotiation->negotiateRdfContentType('text/html');
        $this->assertNull ($type);
    }

}
