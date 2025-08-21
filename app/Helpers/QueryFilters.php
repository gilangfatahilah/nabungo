<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class QueryFilters
{
  /**
   * Allowed operators to prevent SQL injection
   */
  private static array $allowedOperators = [
    '=',
    '!=',
    '<>',
    '>',
    '>=',
    '<',
    '<=',
    'like',
    'not like',
    'between',
    'not between',
    'in',
    'not in',
    'is null',
    'is not null'
  ];

  /**
   * Apply filters to query builder
   */
  public static function apply(Builder $query, array $filters, array $schema): Builder
  {
    foreach ($filters as $filter) {
      try {
        self::applyFilter($query, $filter, $schema);
      } catch (InvalidArgumentException $e) {
        // Log error or handle gracefully
        // For now, skip invalid filters
        continue;
      }
    }

    return $query;
  }

  /**
   * Apply single filter to query
   */
  private static function applyFilter(Builder $query, array $filter, array $schema): void
  {
    $field = $filter['field'] ?? null;
    $operator = strtolower($filter['operator'] ?? '=');
    $value = $filter['value'] ?? null;

    // Validate required fields
    if (!$field || !isset($schema[$field])) {
      throw new InvalidArgumentException("Invalid or missing field: {$field}");
    }

    // Validate operator
    if (!in_array($operator, self::$allowedOperators)) {
      throw new InvalidArgumentException("Invalid operator: {$operator}");
    }

    // Get field configuration from schema
    $fieldConfig = $schema[$field];
    $fieldType = $fieldConfig['type'] ?? 'string';

    // Validate and cast value based on field type
    $value = self::validateAndCastValue($value, $fieldType, $operator);

    // Apply filter based on operator
    self::applyOperatorFilter($query, $field, $operator, $value, $fieldType);
  }

  /**
   * Validate and cast value based on field type
   */
  private static function validateAndCastValue($value, string $fieldType, string $operator)
  {
    // Handle null operators
    if (in_array($operator, ['is null', 'is not null'])) {
      return null;
    }

    // Handle different field types
    switch ($fieldType) {
      case 'date':
      case 'datetime':
        return self::validateDateValue($value, $operator);

      case 'integer':
      case 'int':
        return self::validateIntegerValue($value, $operator);

      case 'float':
      case 'decimal':
        return self::validateFloatValue($value, $operator);

      case 'boolean':
      case 'bool':
        return self::validateBooleanValue($value);

      case 'array':
        return self::validateArrayValue($value, $operator);

      default: // string
        return self::validateStringValue($value, $operator);
    }
  }

  /**
   * Apply filter based on operator
   */
  private static function applyOperatorFilter(Builder $query, string $field, string $operator, $value, string $fieldType): void
  {
    switch ($operator) {
      case 'like':
      case 'not like':
        $query->where($field, strtoupper($operator), "%{$value}%");
        break;

      case 'between':
      case 'not between':
        if (!is_array($value) || count($value) !== 2) {
          throw new InvalidArgumentException("Between operator requires array with exactly 2 values");
        }
        $method = $operator === 'between' ? 'whereBetween' : 'whereNotBetween';
        $query->{$method}($field, $value);
        break;

      case 'in':
      case 'not in':
        if (!is_array($value)) {
          throw new InvalidArgumentException("In operator requires array value");
        }
        $method = $operator === 'in' ? 'whereIn' : 'whereNotIn';
        $query->{$method}($field, $value);
        break;

      case 'is null':
        $query->whereNull($field);
        break;

      case 'is not null':
        $query->whereNotNull($field);
        break;

      default:
        // Handle date fields specially
        if (in_array($fieldType, ['date', 'datetime']) && $operator === '=') {
          $query->whereDate($field, '=', $value);
        } else {
          $query->where($field, $operator, $value);
        }
    }
  }

  /**
   * Validate date value
   */
  private static function validateDateValue($value, string $operator)
  {
    if (in_array($operator, ['between', 'not between', 'in', 'not in'])) {
      if (!is_array($value)) {
        throw new InvalidArgumentException("Date operator {$operator} requires array value");
      }
      return array_map([self::class, 'parseSingleDate'], $value);
    }

    return self::parseSingleDate($value);
  }

  /**
   * Parse single date value
   */
  private static function parseSingleDate($value): string
  {
    // More strict date validation
    if (!is_string($value) && !is_numeric($value)) {
      throw new InvalidArgumentException("Invalid date format");
    }

    // Try to parse with specific formats first
    $formats = ['Y-m-d', 'Y-m-d H:i:s', 'Y/m/d', 'Y/m/d H:i:s'];

    foreach ($formats as $format) {
      $date = \DateTime::createFromFormat($format, $value);
      if ($date && $date->format($format) === $value) {
        return $date->format('Y-m-d H:i:s');
      }
    }

    // Fallback to strtotime but with validation
    $timestamp = strtotime($value);
    if ($timestamp === false) {
      throw new InvalidArgumentException("Invalid date format: {$value}");
    }

    return date('Y-m-d H:i:s', $timestamp);
  }

  /**
   * Validate integer value
   */
  private static function validateIntegerValue($value, string $operator)
  {
    if (in_array($operator, ['between', 'not between', 'in', 'not in'])) {
      if (!is_array($value)) {
        throw new InvalidArgumentException("Operator {$operator} requires array value");
      }
      return array_map('intval', $value);
    }

    if (!is_numeric($value)) {
      throw new InvalidArgumentException("Invalid integer value: {$value}");
    }

    return (int) $value;
  }

  /**
   * Validate float value
   */
  private static function validateFloatValue($value, string $operator)
  {
    if (in_array($operator, ['between', 'not between', 'in', 'not in'])) {
      if (!is_array($value)) {
        throw new InvalidArgumentException("Operator {$operator} requires array value");
      }
      return array_map('floatval', $value);
    }

    if (!is_numeric($value)) {
      throw new InvalidArgumentException("Invalid float value: {$value}");
    }

    return (float) $value;
  }

  /**
   * Validate boolean value
   */
  private static function validateBooleanValue($value): bool
  {
    if (is_bool($value)) {
      return $value;
    }

    if (in_array(strtolower($value), ['true', '1', 'yes', 'on'])) {
      return true;
    }

    if (in_array(strtolower($value), ['false', '0', 'no', 'off', ''])) {
      return false;
    }

    throw new InvalidArgumentException("Invalid boolean value: {$value}");
  }

  /**
   * Validate array value
   */
  private static function validateArrayValue($value, string $operator)
  {
    if (!is_array($value)) {
      throw new InvalidArgumentException("Array field requires array value");
    }

    return $value;
  }

  /**
   * Validate string value
   */
  private static function validateStringValue($value, string $operator)
  {
    if (in_array($operator, ['between', 'not between', 'in', 'not in'])) {
      if (!is_array($value)) {
        throw new InvalidArgumentException("Operator {$operator} requires array value");
      }
      return array_map('strval', $value);
    }

    return (string) $value;
  }
}
