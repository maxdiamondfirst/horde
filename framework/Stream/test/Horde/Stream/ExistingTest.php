<?php
/**
 * @category   Horde
 * @package    Stream
 * @subpackage UnitTests
 */

/**
 * @category   Horde
 * @package    Stream
 * @subpackage UnitTests
 */
class Horde_Stream_ExistingTest extends Horde_Test_Case
{
    private $fd;

    public function setUp()
    {
        $this->fd = fopen(dirname(__FILE__) . '/fixtures/data.txt', 'r');
        $this->stream = new Horde_Stream_Existing(array('stream' => $this->fd));
    }

    public function testFgetToChar()
    {
        $this->stream->rewind();

        $this->assertEquals(
            'A',
            $this->stream->getToChar(' ')
        );
        $this->assertEquals(
            'B',
            $this->stream->getToChar(' ')
        );
        $this->assertEquals(
            '',
            $this->stream->getToChar(' ')
        );
    }

    public function testLength()
    {
        $this->stream->rewind();

        $this->assertEquals(
            3,
            $this->stream->length()
        );
        $this->assertEquals(
            'A',
            $this->stream->getChar()
        );
    }

    public function testGetString()
    {
        $this->stream->rewind();

        $this->assertEquals(
            'A B',
            $this->stream->getString()
        );
        $this->assertEquals(
            'A B',
            $this->stream->getString(0)
        );

        $this->stream->rewind();

        $this->assertEquals(
            'A B',
            $this->stream->getString()
        );
        $this->assertEquals(
            'A B',
            $this->stream->getString(0)
        );

        $this->stream->seek(2, false);
        $this->assertEquals(
            'B',
            $this->stream->getString()
        );

        $this->stream->seek(2, false);
        $this->assertEquals(
            'A ',
            $this->stream->getString(0, -1)
        );

        $this->stream->end();
        $this->assertEquals(
            '',
            $this->stream->getString(null, -1)
        );
    }

    public function testPeek()
    {
        $this->stream->rewind();

        $this->assertEquals(
            'A',
            $this->stream->peek()
        );
        $this->assertEquals(
            'A',
            $this->stream->getChar()
        );

        $this->stream->end(-1);

        $this->assertEquals(
            'B',
            $this->stream->peek()
        );
        $this->assertEquals(
            'B',
            $this->stream->getChar()
        );
    }

    public function testSearch()
    {
        $this->stream->rewind();

        $this->assertEquals(
            2,
            $this->stream->search('B')
        );
        $this->assertEquals(
            0,
            $this->stream->search('A')
        );
        $this->assertEquals(
            0,
            $this->stream->pos()
        );

        $this->assertEquals(
            2,
            $this->stream->search('B', false, false)
        );
        $this->assertNull($this->stream->search('A', false, false));

        $this->assertEquals(
            0,
            $this->stream->search('A', true)
        );
        $this->assertEquals(
            2,
            $this->stream->pos()
        );

        $this->assertEquals(
            0,
            $this->stream->search('A', true, false)
        );
        $this->assertEquals(
            0,
            $this->stream->pos()
        );
    }

    public function testAddMethod()
    {
    }

    public function testStringRepresentation()
    {
        $this->assertEquals(
            'A B',
            strval($this->stream)
        );
    }

    public function testSerializing()
    {
        $stream2 = unserialize(serialize($this->stream));

        $this->assertEquals(
            'A B',
            strval($stream2)
        );
    }

    public function testClone()
    {
        $stream2 = clone $this->stream;

        $this->stream->close();

        $this->assertEquals(
            'A B',
            strval($stream2)
        );
    }

}
