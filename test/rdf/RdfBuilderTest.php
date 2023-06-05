<?php

namespace org\desone\wordpress\wpLinkedData;

use PHPUnit\Framework\TestCase;

require_once 'test/mock/mock_plugin_dir_path.php';
require_once 'test/mock/WP_Query.php';
require_once 'test/mock/WP_Post.php';
require_once 'test/mock/WP_User.php';
require_once 'test/mock/service/MockedLocalWebIdService.php';
require_once(WP_LINKED_DATA_PLUGIN_DIR_PATH . 'vendor/autoload.php');
require_once 'src/rdf/RdfBuilder.php';

function get_permalink ($id) {
    return 'http://example.com/' . $id;
}

function get_userdata ($id) {
    return new \WP_User($id, 'Mario Mustermann');
}

function site_url () {
    return 'http://example.com';
}

function get_bloginfo ($show) {
    if ($show == 'name') {
        return 'My cool blog';
    }
    if ($show == 'description') return 'Cool description';
    return null;
}

class RdfBuilderTest extends TestCase {

    public function testBuildGraphForPost () {

        \EasyRdf\RdfNamespace::set ('sioct', 'http://rdfs.org/sioc/types#');

        $builder = new RdfBuilder(new MockedLocalWebIdService());
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
        self::assertEquals('sioct:BlogPost', $it->type ());
        self::assertProperty ($it, 'dc:title', 'My first blog post');
        self::assertProperty ($it, 'sioc:content', 'The posts content');
        self::assertProperty ($it, 'dc:modified', \EasyRdf\Literal\Date::parse ('2013-04-17 20:16:41'));
        self::assertProperty ($it, 'dc:created', \EasyRdf\Literal\Date::parse ('2013-03-17 19:16:41'));

        $blogResource = $graph->resource ('http://example.com#it');
        self::assertProperty ($it, 'sioc:has_container', $blogResource);

        $creator = $graph->get ($postUri, 'sioc:has_creator');
        self::assertEquals ('http://example.com/author/1#account', $creator->getUri ());
        self::assertEquals ('sioc:UserAccount', $creator->type ());
        self::assertProperty ($creator, 'sioc:name', 'Mario Mustermann');
    }

    public function testPostContentIsPublishedAsPlainText () {
        $builder = new RdfBuilder(new MockedLocalWebIdService());
        $post = new \WP_Post();

        $post->ID = 1;
        $post->post_type = 'post';
        $post->post_content = '<div cass="content">The <strong>posts</strong> content</div><img alt="foo" src="/foo.png" />';

        $graph = $builder->buildGraph ($post, null);
        $it = $graph->resource ('http://example.com/1#it');
        self::assertProperty ($it, 'sioc:content', 'The posts content');
    }

    public function testBuildGraphForUserWithoutPosts () {
        \EasyRdf\RdfNamespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder(new MockedLocalWebIdService());
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

        self::assertEquals ('foaf:Person', $me->type ());
        self::assertProperty ($me, 'foaf:name', 'Maria Musterfrau');
        self::assertProperty ($me, 'foaf:nick', 'mmuster');
        self::assertProperty ($me, 'foaf:givenName', 'Maria');
        self::assertProperty ($me, 'foaf:familyName', 'Musterfrau');
        self::assertProperty ($me, 'bio:olb', 'just me, muster');
        self::assertProperty ($me, 'foaf:account', $account);

        self::assertEquals ('sioc:UserAccount', $account->type ());
        self::assertProperty ($account, 'sioc:name', 'Maria Musterfrau');
        self::assertProperty ($account, 'sioc:account_of', $me);
        self::assertPropertyNotPresent ($account, 'sioc:creator_of');
    }

    public function testBuildGraphForUserWithCustomWebId () {

        $webIdService = self::createMock(WebIdService::class);

        $webIdService->expects (self::once ())
            ->method ('getWebIdOf')
            ->will (self::returnValue ('http://custom.webid.example#me'));

        $webIdService->expects (self::once ())
            ->method ('getAccountUri')
            ->will (self::returnValue ('http://example.com/author/2#account'));

        $builder = new RdfBuilder($webIdService);
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://custom.webid.example#me';
        $me = $graph->resource ($userUri);
        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);

