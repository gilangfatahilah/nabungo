<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use RuntimeException;
use DateTime;

class FilterParser
{
  private const NULL_OPERATORS = ['is null', 'is not null'];
  private const ARRAY_OPERATORS = ['in', 'not in', 'between', 'not between'];
  private const BETWEEN_OPERATORS = ['between', 'not between'];

  private const DATE_FORMATS = ['Y-m-d', 'Y-m-d H:i:s', 'Y/m/d', 'Y/m/d H:i:s'];

  /**
   * Parse and validate filters from request data
   */
  public static function parseFilters(array $rawFilters, array $schema): array
  {
    if (empty($rawFilters)) {
      return [];
    }

    // Handle special case where filters come as flat array with mixed structure
    $rawFilters = self::normalizeRawFilters($rawFilters);

    $cleanedFilters = self::cleanRawFilters($rawFilters);
    $parsedFilters = self::determineAndParseFilters($cleanedFilters);

    return self::validateFilters($parsedFilters, $schema);
  }

  /**
   * Normalize raw filters to handle mixed flat/nested structures
   */
  private static function normalizeRawFilters(array $rawFilters): array
  {
    // If it's already properly structured, return as is
    if (self::isStructuredFilterFormat($rawFilters)) {
      return $rawFilters;
    }

    // Check if we have a flat array with numbered keys containing mixed data
    if (self::isFlatMixedFormat($rawFilters)) {
      return self::groupFlatFilters($rawFilters);
    }

    return $rawFilters;
  }

  /**
   * Check if filters are in flat mixed format (like from URL query)
   */
  private static function isFlatMixedFormat(array $filters): bool
  {
    // Look for pattern where we have numbered keys with different types of data
    $hasId = false;
    $hasField = false;
    $hasOperator = false;
    $hasValue = false;

    foreach ($filters as $item) {
      if (is_array($item)) {
        if (isset($item['id'])) $hasId = true;
        if (isset($item['field'])) $hasField = true;
        if (isset($item['operator'])) $hasOperator = true;
        if (isset($item['value'])) $hasValue = true;
      }
    }

    return $hasId && $hasField && $hasOperator && $hasValue;
  }

  /**
   * Group flat filters by their ID
   */
  private static function groupFlatFilters(array $flatFilters): array
  {
    $groupedFilters = [];
    $currentId = null;
    $currentFilter = [];

    foreach ($flatFilters as $item) {
      if (!is_array($item)) {
        continue;
      }

      // If we encounter a new ID, save previous filter and start new one
      if (isset($item['id'])) {
        if ($currentId !== null && !empty($currentFilter)) {
          $groupedFilters[] = $currentFilter;
        }
        $currentId = $item['id'];
        $currentFilter = [];
        continue;
      }

      // Add field, operator, or value to current filter
      if (isset($item['field'])) {
        $currentFilter['field'] = $item['field'];
      }

      if (isset($item['operator'])) {
        $currentFilter['operator'] = $item['operator'];
      }

      if (isset($item['value'])) {
        if (isset($currentFilter['value'])) {
          // Merge values properly
          if (is_array($currentFilter['value']) && is_array($item['value'])) {
            $currentFilter['value'] = array_merge($currentFilter['value'], $item['value']);
          } elseif (is_array($item['value'])) {
            $currentFilter['value'] = array_merge((array)$currentFilter['value'], $item['value']);
          } elseif (is_array($currentFilter['value'])) {
            $currentFilter['value'][] = $item['value'];
          } else {
            $currentFilter['value'] = [$currentFilter['value'], $item['value']];
          }
        } else {
          $currentFilter['value'] = $item['value'];
        }
      }
    }

    // Don't forget the last filter
    if ($currentId !== null && !empty($currentFilter)) {
      $groupedFilters[] = $currentFilter;
    }

    return $groupedFilters;
  }

  /**
   * Remove empty or invalid filter items
   */
  private static function cleanRawFilters(array $rawFilters): array
  {
    if (self::isStructuredFilterFormat($rawFilters)) {
      return $rawFilters;
    }

    return array_values(array_filter($rawFilters, function ($item) {
      return isset($item['field']) || isset($item['operator']) || isset($item['value']);
    }));
  }

  /**
   * Determine filter format and parse accordingly
   */
  private static function determineAndParseFilters(array $filters): array
  {
    return self::isStructuredFilterFormat($filters)
      ? self::parseStructuredFilters($filters)
      : self::parseLegacyFilters($filters);
  }

