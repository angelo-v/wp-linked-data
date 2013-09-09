<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Build an RDF graph from queried wordpress data
 */
class RdfBuilder {

    private $webIdService;

    public function __construct ($webIdService) {
        $this->webIdService = $webIdService;
    }

    public function buildGraph ($queriedObject, $wpQuery) {
        $graph = new \EasyRdf_Graph();
        if (!$queriedObject) {
            return $this->buildGraphForBlog ($graph, $wpQuery);
        }
        if ($queriedObject) {
            if ($queriedObject instanceof \WP_User) {
                return $this->buildGraphForUser ($graph, $queriedObject, $wpQuery);
            }
            if ($queriedObject instanceof \WP_Post) {
                return $this->buildGraphForPost ($graph, $queriedObject);
            }
        }
        return $this->buildGraphForAllPostsInQuery ($graph, $wpQuery);
    }

    private function buildGraphForAllPostsInQuery ($graph, $wpQuery) {
        while ($wpQuery->have_posts ()) {
            $wpQuery->next_post ();
            $post = $wpQuery->post;
            $this->buildGraphForPost ($graph, $post);
        }
        return $graph;
    }

    private function buildGraphForPost ($graph, $post) {
        $type = $this->getRdfTypeForPost ($post);
        $post_uri = $this->getPostUri ($post);
        $post_resource = $graph->resource ($post_uri, $type);

        $post_resource->set ('dc:title', $post->post_title);
        $post_resource->set ('sioc:content', strip_tags($post->post_content));
        $post_resource->set ('dc:modified', \EasyRdf_Literal_Date::parse($post->post_modified));
        $post_resource->set ('dc:created', \EasyRdf_Literal_Date::parse($post->post_date));

        $author = get_userdata ($post->post_author);
        $accountUri = $this->webIdService->getAccountUri ($author);
        $accountResource = $graph->resource ($accountUri, 'sioc:UserAccount');
        $accountResource->set ('sioc:name', $author->display_name);
        $post_resource->set ('sioc:has_creator', $accountResource);

        $blogUri = $this->getBlogUri ();
        $blogResource = $graph->resource ($blogUri, 'sioct:Weblog');
        $post_resource->set ('sioc:has_container', $blogResource);

        return $graph;
    }

    private function getPostUri ($post) {
        return untrailingslashit (get_permalink ($post->ID)) . '#it';
    }

    private function getRdfTypeForPost ($queriedObject) {
        if ($queriedObject->post_type == 'post') {
            return 'sioct:BlogPost';
        }
        return 'sioc:Item';
    }

    private function buildGraphForUser ($graph, $user, $wpQuery) {
        $author_uri = $this->webIdService->getWebIdOf ($user);
        $account_uri = $this->webIdService->getAccountUri ($user);
        $author_resource = $graph->resource ($author_uri, 'foaf:Person');
        $account_resource = $graph->resource ($account_uri, 'sioc:UserAccount');

        $author_resource->set ('foaf:name', $user->display_name ?: null);
        $author_resource->set ('foaf:givenName', $user->user_firstname ?: null);
        $author_resource->set ('foaf:familyName', $user->user_lastname ?: null);
        $author_resource->set ('foaf:nick', $user->nickname ?: null);
        $author_resource->set ('bio:olb', $user->user_description ?: null);
        $author_resource->set ('foaf:account', $account_resource);

        $account_resource->set ('sioc:name', $user->display_name ?: null);
        $account_resource->set ('sioc:account_of', $author_resource);

        $this->addRsaPublicKey ($user, $graph, $author_resource);
        $this->addAdditionalRdf ($user, $graph);

        $this->linkAllPosts ($wpQuery, $graph, $account_resource, 'sioc:creator_of');
        return $graph;
    }

    private function addRsaPublicKey ($user, $graph, $author_resource) {
        $rsaPublicKey = $this->webIdService->getRsaPublicKey ($user);
        if ($rsaPublicKey) {
            $key_resource = $graph->newBNode ('cert:RSAPublicKey');
            $key_resource->set ('cert:exponent', new \EasyRdf_Literal_Integer($rsaPublicKey->getExponent ()));
            $key_resource->set ('cert:modulus', new \EasyRdf_Literal_HexBinary($rsaPublicKey->getModulus ()));
            $author_resource->set ('cert:key', $key_resource);
        }
    }

    private function addAdditionalRdf ($user, $graph) {
        $rdf = get_the_author_meta ('additionalRdf', $user->ID);
        if (!empty($rdf)) {
            $graph->parse ($rdf);
        }
    }

    private function linkAllPosts ($wpQuery, $graph, $resourceToLink, $property) {
        while ($wpQuery->have_posts ()) {
            $wpQuery->next_post ();
            $post = $wpQuery->post;
            $post_uri = $this->getPostUri ($post);
            $post_resource = $graph->resource ($post_uri, 'sioct:BlogPost');
            $post_resource->set ('dc:title', $post->post_title);
            $resourceToLink->add ($property, $post_resource);
        }
    }

    private function buildGraphForBlog ($graph, $wpQuery) {
        $blogUri = $this->getBlogUri ();
        $blogResource = $graph->resource ($blogUri, 'sioct:Weblog');
        $blogResource->set ('rdfs:label', get_bloginfo('name') ?: null);
        $blogResource->set ('rdfs:comment', get_bloginfo('description') ?: null);
        $this->linkAllPosts ($wpQuery, $graph, $blogResource, 'sioc:container_of');
        return $graph;
    }

    private function getBlogUri () {
        return site_url () . '#it';
    }

}