        self::assertEquals ('foaf:Person', $me->type ());
        self::assertProperty ($me, 'foaf:account', $account);

        self::assertEquals ('sioc:UserAccount', $account->type ());
        self::assertProperty ($account, 'sioc:name', 'Maria Musterfrau');
        self::assertProperty ($account, 'sioc:account_of', $me);
        self::assertPropertyNotPresent ($account, 'sioc:creator_of');
    }

    public function testBuildGraphForUserWithRsaPublicKey () {
        $webIdService = self::createMock(WebIdService::class);

        $webIdService->expects (self::once ())
            ->method ('getWebIdOf')
            ->will (self::returnValue ('http://example.com/author/2#me'));

        $webIdService->expects (self::once ())
            ->method ('getAccountUri')
            ->will (self::returnValue ('http://example.com/author/2#account'));

        $webIdService->expects (self::once ())
            ->method ('getRsaPublicKey')
            ->will (self::returnValue (new RsaPublicKey('1234', 'abc123')));


        $builder = new RdfBuilder($webIdService);
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);

        $key = $me->get ('cert:key');
        self::assertNotNull ($key, 'RSA public key should be present');
        self::assertEquals ('cert:RSAPublicKey', $key->type ());
        self::assertProperty ($key, 'cert:exponent', new \EasyRdf\Literal\Integer(1234));
        self::assertProperty ($key, 'cert:modulus', new \EasyRdf\Literal\HexBinary('abc123'));
    }

    public function testBuildGraphForUserWithAdditionalRdf () {
        $webIdService = self::createMock(WebIdService::class);

        $webIdService->expects (self::once ())
            ->method ('getWebIdOf')
            ->will (self::returnValue ('http://example.com/author/2#me'));

        $webIdService->expects (self::once ())
            ->method ('getAccountUri')
            ->will (self::returnValue ('http://example.com/author/2#account'));

        setMockedMetaData (2, array(
            'additionalRdf' =>
            '@prefix foaf: <http://xmlns.com/foaf/0.1/>.' .
                '<http://example.com/author/2#me> foaf:knows <http://friends.example.com/trudy#i>.' .
                '<http://friends.example.com/trudy#i> foaf:name "Trudy".'
        ));

        $builder = new RdfBuilder($webIdService);
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        $friend = $graph->resource('http://friends.example.com/trudy#i');
        self::assertProperty ($me, 'foaf:knows', $friend);
        self::assertProperty ($friend, 'foaf:name', 'Trudy');

    }

    private function assertProperty ($subject, $predicate, $value) {
        self::assertEquals ($value, $subject->get ($predicate));
    }

    public function testBuildGraphForUserWithoutPostsAndData () {
        \EasyRdf\RdfNamespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder(new MockedLocalWebIdService());
        $user = new \WP_User(
            2, 'Maria Musterfrau'
        );
        $graph = $builder->buildGraph ($user, new \WP_Query());

        $userUri = 'http://example.com/author/2#me';
        $me = $graph->resource ($userUri);
        self::assertEquals ('foaf:Person', $me->type ());
        self::assertProperty ($me, 'foaf:name', 'Maria Musterfrau');
        self::assertPropertyNotPresent ($me, 'foaf:nick');
        self::assertPropertyNotPresent ($me, 'foaf:givenName');
        self::assertPropertyNotPresent ($me, 'foaf:familyName');
        self::assertPropertyNotPresent ($me, 'bio:olb');

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        self::assertEquals ('sioc:UserAccount', $account->type ());
        self::assertEquals ('Maria Musterfrau', $account->get ('sioc:name'));
        self::assertPropertyNotPresent ($account, 'sioc:creator_of');
    }

    private function assertPropertyNotPresent ($me, $predicate) {
        self::assertNull ($me->get ($predicate), 'No ' . $predicate . ' should be present');
    }

    public function testBuildGraphForUserWithoutPostsAndEmptyData () {
        \EasyRdf\RdfNamespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder(new MockedLocalWebIdService());
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
        self::assertEquals ('foaf:Person', $me->type ());
        self::assertEquals ('Maria Musterfrau', $me->get ('foaf:name'));
        self::assertPropertyNotPresent ($me, 'foaf:nick');
        self::assertPropertyNotPresent ($me, 'foaf:givenName');
        self::assertPropertyNotPresent ($me, 'foaf:familyName');
        self::assertPropertyNotPresent ($me, 'bio:olb');

        $accountUri = 'http://example.com/author/2#account';
        $account = $graph->resource ($accountUri);
        self::assertEquals ('sioc:UserAccount', $account->type ());
        self::assertEquals ('Maria Musterfrau', $account->get ('sioc:name'));
        self::assertPropertyNotPresent ($account, 'sioc:creator_of');
    }

    public function testBuildGraphForUserWithOnePost () {
        \EasyRdf\RdfNamespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder(new MockedLocalWebIdService());
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
        self::assertEquals (1, count ($createdPosts), 'User should have 1 post');
        $createdPost = array_shift ($createdPosts);
        self::assertEquals ('http://example.com/1#it', $createdPost->getUri ());
        self::assertEquals ('sioct:BlogPost', $createdPost->type ());
        self::assertProperty ($createdPost, 'dc:title', 'My first blog post');
    }

    public function testBuildGraphForUserWithMultiplePosts () {
        \EasyRdf\RdfNamespace::set ('bio', 'http://purl.org/vocab/bio/0.1/');
        $builder = new RdfBuilder(new MockedLocalWebIdService());
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
        self::assertEquals (2, count ($createdPosts), 'User should have 2 posts');
        $createdPost = array_shift ($createdPosts);
        self::assertEquals ('http://example.com/1#it', $createdPost->getUri ());
        self::assertEquals ('sioct:BlogPost', $createdPost->type ());
        self::assertProperty ($createdPost, 'dc:title', 'My first blog post');
        $createdPost2 = array_shift ($createdPosts);
        self::assertEquals ('http://example.com/2#it', $createdPost2->getUri ());
        self::assertEquals ('sioct:BlogPost', $createdPost2->type ());
        self::assertProperty ($createdPost2, 'dc:title', 'My second blog post');
    }

    public function testBuildGraphForBlogWithoutPosts () {
        \EasyRdf\RdfNamespace::set ('sioct', 'http://rdfs.org/sioc/types#');

        $builder = new RdfBuilder(new MockedLocalWebIdService());
        $graph = $builder->buildGraph (null, new \WP_Query());

        $blogUri = 'http://example.com#it';
        $it = $graph->resource ($blogUri);
        self::assertEquals ('sioct:Weblog', $it->type ());
        self::assertProperty ($it, 'rdfs:label', 'My cool blog');
        $homepage = $graph->get ($it, 'foaf:homepage');
        self::assertEquals ('http://example.com', $homepage->getUri ());
        self::assertProperty ($it, 'rdfs:comment', 'Cool description');
        self::assertPropertyNotPresent ($it, 'sioc:container_of');
    }

    public function testBuildGraphForBlogWithPosts () {
        $builder = new RdfBuilder(new MockedLocalWebIdService());

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
        self::assertCount(2, $containedPosts, 'Blog should have 2 posts');
        $containedPost = array_shift ($containedPosts);
        self::assertEquals ('http://example.com/1#it', $containedPost->getUri ());
        self::assertEquals ('sioct:BlogPost', $containedPost->type ());
        self::assertProperty ($containedPost, 'dc:title', 'My first blog post');
        $createdPost2 = array_shift ($containedPosts);
        self::assertEquals ('http://example.com/2#it', $createdPost2->getUri ());
        self::assertEquals ('sioct:BlogPost', $createdPost2->type ());
        self::assertProperty ($createdPost2, 'dc:title', 'My second blog post');

    }


}
