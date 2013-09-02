<?php

if (!class_exists ('WpLinkedDataInitializer')) {
    class WpLinkedDataInitializer {

        function initialize () {
            $this->registerRdfNamespaces ();
            $contentNegotiation = $this->getSupportedContentNegotiation ();
            $rdfBuilder = new \org\desone\wordpress\wpLinkedData\RdfBuilder();
            $rdfPrinter = new \org\desone\wordpress\wpLinkedData\RdfPrinter();
            $interceptor = new \org\desone\wordpress\wpLinkedData\RequestInterceptor(
                $contentNegotiation, $rdfBuilder, $rdfPrinter
            );
            return $interceptor;
        }

        function getUserProfileController () {
            return new \org\desone\wordpress\wpLinkedData\UserProfileController();
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