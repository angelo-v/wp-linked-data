<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Prints out an RDF graph in a given format
 */
class RdfPrinter {

    /**
     * Prints the given graph in the given format
     * @param $graph
     * @param $mimeType
     */
    public function printGraph ($graph, $mimeType) {
        $this->addContentTypeHeader ($mimeType);
        $this->allowCors();
        $data = $graph->serialise ($mimeType);
        if (!is_scalar ($data)) {
            $data = var_export ($data, true);
        }
        print $data;
    }

    private function addContentTypeHeader ($mimeType) {
        header ('Content-Type: ' . $mimeType);
    }

    // Linked data should be allowed to fetched from all origins
    private function allowCors() {
        header ('Access-Control-Allow-Origin: ' . '*');
    }

}
