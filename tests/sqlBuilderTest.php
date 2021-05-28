<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Rum\Builder;
use Rum\BuilderHelper;

//加载方法集
require_once(dirname(dirname(__FILE__)) . '/src/helpers.php');

/**
 * RumSqlBuilder Unit Test
 */
class sqlBuilderTest extends TestCase
{
    // 查询
    public function testQuery()
    {
        $opts = [
            'id' => BuilderHelper::where('id', '='),
            'ids' => BuilderHelper::whereIn('id'),
        ];
        $params = [
            'id' => 2,
            'ids' => [2, 3]
        ];

        $tmpb = Builder::factory()->from('test')->select(['id', 'name']);
        $tmpb = BuilderHelper::prepare($tmpb, $opts, $params);

        $sqlStr = $tmpb->toSql();
        $data = $tmpb->getBindings();

        self::assertEquals('select `id`, `name` from `test` where `id` = ? and `id` in (?, ?)', $sqlStr);
        self::assertEquals($data[0], 2);
        self::assertEquals($data[1], 2);
        self::assertEquals($data[2], 3);
    }

    // 更新数据
    public function testUpdate()
    {
        $opts = [
            'id' => BuilderHelper::where('id', '='),
        ];
        $params = [
            'id' => 13,
        ];
        $vals = [
            'stat' => 1
        ];
        $tmpb = Builder::factory()->from('test');
        $sto = BuilderHelper::prepare($tmpb, $opts, $params)->update($vals);

        $sqlStr = $sto['sql'];
        $data = $sto['params'];

        self::assertEquals('update `test` set `stat` = ? where `id` = ?', $sqlStr);
        self::assertEquals($data[0], 1);
        self::assertEquals($data[1], 13);
    }

    // 插入数据
    public function testInsert()
    {
        $vals = [
            'name' => 'test',
            'stat' => 1
        ];
        $sto = Builder::factory()->from('test')->insert($vals);

        $sqlStr = $sto['sql'];
        $data = $sto['params'];

        self::assertEquals('insert into `test` (`name`, `stat`) values (?, ?)', $sqlStr);
        self::assertEquals($data[0], 'test');
        self::assertEquals($data[1], 1);
    }
}