  /**
   * Check if filters are in structured format (new format)
   */
  private static function isStructuredFilterFormat(array $filters): bool
  {
    if (empty($filters) || !is_array($filters[0] ?? null)) {
      return false;
    }

    $firstFilter = $filters[0];

    return isset($firstFilter['field'], $firstFilter['operator']) &&
      (isset($firstFilter['value']) || in_array($firstFilter['operator'], self::NULL_OPERATORS));
  }

  /**
   * Parse structured filters (new format)
   */
  private static function parseStructuredFilters(array $rawFilters): array
  {
    $filters = [];

    foreach ($rawFilters as $filter) {
      $parsedFilter = self::parseStructuredFilter($filter);
      if ($parsedFilter !== null) {
        $filters[] = $parsedFilter;
      }
    }

    return $filters;
  }

  /**
   * Parse a single structured filter
   */
  private static function parseStructuredFilter(array $filter): ?array
  {
    if (!is_array($filter) || empty($filter['field']) || empty($filter['operator'])) {
      return null;
    }

    $parsedFilter = [
      'field' => trim($filter['field']),
      'operator' => strtolower(trim($filter['operator'])),
      'value' => $filter['value'] ?? null
    ];

    // Handle null operators
    if (in_array($parsedFilter['operator'], self::NULL_OPERATORS)) {
      $parsedFilter['value'] = null;
      return $parsedFilter;
    }

    // Handle array operators
    if (in_array($parsedFilter['operator'], self::ARRAY_OPERATORS)) {
      $parsedFilter = self::handleArrayOperator($parsedFilter);
      if ($parsedFilter === null) {
        return null;
      }
    }

    return $parsedFilter;
  }

  /**
   * Handle array-based operators (in, not in, between, not between)
   */
  private static function handleArrayOperator(array $filter): ?array
  {
    if (!is_array($filter['value'])) {
      if (in_array($filter['operator'], ['in', 'not in'])) {
        $filter['value'] = [$filter['value']];
      } else {
        return null; // Invalid for between operators
      }
    }

    return $filter;
  }

  /**
   * Parse legacy filters (old format)
   */
  private static function parseLegacyFilters(array $rawFilters): array
  {
    $filters = [];
    $currentFilter = ['field' => null, 'operator' => null, 'value' => null];

    foreach ($rawFilters as $item) {
      $currentFilter = self::updateCurrentFilter($currentFilter, $item);

      if (self::isCompleteFilter($currentFilter)) {
        $filters[] = $currentFilter;
        $currentFilter = ['field' => null, 'operator' => null, 'value' => null];
      }
    }

    return $filters;
  }

  /**
   * Update current filter with new item data
   */
  private static function updateCurrentFilter(array $currentFilter, array $item): array
  {
    if (isset($item['field'])) {
      $currentFilter['field'] = $item['field'];
    }

    if (isset($item['operator'])) {
      $currentFilter['operator'] = strtolower($item['operator']);
    }

    if (isset($item['value'])) {
      $currentFilter['value'] = self::mergeFilterValues($currentFilter['value'], $item['value']);
    }

    return $currentFilter;
  }

  /**
   * Merge filter values (for legacy format)
   */
  private static function mergeFilterValues($existing, $new)
  {
    if ($existing === null) {
      return $new;
    }

    // Handle array merging more carefully
    if (is_array($existing) && is_array($new)) {
      // Use array_values to reindex and merge unique values
      $merged = array_merge(array_values($existing), array_values($new));
      return array_values(array_unique($merged));
    }

    // If existing is array but new is not, add new to array
    if (is_array($existing) && !is_array($new)) {
      $existing[] = $new;
      return array_values(array_unique($existing));
    }

    // If existing is not array but new is, combine them
    if (!is_array($existing) && is_array($new)) {
      return array_values(array_unique(array_merge([$existing], array_values($new))));
    }

    // If both are not arrays, create array with both values
    if ($existing !== $new) {
      return [$existing, $new];
    }

    return $new;
  }

  /**
   * Check if filter has all required components
   */
  private static function isCompleteFilter(array $filter): bool
  {
    return $filter['field'] !== null &&
      $filter['operator'] !== null &&
      $filter['value'] !== null;
  }

  /**
   * Validate parsed filters against schema
   */
  private static function validateFilters(array $filters, array $schema): array
  {
    $validatedFilters = [];

    foreach ($filters as $filter) {
      try {
        $validatedFilter = self::validateSingleFilter($filter, $schema);
        if ($validatedFilter !== null) {
          $validatedFilters[] = $validatedFilter;
        }
      } catch (\Exception $e) {
        Log::warning("Filter validation failed for field '{$filter['field']}': " . $e->getMessage());
      }
    }

    return $validatedFilters;
  }

