<?php

namespace org\desone\wordpress\wpLinkedData;

require_once( WP_LINKED_DATA_PLUGIN_DIR_PATH . 'request/ContentNegotiation.php');

/**
 * Delegates content negotiation to the willdurand/negotiation library
 */
class WilldurandContentNegotiation implements ContentNegotiation
{

    public function negotiateRdfContentType($acceptHeader)
    {
        $negotiator = new \Negotiation\Negotiator();

        $priorities   = array('text/html', 'application/ld+json', 'text/turtle', 'text/n3', 'application/n-triples', 'application/rdf+xml', 'application/trig;q=0.1');
        $mediaType = $negotiator->getBest($acceptHeader, $priorities);

        if (is_null($mediaType)) {
            return null;
        }

        $negotiatedType = $mediaType->getType();

        if ($negotiatedType == "application/trig") {
            return "text/turtle";
        }

        if ($negotiatedType == "text/html") {
            return null;
        }

        return $negotiatedType;

    }
}

?>
