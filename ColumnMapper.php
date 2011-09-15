<?php
namespace Tatami\Components\Datagrid;

/**
 * Description of ColumnMapper
 *
 * @author Martin
 */
class ColumnMapper
{
    private static $map = array(
      'integer' => 'IntegerColumn',
      'float' => 'FloatColumn',
      'string' => 'TextColumn',
      'text' => 'TextColumn',
      'array' => 'StandardColumn',
      'datetime' => 'DatetimeColumn',
      'timestamp' => 'DatetimeColumn',
      'image' => 'ImageColumn',
      'bool' => 'BoolColumn'
    );

    private static $typesMap = array(
        'VAR_STRING' => 'string',
        'LONG' => 'integer',
        'FLOAT' => 'float',
        'CHAR' => 'string',
        'DATETIME' => 'datetime',
        'BLOB' => 'text',
        'TIMESTAMP' => 'timestamp'
    );

    public static function Map($name, &$parent, $dataType, &$options = null)
    {
        if(isset(self::$map[$dataType]))
        return new self::$map[$dataType]($parent, $name);
        else return new TextColumn ($parent, $name);
    }

    public static function MapDataType($nativeType)
    {
        if(isset(self::$typesMap[$nativeType]))
        return self::$typesMap[$nativeType];
        else return 'string';
    }
}