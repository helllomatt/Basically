<?php

namespace Basically;

use Database\DB;

class CRUDTest extends \PHPUnit_Framework_TestCase {
    private static $table = 'sometable';
    private static $db = null;

    public static function setUpBeforeClass() {
        static::$db = (new DB)->connect(database_host, database_username, database_password, database_name);
        static::$db->query('verbatim')
            ->sql('CREATE TABLE `'.static::$table.'` (`id` int(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY(`id`), `email` varchar(250) NOT NULL, `password` varchar(250) NOT NULL, `firstname` varchar(50) NOT NULL, `lastname` varchar(50) NOT NULL, `activated` tinyint(1) NOT NULL, `created` date NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=latin1;')
            ->execute();
    }

    public static function tearDownAfterClass() {
        static::$db->query('verbatim')->sql('DROP TABLE '.static::$table)->execute();
    }

    public function testDefaultSanitation() {
        $this->assertEquals('string', CRUD::sanitize('string'));
    }

    public function testEmail() {
        $email = 'john@smith.com';
        $this->assertEquals($email, CRUD::sanitize($email, ['email']));
    }

    public function testBadEmail() {
        $this->expectException('\Exception');
        CRUD::sanitize('string', ['email']);
    }

    public function testString() {
        $this->assertEquals('string', CRUD::sanitize('string', ['string']));
    }

    public function testBadRequiredString() {
        $this->expectException('\Exception');
        CRUD::sanitize('', ['string', 'required']);
    }

    public function testStringNoTags() {
        $this->assertEquals('string', CRUD::sanitize('<b>string</b>', ['string', 'notags']));
    }

    public function testStringAlphaNumeric() {
        $this->assertEquals('string123', CRUD::sanitize('string123', ['string', 'match' => 'a-z0-9']));
    }

    public function testStringAlphaNumericWithExceptions() {
        $this->assertEquals('string_', CRUD::sanitize('string_', ['string', 'match' => 'a-z0-9_']));
    }

    public function testBadStringAlphaNumericWithExceptions() {
        $this->expectException('\Exception');
        CRUD::sanitize('string_-', ['string', 'match' => 'a-z0-9_']);
    }

    public function testStringBadAlphaNumeric() {
        $this->expectException('\Exception');
        CRUD::sanitize('string123<asdf>', ['string', 'match' => 'a-z0-9']);
    }

    public function testBadString() {
        $this->expectException('\Exception');
        CRUD::sanitize(1, ['string']);
    }

    public function testStrLen() {
        $against = ['string', 'strlen' => ['short' => 1, 'long' => 10]];
        $this->assertEquals('string', CRUD::sanitize('string', $against));
    }

    public function testSmallStrLen() {
        $this->expectException('\Exception');
        CRUD::sanitize('s', ['string', 'strlen' => ['short' => 2]]);
    }

    public function testBigStrLen() {
        $this->expectException('\Exception');
        CRUD::sanitize('string', ['string', 'strlen' => ['long' => 4]]);
    }

    public function testNumber() {
        $this->assertEquals(1, CRUD::sanitize(1, ['number']));
    }

    public function testBadNumber() {
        $this->expectException('\Exception');
        CRUD::sanitize('string', ['number']);
    }

    public function testDate() {
        $this->assertEquals('2017-01-01', CRUD::sanitize('2017-01-01', ['date']));
    }

    public function testFormattedDate() {
        $against = ['date', 'date_format' => 'm/d/Y'];
        $this->assertEquals('01/01/2017', CRUD::sanitize('2017-01-01', $against));
    }

    public function testBadDate() {
        $this->expectException('\Exception');
        CRUD::sanitize('hi', ['date']);
    }

    public function testXSS() {
        $this->assertEquals('&lt;script&gt;', CRUD::sanitize('<script>', ['xss']));
    }

    public function testBadXss() {
        $this->assertEquals('<script>', CRUD::sanitize('<script>'));
    }

    public function testName() {
        $this->assertEquals(['first' => 'john', 'last' => ''], CRUD::sanitize('john', ['name']));
        $this->assertEquals(['first' => 'john', 'last' => 'smith'], CRUD::sanitize('john smith', ['name']));
        $this->assertEquals(['first' => 'john james', 'last' => 'smith'], CRUD::sanitize('john james smith', ['name']));
        $this->assertEquals(['first' => 'john', 'last' => ''], CRUD::sanitize('john ', ['name']));
        $this->assertEquals(['first' => 'john', 'last' => ''], CRUD::sanitize('<b>john</b>', ['name']));
        $this->assertEquals(['first' => 'john\'lemore', 'last' => ''], CRUD::sanitize('john\'lemore', ['name']));
    }

    public function testBadFullName() {
        $this->expectException('\Exception');
        CRUD::sanitize('John', ['name', 'required-full']);
    }

    public function testCompilingData() {
        $this->assertEquals(['columns' => ['key'], 'values' => ['val']], CRUD::compile(['key' => 'val']));
    }

    public function testCreating() {
        $name = CRUD::sanitize('John Smith', ['name', 'required-full']);
        $id = CRUD::insert(static::$db, static::$table, CRUD::compile([
            'email'     => CRUD::sanitize('john@smith.com', ['email', 'required']),
            'password'  => CRUD::sanitize('hunter2', ['string', 'strlen' => ['short' => 2, 'long' => 50], 'password', 'required']),
            'firstname' => $name['first'],
            'lastname'  => $name['last'],
            'activated' => CRUD::sanitize(false, ['boolean', 'required']),
            'created'   => ['now()']
        ]));
        $this->assertEquals(1, $id);
    }

    public function testBadCreating() {
        $this->expectException('\Exception');
        CRUD::insert(static::$db, '', []);
    }

    public function testBadTableCreating() {
        $this->expectException('\Exception');
        CRUD::insert(static::$db, 'notable', []);
    }

    public function testUpdating() {
        $update = CRUD::update(static::$db, static::$table, CRUD::compile([
            'activated' => CRUD::sanitize(true, ['boolean', 'required'])
        ]), [
            'expression' => 'id = :id',
            'data'       => [':id' => 1]
        ]);

        $this->assertTrue($update);
    }

    public function testBadUpdate() {
        $this->expectException('\Exception');
        CRUD::update(static::$db, 'notable', [], []);
    }

    public function testBadUpdateTable() {
        $this->expectException('\Exception');
        CRUD::update(static::$db, 'notable', CRUD::compile([
            'activated' => CRUD::sanitize(true, ['boolean', 'required'])
        ]), [
            'expression' => 'id = :id',
            'data'       => [':id' => 1]
        ]);
    }

    public function testDelete() {
        $delete = CRUD::delete(static::$db, static::$table, [
            'expression' => 'id = :id',
            'data'       => [':id' => 1]
        ]);

        $this->assertTrue($delete);
    }

    public function testBadDelete() {
        $this->expectException('\Exception');
        CRUD::delete(static::$db, 'notable', []);
    }

    public function testBadDeleteTable() {
        $this->expectException('\Exception');
        CRUD::delete(static::$db, 'notable', [
            'expression' => 'id = :id',
            'data'       => [':id' => 1]
        ]);
    }
}
