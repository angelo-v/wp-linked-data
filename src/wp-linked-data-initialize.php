<?php

if (!class_exists ('WpLinkedDataInitializer')) {
    class WpLinkedDataInitializer {

        private $webIdService;

        function initialize () {
            $this->registerRdfNamespaces ();
            $this->webIdService = new \org\desone\wordpress\wpLinkedData\UserProfileWebIdService();
            $contentNegotiation = $this->getSupportedContentNegotiation ();
            $rdfBuilder = new \org\desone\wordpress\wpLinkedData\RdfBuilder($this->webIdService);
            $rdfPrinter = new \org\desone\wordpress\wpLinkedData\RdfPrinter();
            $interceptor = new \org\desone\wordpress\wpLinkedData\RequestInterceptor(
                $contentNegotiation, $rdfBuilder, $rdfPrinter
            );
            return $interceptor;
        }

        function getUserProfileController () {
            return new \org\desone\wordpress\wpLinkedData\UserProfileController($this->webIdService);
        }

        private function registerRdfNamespaces () {
            EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
            EasyRdf_Namespace::set ('sioct', 'http://rdfs.org/sioc/types#');
        }

        private function getSupportedContentNegotiation () {
            if (self::isPeclHttpInstalled ()) {
                return new \org\desone\wordpress\wpLinkedData\PeclHttpContentNegotiation();
            } else {
                return new \org\desone\wordpress\wpLinkedData\SimplifiedContentNegotiation();
            }
        }

        public static function isPeclHttpInstalled () {
            return in_array ('http', get_loaded_extensions ());
        }

    }
} else {
    exit ('Class WpLinkedDataInitializer already exists');
}