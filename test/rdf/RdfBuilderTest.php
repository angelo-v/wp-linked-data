<?php

namespace org\desone\wordpress\wpLinkedData;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'test/mock/WP_Query.php';
require_once 'test/mock/WP_Post.php';
require_once 'test/mock/WP_User.php';
require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'lib/EasyRdf.php');
require_once 'src/rdf/RdfBuilder.php';


function untrailingslashit ($string) {
    return $string;
}

function get_permalink ($id) {
    return 'http://example.com/' . $id;
}

function get_userdata ($id) {
    return new \WP_User($id, 'Mario Mustermann');
}

function get_author_posts_url ($id) {
    return 'http://example.com/author/' . $id;
}


class RdfBuilderTest extends \PHPUnit_Framework_TestCase {

    public function testBuildGraphForPost () {

        \EasyRdf_Namespace::set ('sioct', 'http://rdfs.org/sioc/types#');

        $builder = new RdfBuilder();
        $post = new \WP_Post();

        $post->ID = 1;
        $post->post_type = 'post';
        $post->post_title = 'My first blog post';
        $post->post_modified = '2013-04-17 20:16:41';
        $post->post_date = '2013-03-17 19:16:41';
        $post->post_content = 'The posts content';
        $post->post_author = 1;

        $graph = $builder->buildGraph ($post, null);

        $postUri = 'http://example.com/1#it';
        $it = $graph->resource ($postUri);
        $this->assertEquals ('sioct:BlogPost', $it->type ());
        $this->assertEquals ('My first blog post', $it->get ('dc:title'));
        $this->assertEquals ('The posts content', $it->get ('dc:content'));

        $this->assertEquals (\EasyRdf_Literal_Date::parse ('2013-04-17 20:16:41'), $it->get ('dc:modified'));
        $this->assertEquals (\EasyRdf_Literal_Date::parse ('2013-03-17 19:16:41'), $it->get ('dc:created'));

        $author = $graph->get ($postUri, 'dc:creator');
        $this->assertEquals ('http://example.com/author/1#me', $author->getUri ());
        $this->assertEquals ('foaf:Person', $author->type ());
        $this->assertEquals ('Mario Mustermann', $author->get ('foaf:name'));
    }

    public function testBuildGraphForUserWithoutPosts () {
        \EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder();
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $user->nickname = 'mmuster';
        $user->user_description = 'just me, muster';
        $user->user_firstname = 'Maria';
        $user->user_lastname = 'Musterfrau';
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $this->assertEquals ('foaf:Person', $me->type ());
        $this->assertProperty ($me, 'foaf:name', 'Maria Musterfrau');
        $this->assertProperty ($me, 'foaf:nick', 'mmuster');
        $this->assertProperty ($me, 'foaf:givenName', 'Maria');
        $this->assertProperty ($me, 'foaf:familyName', 'Musterfrau');
        $this->assertProperty ($me, 'bio:olb', 'just me, muster');
        $this->assertPropertyNotPresent($me, 'foaf:publications');
    }

    private function assertProperty ($subject, $predicate, $value) {
        $this->assertEquals ($value, $subject->get ($predicate));
    }

    public function testBuildGraphForUserWithoutPostsAndData () {
        \EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder();
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $this->assertEquals ('foaf:Person', $me->type ());
        $this->assertEquals ('Maria Musterfrau', $me->get ('foaf:name'));
        $this->assertPropertyNotPresent ($me, 'foaf:nick');
        $this->assertPropertyNotPresent ($me, 'foaf:givenName');
        $this->assertPropertyNotPresent ($me, 'foaf:familyName');
        $this->assertPropertyNotPresent ($me, 'bio:olb');
        $this->assertPropertyNotPresent ($me, 'foaf:publications');
    }

    private function assertPropertyNotPresent ($me, $predicate) {
        $this->assertNull ($me->get ($predicate), 'No '. $predicate .' should be present');
    }

    public function testBuildGraphForUserWithoutPostsAndEmptyData () {
        \EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder();
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $user->nickname = '';
        $user->user_description = '';
        $user->user_firstname = '';
        $user->user_lastname = '';
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $this->assertEquals ('foaf:Person', $me->type ());
        $this->assertEquals ('Maria Musterfrau', $me->get ('foaf:name'));
        $this->assertPropertyNotPresent ($me, 'foaf:nick');
        $this->assertPropertyNotPresent ($me, 'foaf:givenName');
        $this->assertPropertyNotPresent ($me, 'foaf:familyName');
        $this->assertPropertyNotPresent ($me, 'bio:olb');
        $this->assertPropertyNotPresent ($me, 'foaf:publications');
    }

    public function testBuildGraphForUserWithOnePost () {
        \EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder();
        $user = new \WP_User(2, 'Maria Musterfrau');

        $firstPost = new \WP_Post();
        $firstPost->ID = 1;
        $firstPost->post_type = 'post';
        $firstPost->post_title = 'My first blog post';
        $firstPost->post_modified = '2013-04-17 20:16:41';
        $firstPost->post_date = '2013-03-17 19:16:41';
        $firstPost->post_content = 'The posts content';
        $firstPost->post_author = 2;

        $posts = array($firstPost);
        $graph = $builder->buildGraph ($user, new \WP_Query($posts));

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $publications = $me->allResources ('foaf:publications');
        $this->assertEquals (1, count($publications), 'User should have 1 publication');
        $publication = array_shift($publications);
        $this->assertEquals ('http://example.com/1#it', $publication->getUri());
        $this->assertEquals ('sioct:BlogPost', $publication->type());
        $this->assertProperty($publication, 'dc:title', 'My first blog post');
    }

    public function testBuildGraphForUserWithMultiplePosts () {
        \EasyRdf_Namespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder();
        $user = new \WP_User(2, 'Maria Musterfrau');

        $firstPost = new \WP_Post();
        $firstPost->ID = 1;
        $firstPost->post_type = 'post';
        $firstPost->post_title = 'My first blog post';
        $firstPost->post_modified = '2013-04-17 20:16:41';
        $firstPost->post_date = '2013-03-17 19:16:41';
        $firstPost->post_content = 'The posts content';
        $firstPost->post_author = 2;

        $secondPost = new \WP_Post();
        $secondPost->ID = 2;
        $secondPost->post_type = 'post';
        $secondPost->post_title = 'My second blog post';

        $posts = array($firstPost, $secondPost);
        $graph = $builder->buildGraph ($user, new \WP_Query($posts));

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $publications = $me->allResources ('foaf:publications');
        $this->assertEquals (2, count($publications), 'User should have 1 publication');
        $publication = array_shift($publications);
        $this->assertEquals ('http://example.com/1#it', $publication->getUri());
        $this->assertEquals ('sioct:BlogPost', $publication->type());
        $this->assertProperty($publication, 'dc:title', 'My first blog post');
        $publication2 = array_shift($publications);
        $this->assertEquals ('http://example.com/2#it', $publication2->getUri());
        $this->assertEquals ('sioct:BlogPost', $publication2->type());
        $this->assertProperty($publication2, 'dc:title', 'My second blog post');
    }

}
