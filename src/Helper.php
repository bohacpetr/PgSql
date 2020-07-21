<?php

/**
 * @author Petr Boháč <bohacpetr@bohyn.cz>
 */

namespace bohyn\PgSql;

use DateInterval;
use DateTime;
use libs\hyper\database\type\DateRange;
use libs\hyper\database\type\DateTimeRange;
use libs\hyper\database\type\Dimensions;
use libs\hyper\database\type\IntegerRange;
use libs\hyper\database\type\Point;
use stdClass;

class Helper
{

    public static function escapeTableName(PgSqlConnection $pgsql, $table)
    {
        $tableNameParts = [];

        foreach (explode('.', $table) as $ident) {
            $tableNameParts[] = $pgsql->escapeIdentifier($ident);
        }

        return implode('.', $tableNameParts);
    }

    public static function decodeRow($row, array $fieldTypes)
    {
        foreach ($row as $name => &$value) {
            $type = isset($fieldTypes[$name]) ? $fieldTypes[$name] : null;
            $value = self::decodeValue($value, $type);
        }

        return $row;
    }

    public static function decodeValue($value, $type)
    {
        if ($value === null) {
            return null;
        }

        switch ($type) {
            case 'bool':
                return $value == 't' ? true : false;
            case 'int2':
            case 'int4':
            case 'int8':
                return (int)$value;
            case '_int2':
            case '_int4':
            case '_int8':
                $value = trim($value, '{}');

                if ($value === '') {
                    return [];
                }

                $arr = explode(',', $value);

                return array_map(
                    function ($v) {
                        return (int)$v;
                    },
                    $arr
                );
            case 'date':
            case 'timestamp':
                return new DateTime($value);
            case 'json':
            case 'jsonb':
                return json_decode($value);
            case 'tsrange':
                return DateTimeRange::fromPgToPhp($value);
            case 'daterange':
                return DateRange::fromPgToPhp($value);
            case 'point':
                return Point::fromPgToPhp($value);
            case 'dimensions':
                return Dimensions::fromPgToPhp($value);
            case 'int2range':
            case 'int4range':
            case 'int8range':
                return IntegerRange::fromPgToPhp($value);
            case 'interval':
                preg_match(
                    '~^(?:(?P<y>[-0-9]+)? years?\s?)?(?:(?P<m>[-0-9]+)? mons?\s?)?(?:(?P<d>[-0-9]+)? days?\s?)?(?:(?P<h>[-0-9]{1,3})?:(?P<i>[-0-9]{1,3})?:(?P<s>[-0-9]{1,3})?)?$~',
                    $value,
                    $matches
                );
                $matches = array_merge(['y' => 0, 'm' => 0, 'd' => 0, 'h' => 0, 'i' => 0, 's' => 0], $matches);
                $format = sprintf(
                    'P%dY%dM%dDT%dH%dM%dS',
                    $matches['y'],
                    $matches['m'],
                    $matches['d'],
                    $matches['h'],
                    $matches['i'],
                    $matches['s']
                );

                return new DateInterval($format);
        }

        return $value;
    }

    public static function encodeArray(PgSqlConnection $pgsql, array $values)
    {
        foreach ($values as &$value) {
            $value = self::encodeArrayItem($pgsql, $value);
        }

        return 'ARRAY[' . implode(',', $values) . ']';
    }

    public static function encodeArrayItem(PgSqlConnection $pgsql, $value)
    {
        switch (gettype($value)) {
            case 'integer':
            case 'float':
                return $value;
            case 'NULL':
                return 'NULL';
            case 'string':
                return $pgsql->escapeLiteral($value);
            case 'boolean':
                return $value ? 'TRUE' : 'FALSE';
            case 'array':
                return self::encodeArray($pgsql, $value);
            default:
                $type = gettype($value) === 'object' ? get_class($value) : gettype($value);
                throw new PgSqlException(sprintf('Unsupported data type %s', $type));
        }
    }

    public static function encodeParams(PgSqlConnection $pgsql, array $values)
    {
        foreach ($values as &$value) {
            $value = self::encodeParam($pgsql, $value);
        }

        return $values;
    }

    public static function encodeParam(PgSqlConnection $pgsql, $value, $quotes = false)
    {
        switch (gettype($value)) {
            case 'NULL':
            case 'integer':
            case 'float':
            case 'double':
                return $value;
            case 'string':
                return ($quotes ? "'" : '') . $value . ($quotes ? "'" : '');
            case 'boolean':
                return $value ? 'TRUE' : 'FALSE';
            case 'array':
                foreach ($value as &$item) {
                    $item = self::encodeParamArrayItem($pgsql, $item);
                }

                return ($quotes ? "'" : '') . '{' . implode(',', $value) . '}' . ($quotes ? "'" : '');
            case 'object':
                if (method_exists($value, '__toString')) {
                    return ($quotes ? "'" : '') . (string)$value . ($quotes ? "'" : '');
                }

                if ($value instanceof DateTime) {
                    return ($quotes ? "'" : '') . $value->format('Y-m-d H:i:se') . ($quotes ? "'" : '');
                }

                if ($value instanceof DateInterval) {
                    $format = ($quotes ? "'" : '') . '%r%y year %r%m month %r%d day %r%h hour %r%i minute %r%s second' . ($quotes ? "'" : '');

                    return ($quotes ? "'" : '') . $value->format($format) . ($quotes ? "'" : '');
                }

                if ($value instanceof stdClass) {
                    return json_encode($value, JSON_UNESCAPED_UNICODE);
                }
        }

        $type = gettype($value) === 'object' ? get_class($value) : gettype($value);

        throw new PgSqlException(sprintf('Unsupported data type %s', $type));
    }

    public static function encodeParamArrayItem(PgSqlConnection $pgsql, $value)
    {
        switch (gettype($value)) {
            case 'integer':
            case 'float':
            case 'NULL':
                return $value;
            case 'string':
                return '"' . str_replace('""', '\"', substr($pgsql->escapeIdentifier($value), 1, -1)) . '"';
            case 'boolean':
                return $value ? 'TRUE' : 'FALSE';
            case 'array':
                foreach ($value as &$item) {
                    $item = self::encodeParamArrayItem($pgsql, $item);
                }

                return '{' . implode(',', $value) . '}';
            default:
                $type = gettype($value) === 'object' ? get_class($value) : gettype($value);
                throw new PgSqlException(sprintf('Unsupported data type %s', $type));
        }
    }
}