  /**
   * Validate a single filter against schema
   */
  private static function validateSingleFilter(array $filter, array $schema): ?array
  {
    $field = $filter['field'];
    $operator = $filter['operator'];

    if (!isset($schema[$field])) {
      Log::warning("Invalid filter field: {$field}");
      return null;
    }

    $fieldConfig = $schema[$field];

    if (!in_array($operator, $fieldConfig['operators'] ?? [])) {
      Log::warning("Invalid operator '{$operator}' for field '{$field}'");
      return null;
    }

    $filter['value'] = self::validateAndCastValue(
      $filter['value'],
      $fieldConfig['type'],
      $operator
    );

    return $filter;
  }

  /**
   * Validate and cast filter value based on field type and operator
   */
  public static function validateAndCastValue($value, string $fieldType, string $operator)
  {
    if (in_array($operator, self::NULL_OPERATORS)) {
      return null;
    }

    self::validateArrayOperators($value, $operator);

    return self::castValueByType($value, $fieldType);
  }

  /**
   * Validate array operators requirements
   */
  private static function validateArrayOperators($value, string $operator): void
  {
    if (!in_array($operator, self::ARRAY_OPERATORS)) {
      return;
    }

    if (!is_array($value)) {
      throw new InvalidArgumentException("Array value required for operator: {$operator}");
    }

    if (in_array($operator, self::BETWEEN_OPERATORS) && count($value) !== 2) {
      throw new InvalidArgumentException("Exactly 2 values required for between operator");
    }
  }

  /**
   * Cast value based on field type
   */
  private static function castValueByType($value, string $fieldType)
  {
    switch ($fieldType) {
      case 'integer':
      case 'int':
        return self::castToInteger($value);

      case 'float':
      case 'decimal':
        return self::castToFloat($value);

      case 'boolean':
      case 'bool':
        return self::castToBoolean($value);

      case 'date':
      case 'datetime':
        return self::castToDate($value);

      default:
        return self::castToString($value);
    }
  }

  /**
   * Cast to integer
   */
  private static function castToInteger($value)
  {
    return is_array($value) ? array_map('intval', $value) : (int) $value;
  }

  /**
   * Cast to float
   */
  private static function castToFloat($value)
  {
    return is_array($value) ? array_map('floatval', $value) : (float) $value;
  }

  /**
   * Cast to boolean
   */
  private static function castToBoolean($value)
  {
    if (is_array($value)) {
      throw new InvalidArgumentException("Boolean field cannot have array value");
    }

    $result = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

    if ($result === null) {
      throw new InvalidArgumentException("Invalid boolean value: {$value}");
    }

    return $result;
  }

  /**
   * Cast to date
   */
  private static function castToDate($value)
  {
    return is_array($value)
      ? array_map([self::class, 'validateAndFormatDate'], $value)
      : self::validateAndFormatDate($value);
  }

  /**
   * Cast to string
   */
  private static function castToString($value)
  {
    return is_array($value) ? array_map('strval', $value) : (string) $value;
  }

  /**
   * Validate and format date string
   */
  public static function validateAndFormatDate(string $value): string
  {
    if (!is_string($value)) {
      throw new InvalidArgumentException("Date value must be string");
    }

    // Try predefined formats first
    foreach (self::DATE_FORMATS as $format) {
      $date = DateTime::createFromFormat($format, $value);
      if ($date && $date->format($format) === $value) {
        return $date->format('Y-m-d H:i:s');
      }
    }

    // Fallback to strtotime
    $timestamp = strtotime($value);
    if ($timestamp === false) {
      throw new InvalidArgumentException("Invalid date format: {$value}");
    }

    return date('Y-m-d H:i:s', $timestamp);
  }

  /**
   * Get filter schema from model
   */
  public static function getFilterSchema(string $modelClass): array
  {
    if (!method_exists($modelClass, 'filterableFields')) {
      throw new RuntimeException("Model {$modelClass} must implement filterableFields() method");
    }

    $fields = $modelClass::filterableFields();
    return collect($fields)->keyBy('key')->toArray();
  }

  /**
   * Prepare filter schema for frontend consumption
   */
  public static function prepareSchemaForFrontend(array $schema): array
  {
    return collect($schema)->values()->map(function ($field) {
      return [
        'key' => $field['key'],
        'label' => $field['label'],
        'type' => $field['type'],
        'operators' => $field['operators'],
        'enumOptions' => $field['enumOptions'] ?? null,
        'validation' => $field['validation'] ?? null,
      ];
    })->toArray();
  }
}
