<?php

namespace org\desone\wordpress\wpLinkedData;

/**
 * Build an RDF graph from queried wordpress data
 */
class RdfBuilder {

    public function buildGraph ($queriedObject, $wpQuery) {
        $graph = new \EasyRdf_Graph();
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
        $post_resource->set ('dc:content', $post->post_content);
        $post_resource->set ('dc:modified', \EasyRdf_Literal_Date::parse($post->post_modified));
        $post_resource->set ('dc:created', \EasyRdf_Literal_Date::parse($post->post_date));

        $author = get_userdata ($post->post_author);
        $author_uri = $this->getAuthorUri ($author);
        $author_resource = $graph->resource ($author_uri, 'foaf:Person');
        $author_resource->set ('foaf:name', $author->display_name);
        $post_resource->set ('dc:creator', $author_resource);

        return $graph;
    }

    private function getPostUri ($post) {
        return untrailingslashit (get_permalink ($post->ID)) . '#it';
    }

    private function getAuthorUri ($author) {
        return untrailingslashit (get_author_posts_url ($author->ID)) . '#me';
    }

    private function getRdfTypeForPost ($queriedObject) {
        if ($queriedObject->post_type == 'post') {
            return 'sioct:BlogPost';
        }
        return 'sioc:Item';
    }

    private function buildGraphForUser ($graph, $user, $wpQuery) {
        $author_uri = $this->getAuthorUri ($user);
        $author_resource = $graph->resource ($author_uri, 'foaf:Person');
        $author_resource->set ('foaf:name', $user->display_name ?: null);
        $author_resource->set ('foaf:givenName', $user->user_firstname ?: null);
        $author_resource->set ('foaf:familyName', $user->user_lastname ?: null);
        $author_resource->set ('foaf:nick', $user->nickname ?: null);
        $author_resource->set ('bio:olb', $user->user_description ?: null);

        while ($wpQuery->have_posts ()) {
            $wpQuery->next_post ();
            $post = $wpQuery->post;
            $post_uri = $this->getPostUri ($post);
            $post_resource = $graph->resource ($post_uri, 'sioct:BlogPost');
            $post_resource->set ('dc:title', $post->post_title);
            $author_resource->add ('foaf:publications', $post_resource);

        }
        return $graph;
    }

}