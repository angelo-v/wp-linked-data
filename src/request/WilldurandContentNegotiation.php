<?php

namespace org\desone\wordpress\wpLinkedData;

require_once( WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/ContentNegotiation.php');

/**
 * Delegates content negotiation to the pecl_http extension
 */
class WilldurandContentNegotiation implements ContentNegotiation
{

    public function negotiateRdfContentType($acceptHeader)
    {
        $negotiator = new \Negotiation\Negotiator();

        $priorities   = array('text/turtle', 'application/rdf+xml');
        $mediaType = $negotiator->getBest($acceptHeader, $priorities);

        if (is_null($mediaType)) {
            return null;
        }

        $negotiatedType = $mediaType->getType();

        return RdfType::getByMimeType($negotiatedType);

    }
}

?>