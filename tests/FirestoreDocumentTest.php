<?php

namespace MrShan0\PHPFirestore\Tests;

use MrShan0\PHPFirestore\FirestoreDocument;
use PHPUnit\Framework\TestCase;

class FirestoreDocumentTest extends TestCase
{
    /** @test */
    public function get_absolute_name_returns_name()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
            ]
        );

        $this->assertSame('projects/database/(default)/documents/collection/item', $document->getAbsoluteName());
    }


    /** @test */
    public function get_absolute_name_returns_null_if_no_document_provided()
    {
        $document = new FirestoreDocument();

        $this->assertNull($document->getAbsoluteName());
    }


    /** @test */
    public function get_created_time_returns_created_time()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
                'createTime' => '2018-12-24T15:00:00.123456Z',
                'updateTime' => '2018-12-24T16:00:00.123456Z',
                'fields' => [
                    'myKey' => [
                        'stringValue' => 'my value'
                    ]
                ],
            ]
        );

        $this->assertEquals(new \DateTime('2018-12-24T15:00:00.123456Z'), $document->getCreatedTime());
    }


    /** @test */
    public function get_updated_time_returns_updated_time()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
                'createTime' => '2018-12-24T15:00:00.123456Z',
                'updateTime' => '2018-12-24T16:00:00.123456Z',
                'fields' => [
                    'myKey' => [
                        'stringValue' => 'my value'
                    ]
                ],
            ]
        );

        $this->assertEquals(new \DateTime('2018-12-24T16:00:00.123456Z'), $document->getUpdatedTime());
    }


    /** @test */
    public function get_created_time_returns_null_if_document_does_not_exist()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
            ]
        );

        $this->assertNull($document->getCreatedTime());
    }


    /** @test */
    public function get_updated_time_returns_null_if_document_does_not_exist()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
            ]
        );

        $this->assertNull($document->getUpdatedTime());
    }


    /** @test */
    public function get_name_returns_name()
    {
        $document = new FirestoreDocument(
            [
                'name' => 'projects/database/(default)/documents/collection/item',
            ]
        );

        $this->assertSame('projects/database/(default)/documents/collection/item', $document->getName());
    }


    /** @test */
    public function get_name_returns_null_if_no_document_provided()
    {
        $document = new FirestoreDocument();

        $this->assertNull($document->getName());
    }
}
