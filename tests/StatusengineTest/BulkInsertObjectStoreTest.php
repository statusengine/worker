<?php
/**
 * Statusengine Worker
 * Copyright (C) 2016  Daniel Ziegler
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Statusengine\Test;


use Statusengine\BulkInsertObjectStore;


class BulkInsertObjectStoreTest extends \PHPUnit_Framework_TestCase {

    public function testInstanceOf(){
        $ObjectStore = new BulkInsertObjectStore(5, 10);
        $this->assertInstanceOf('\Statusengine\BulkInsertObjectStore', $ObjectStore);
    }

    public function testGetStoredObjectType(){
        $ObjectStore = new BulkInsertObjectStore(5, 10);
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject([]);
        $this->assertEquals('stdClass', $ObjectStore->getStoredType());
    }

    public function testExceptionsMaxDelay(){
        $this->setExpectedException('\Statusengine\Exception\InvalidArgumentException');
        $ObjectStore = new BulkInsertObjectStore('abc', 2);
    }

    public function testExceptionsMaxObjects(){
        $this->setExpectedException('\Statusengine\Exception\InvalidArgumentException');
        $ObjectStore = new BulkInsertObjectStore('1', 'xyz');
    }

    public function testUnknownTypeExceptions(){
        $ObjectStore = new BulkInsertObjectStore(5, 10);
        $this->setExpectedException('\Statusengine\Exception\UnknownTypeException');
        $ObjectStore->getStoredType();
    }


    public function testStoreAndGetObjectsAndResetCache(){
        $ObjectStore = new BulkInsertObjectStore(5, 10);
        $ObjectStore->addObject($this->getObject());
        $this->assertEquals(1, $ObjectStore->getObjectCount());
        $ObjectStore->addObject($this->getObject());
        $this->assertEquals(2, $ObjectStore->getObjectCount());

        $allObjects= [
            $this->getObject(),
            $this->getObject()
        ];
        $this->assertEquals($allObjects, $ObjectStore->getObjects());
        $this->assertEquals(2, $ObjectStore->getObjectCount());

        $ObjectStore->reset();
        $this->assertEquals(0, $ObjectStore->getObjectCount());
        $this->assertEquals([], $ObjectStore->getObjects());
    }

    public function testHasRaisedTimeout(){
        $ObjectStore = new BulkInsertObjectStore(2,5);
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $this->assertFalse($ObjectStore->hasRaisedTimeout());
        sleep(3);
        $this->assertTrue($ObjectStore->hasRaisedTimeout());
        $ObjectStore->reset();

        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $ObjectStore->addObject($this->getObject());
        $this->assertTrue($ObjectStore->hasRaisedTimeout());
    }

    public function getObject(){
        $object = new \stdClass();
        $object->foo = 'foo';
        $object->bar = 'bar';
        return $object;
    }

}
