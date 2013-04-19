<?php

namespace org\desone\wordpress\wpLinkedData;

require_once( WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/ContentNegotiation.php');

/**
 * A simple, inaccurate content negotiation that is used if pecl_http extension is missing
 */
class SimplifiedContentNegotiation implements ContentNegotiation
{

    public function negotiateRdfContentType($acceptHeader)
    {
        $acceptedTypes = array_map("trim", preg_split('/(,|;)/', $acceptHeader));
        if (in_array('text/turtle', $acceptedTypes)) {
            return RdfType::TURTLE;
        }
        if (in_array('application/rdf+xml', $acceptedTypes)) {
            return RdfType::RDF_XML;
        }
        return null;

    }
}

?>