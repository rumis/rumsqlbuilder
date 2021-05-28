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
        // 一般查询
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

        // Like查询
        $opts1 = [
            'stat' => BuilderHelper::where('stat', '='),
            'name' => BuilderHelper::like('name'),
        ];
        $params1 = [
            'stat' => 1,
            'name' => 'murong'
        ];

        $tmpb = Builder::factory()->from('test')->select(['id', 'name']);
        $tmpb = BuilderHelper::prepare($tmpb, $opts1, $params1)->offset(2)->limit(10)->orderByDesc('id');

        $sqlStr = $tmpb->toSql();
        $data = $tmpb->getBindings();

        self::assertEquals('select `id`, `name` from `test` where `stat` = ? and `name` LIKE ? order by `id` desc limit 10 offset 2', $sqlStr);
        self::assertEquals($data[0], 1);
        self::assertEquals($data[1], '%murong%');

        // 条件子句 - 连表
        $opts2 = [
            'stat' => BuilderHelper::where('test.stat', '='),
            'name' => function (Builder $b, $v, $o) {
                return $b->where(function (Builder $b1) use ($v) {
                    return $b1->where('test.name', 'like', '%' . $v . '%')->orWhere('test.name', 'like', '%murong%');
                });
            }
        ];
        $params2 = [
            'stat' => 1,
            'name' => 'xx'
        ];
        $tmpb = Builder::factory()->from('test')->select(array_merge(
            BuilderHelper::serialFields(['id', 'name', 'group_id'], 'test'),
            BuilderHelper::serialFields(['group_name'], 'group')
        ))->leftJoin('group', 'test.group_id', '=', 'group.id')->groupBy('test.group_id');
        $tmpb = BuilderHelper::prepare($tmpb, $opts2, $params2);

        $sqlStr = $tmpb->toSql();
        $data = $tmpb->getBindings();

        self::assertEquals('select `test`.`id`, `test`.`name`, `test`.`group_id`, `group`.`group_name` from `test` left join `group` on `test`.`group_id` = `group`.`id` where `test`.`stat` = ? and (`test`.`name` like ? or `test`.`name` like ?) group by `test`.`group_id`', $sqlStr);
        self::assertEquals($data[0], 1);
        self::assertEquals($data[1], '%xx%');
        self::assertEquals($data[2], '%murong%');
    }

    // 更新数据
    public function testUpdate()
    {
        // 更新
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
        // 插入单条
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

        // 插入多条
        $vals2 = [
            [
                'name' => 'test',
                'stat' => 1
            ],
            [
                'name' => 'test2',
                'stat' => 2
            ]
        ];
        $sto = Builder::factory()->from('test1')->insert($vals2);

        $sqlStr = $sto['sql'];
        $data = $sto['params'];

        self::assertEquals('insert into `test1` (`name`, `stat`) values (?, ?), (?, ?)', $sqlStr);
        self::assertEquals($data[0], 'test');
        self::assertEquals($data[1], 1);
        self::assertEquals($data[2], 'test2');
        self::assertEquals($data[3], 2);
    }

    // 函数
    public function testFunc()
    {
        // 查询记录数量
        // 参数字段名和表字段不一致
        $opts = [
            'stat1' => BuilderHelper::where('stat', '='),
        ];
        $params = [
            'stat1' => 1,
        ];

        $tmpb = Builder::factory()->from('test');
        $tmpb = BuilderHelper::prepare($tmpb, $opts, $params)->count('id');

        $sqlStr = $tmpb->toSql();
        $data = $tmpb->getBindings();

        self::assertEquals('select count(`id`) as aggregate from `test` where `stat` = ?', $sqlStr);
        self::assertEquals($data[0], 1);

        // 函数结果作为字段返回
        $opts1 = [
            'stat' => BuilderHelper::where('stat', '='),
        ];
        $params1 = [
            'stat' => 11,
        ];

        $tmpb = Builder::factory()->from('test')->select('group_id', 'group_name', 'count(group_id) as total');
        $tmpb = BuilderHelper::prepare($tmpb, $opts1, $params1)->groupBy('group_id');

        $sqlStr = $tmpb->toSql();
        $data = $tmpb->getBindings();

        self::assertEquals('select `group_id`, `group_name`, `count(group_id)` as `total` from `test` where `stat` = ? group by `group_id`', $sqlStr);
        self::assertEquals($data[0], 11);
    }
}
