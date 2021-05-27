<?php

namespace Rum;

class BuilderHelper
{
    /**
     * 根据规则构建SQL语句
     * @author: liumurong  <liumurong1@100tal.com>
     * @date: 2020-08-13 16:19:03
     */
    public static function prepare(Builder &$builder, $rules, $items): Builder
    {
        foreach ($rules as $k => $v) {
            if (!isset($items[$k])) {
                continue;
            }
            $builder = $v($builder, $items[$k], $items);
        }
        return $builder;
    }

    /**
     * 简单where条件
     * LIKE等需要特殊处理
     * @date: 2020-08-14 20:20:16
     */
    public static function where($column, $operate)
    {
        return function (Builder $b, $v) use ($column, $operate) {
            return $b->where($column, $operate, $v);
        };
    }

    /**
     * 简单whereIn条件
     * @date: 2020-08-14 20:21:17
     */
    public static function whereIn($column)
    {
        return function (Builder $b, $v) use ($column) {
            return $b->whereIn($column, $v);
        };
    }

    /**
     * 简单orWhere条件
     * @date: 2020-08-14 20:20:16
     */
    public static function orWhere($column, $operate)
    {
        return function (Builder $b, $v) use ($column, $operate) {
            return $b->orWhere($column, $operate, $v);
        };
    }

    /**
     * 简单orWhereIn条件
     * @date: 2020-08-14 20:21:17
     */
    public static function orWhereIn($column)
    {
        return function (Builder $b, $v) use ($column) {
            return $b->orWhereIn($column, $v);
        };
    }

    /**
     * 前后模糊查询
     * @date: 2020-08-20 21:04:35
     */
    public static function like($column)
    {
        return function (Builder $b, $v) use ($column) {
            return $b->where($column, 'LIKE', "%{$v}%");
        };
    }

    /**
     * 左侧模糊查询
     * @date: 2020-08-20 21:04:35
     */
    public static function leftLike($column)
    {
        return function (Builder $b, $v) use ($column) {
            return $b->where($column, 'LIKE', "%{$v}");
        };
    }

    /**
     * sql语句中的所有参数位替换为实际的参数
     * ES使用
     * @date: 2020-10-27 11:32:33
     */
    public static function mergeQueryParams($sql, $params)
    {
        foreach ($params as $k => $v) {
            $sql = str_replace(':' . $k, is_numeric($v) ? $v : '\'' . $v . '\'', $sql);
        }
        return $sql;
    }
    /**
     * 连表查询时字段上拼接表名
     * 连表查询时字段上拼接表明
     * @author: liumurong  <liumurong1@100tal.com>
     * @date: 2020-11-10 14:43:25
     */
    public static function serialFields($fields, $tableName)
    {
        return array_map(function ($v) use ($tableName) {
            return $tableName . '.' . $v;
        }, $fields);
    }
}
