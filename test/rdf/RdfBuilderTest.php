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

function site_url() {
    return 'http://example.com';
}

function get_bloginfo($show) {
    if ($show == 'name') return 'My cool blog';
    if ($show == 'description') return 'Cool description';
    return null;
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
        $this->assertProperty($it, 'dc:title', 'My first blog post');
        $this->assertProperty($it, 'sioc:content', 'The posts content');
        $this->assertProperty($it, 'dc:modified', \EasyRdf_Literal_Date::parse ('2013-04-17 20:16:41'));
        $this->assertProperty($it, 'dc:created', \EasyRdf_Literal_Date::parse ('2013-03-17 19:16:41'));

        $blogResource = $graph->resource ('http://example.com#it');
        $this->assertProperty($it, 'sioc:has_container', $blogResource);

        $creator = $graph->get ($postUri, 'sioc:has_creator');
        $this->assertEquals ('http://example.com/author/1#account', $creator->getUri ());
        $this->assertEquals ('sioc:UserAccount', $creator->type ());
        $this->assertProperty($creator, 'sioc:name', 'Mario Mustermann');
    }

    public function testPostContentIsPublishedAsPlainText () {
        $builder = new RdfBuilder();
        $post = new \WP_Post();

        $post->ID = 1;
        $post->post_type = 'post';
        $post->post_content = '<div cass="content">The <strong>posts</strong> content</div><img alt="foo" src="/foo.png" />';

        $graph = $builder->buildGraph ($post, null);
        $it = $graph->resource ('http://example.com/1#it');
        $this->assertProperty($it, 'sioc:content', 'The posts content');
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
        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);

        $this->assertEquals ('foaf:Person', $me->type ());
        $this->assertProperty ($me, 'foaf:name', 'Maria Musterfrau');
        $this->assertProperty ($me, 'foaf:nick', 'mmuster');
        $this->assertProperty ($me, 'foaf:givenName', 'Maria');
        $this->assertProperty ($me, 'foaf:familyName', 'Musterfrau');
        $this->assertProperty ($me, 'bio:olb', 'just me, muster');
        $this->assertProperty ($me, 'foaf:account', $account);

        $this->assertEquals ('sioc:UserAccount', $account->type ());
        $this->assertProperty ($account, 'sioc:name', 'Maria Musterfrau');
        $this->assertProperty ($account, 'sioc:account_of', $me);
        $this->assertPropertyNotPresent($account, 'sioc:creator_of');
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
        $this->assertProperty($me, 'foaf:name', 'Maria Musterfrau');
        $this->assertPropertyNotPresent ($me, 'foaf:nick');
        $this->assertPropertyNotPresent ($me, 'foaf:givenName');
        $this->assertPropertyNotPresent ($me, 'foaf:familyName');
        $this->assertPropertyNotPresent ($me, 'bio:olb');

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        $this->assertEquals ('sioc:UserAccount', $account->type ());
        $this->assertEquals ('Maria Musterfrau', $account->get ('sioc:name'));
        $this->assertPropertyNotPresent($account, 'sioc:creator_of');
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

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        $this->assertEquals ('sioc:UserAccount', $account->type ());
        $this->assertEquals ('Maria Musterfrau', $account->get ('sioc:name'));
        $this->assertPropertyNotPresent($account, 'sioc:creator_of');
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

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        $createdPosts = $account->allResources ('sioc:creator_of');
        $this->assertEquals (1, count($createdPosts), 'User should have 1 post');
        $createdPost = array_shift($createdPosts);
        $this->assertEquals ('http://example.com/1#it', $createdPost->getUri());
        $this->assertEquals ('sioct:BlogPost', $createdPost->type());
        $this->assertProperty($createdPost, 'dc:title', 'My first blog post');
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

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        $createdPosts = $account->allResources ('sioc:creator_of');
        $this->assertEquals (2, count($createdPosts), 'User should have 2 posts');
        $createdPost = array_shift($createdPosts);
        $this->assertEquals ('http://example.com/1#it', $createdPost->getUri());
        $this->assertEquals ('sioct:BlogPost', $createdPost->type());
        $this->assertProperty($createdPost, 'dc:title', 'My first blog post');
        $createdPost2 = array_shift($createdPosts);
        $this->assertEquals ('http://example.com/2#it', $createdPost2->getUri());
        $this->assertEquals ('sioct:BlogPost', $createdPost2->type());
        $this->assertProperty($createdPost2, 'dc:title', 'My second blog post');
    }

    public function testBuildGraphForBlogWithoutPosts () {
        \EasyRdf_Namespace::set ('sioct', 'http://rdfs.org/sioc/types#');

        $builder = new RdfBuilder();
        $graph = $builder->buildGraph (null, new \WP_Query());

        $blogUri = 'http://example.com#it';
        $it = $graph->resource ($blogUri);
        $this->assertEquals ('sioct:Weblog', $it->type ());
        $this->assertProperty($it, 'rdfs:label', 'My cool blog');
        $this->assertProperty($it, 'rdfs:comment', 'Cool description');
        $this->assertPropertyNotPresent($it, 'sioc:container_of');
    }

    public function testBuildGraphForBlogWithPosts () {
        $builder = new RdfBuilder();

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

        $graph = $builder->buildGraph (null, new \WP_Query($posts));

        $blogUri = 'http://example.com#it';
        $blog = $graph->resource ($blogUri);

        $containedPosts = $blog->allResources ('sioc:container_of');
        $this->assertEquals (2, count($containedPosts), 'Blog should have 2 posts');
        $containedPost = array_shift($containedPosts);
        $this->assertEquals ('http://example.com/1#it', $containedPost->getUri());
        $this->assertEquals ('sioct:BlogPost', $containedPost->type());
        $this->assertProperty($containedPost, 'dc:title', 'My first blog post');
        $createdPost2 = array_shift($containedPosts);
        $this->assertEquals ('http://example.com/2#it', $createdPost2->getUri());
        $this->assertEquals ('sioct:BlogPost', $createdPost2->type());
        $this->assertProperty($createdPost2, 'dc:title', 'My second blog post');

    }



}
