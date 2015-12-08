<?php

class ContentReviewTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \WP_Mock::setUp();
    }

    public function tearDown()
    {
        \WP_Mock::tearDown();
    }

    public function testSetupMetaBoxesPost()
    {
        \WP_Mock::wpFunction('add_meta_box', [
            'args' => [
                'dxw-content',
                'Content Review',
                ['\\Dxw_Content_Review\\Dxw_Content_Review', 'render_meta_box'],
                'post',
                'side',
                'core',
            ],
            'times' => 1,
        ]);

        \Dxw_Content_Review\Dxw_Content_Review::setup_meta_boxes('post');
    }

    public function testSetupMetaBoxesPage()
    {
        \WP_Mock::wpFunction('add_meta_box', [
            'args' => [
                'dxw-content',
                'Content Review',
                ['\\Dxw_Content_Review\\Dxw_Content_Review', 'render_meta_box'],
                'page',
                'side',
                'core',
            ],
            'times' => 1,
        ]);

        \Dxw_Content_Review\Dxw_Content_Review::setup_meta_boxes('page');
    }

    public function testSetupMetaBoxesCustomPostType()
    {
        \WP_Mock::wpFunction('add_meta_box', [
            'args' => [
                'dxw-content',
                'Content Review',
                ['\\Dxw_Content_Review\\Dxw_Content_Review', 'render_meta_box'],
                'meow',
                'side',
                'core',
            ],
            'times' => 1,
        ]);

        \WP_Mock::onFilter('dxw_content_review_post_types')
        ->with(['post', 'page'])
        ->reply(['meow']);

        \Dxw_Content_Review\Dxw_Content_Review::setup_meta_boxes('meow');
    }

    public function testSetupMetaBoxesNotPostOrPage()
    {
        \WP_Mock::wpFunction('add_meta_box', [
            'times' => 0,
        ]);

        \WP_Mock::onFilter('dxw_content_review_post_types')
        ->with(['post', 'page'])
        ->reply(['meow']);

        \Dxw_Content_Review\Dxw_Content_Review::setup_meta_boxes('post');
        \Dxw_Content_Review\Dxw_Content_Review::setup_meta_boxes('page');
    }
}
