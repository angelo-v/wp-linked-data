<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Intercepts HTTP requests to serve RDF if requested by the client
 */
class RequestInterceptor {

    /** @var ContentNegotiation */
    private $contentNegotiation;

    /** @var RdfBuilder */
    private $rdfBuilder;

    /** @var RdfPrinter */
    private $rdfPrinter;

    /**
     * @param $contentNegotiation - The content negotiation strategy to use
     * @param $rdfBuilder - The RdfBuilder used for building rdf graphs
     * @param $rdfPrinter - The RdfPrinter used to print the built rdf graphs
     */
    public function __construct ($contentNegotiation, $rdfBuilder, $rdfPrinter) {
        $this->contentNegotiation = $contentNegotiation;
        $this->rdfBuilder = $rdfBuilder;
        $this->rdfPrinter = $rdfPrinter;
    }

    /**
     * Intercepts the current HTTP request and serves an RDF document
     * if the content negotiation results in a supported RDF format
     */
    public function intercept () {
        $rdf_format = ($this->contentNegotiation->negotiateRdfContentType ($_SERVER['HTTP_ACCEPT']));
        if ($rdf_format != null) {
            global $wp_query;
            $graph = $this->rdfBuilder->buildGraph (get_queried_object (), $wp_query);
            $this->rdfPrinter->printGraph ($graph, $rdf_format);
            exit;
        }
    }


}
