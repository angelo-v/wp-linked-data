<?php

namespace org\desone\wordpress\wpLinkedData;

require_once( WP_LINKED_DATA_PLUGIN_DIR_PATH . 'rdf/RdfType.php');

interface ContentNegotiation {

    public function negotiateRdfContentType ($acceptHeader);

}

?>