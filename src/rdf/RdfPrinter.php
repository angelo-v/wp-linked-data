<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Prints out an RDF graph in a given format
 */
class RdfPrinter {

    /**
     * Prints the given graph in the given format
     * @param $graph
     * @param $rdf_format
     */
    public function printGraph ($graph, $rdf_format) {
        $this->addContentTypeHeader ($rdf_format);
        $data = $graph->serialise ($rdf_format);
        if (!is_scalar ($data)) {
            $data = var_export ($data, true);
        }
        print $data;
    }

    private function addContentTypeHeader ($rdfFormat) {
        $mimeType = RdfType::getMimeType($rdfFormat);
        header ('Content-Type: ' . $mimeType);
    }

}
