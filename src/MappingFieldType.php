<?php

declare(strict_types=1);

namespace BinaryCube\ElasticTool;

/**
 * Class MappingFieldType
 */
final class MappingFieldType
{

    /**
     * Field types declaration.
     */
    const
        TEXT       = 'text',
        KEYWORD    = 'keyword',
        COMPLETION = 'completion',

        BYTE         = 'byte',
        INTEGER      = 'integer',
        SHORT        = 'short',
        LONG         = 'long',
        DOUBLE       = 'double',
        FLOAT        = 'float',
        HALF_FLOAT   = 'half_float',
        SCALED_FLOAT = 'scaled_float',

        DATE       = 'date',
        DATE_NANOS = 'date_nanos',

        BOOLEAN = 'boolean',

        JOIN   = 'join',
        NESTED = 'nested',
        OBJECT = 'object',

        BINARY       = 'binary',
        IP           = 'ip',
        DENSE_VECTOR = 'dense_vector',
        ALIAS        = 'alias',
        HISTOGRAM    = 'histogram',
        FLATTENED    = 'flattened',

        POINT     = 'point',
        GEO_POINT = 'geo_point',

        INTEGER_RANGE = 'integer_range',
        FLOAT_RANGE   = 'float_range',
        LONG_RANGE    = 'long_range',
        DOUBLE_RANGE  = 'double_range',
        DATE_RANGE    = 'date_range',
        IP_RANGE      = 'ip_range',

        RANK_FEATURE  = 'rank_feature',
        RANK_FEATURES = 'rank_features',

        SEARCH_AS_YOU_TYPE = 'search_as_you_type';

}
