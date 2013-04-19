<?php

namespace org\desone\wordpress\wpLinkedData;

require_once( WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/ContentNegotiation.php');

/**
 * Delegates content negotiation to the pecl_http extension
 */
class PeclHttpContentNegotiation implements ContentNegotiation
{

    public function negotiateRdfContentType($acceptHeader)
    {
        $contentTypes = array('application/rdf+xml', 'text/turtle');
        $negotiationResults = array ();
        $negotiatedType = http_negotiate_content_type($contentTypes, $negotiationResults);
        if (!empty ($negotiationResults)) {
            return RdfType::getByMimeType($negotiatedType);
        }
        return null;
    }
}

?>