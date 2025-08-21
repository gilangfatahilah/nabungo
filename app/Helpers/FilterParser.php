<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class FilterParser
{
  /**
   * Parse filters from request
   */
  public static function parseFilters(array $rawFilters, array $schema): array
  {
    if (empty($rawFilters)) {
      return [];
    }

    // 🔥 Pre-filter sebelum parsing legacy
    if (!self::isStructuredFilterFormat($rawFilters)) {
      $rawFilters = array_values(array_filter($rawFilters, function ($item) {
        return isset($item['field']) || isset($item['operator']) || isset($item['value']);
      }));
    }

    $filters = [];
    if (self::isStructuredFilterFormat($rawFilters)) {
      $filters = self::parseStructuredFilters($rawFilters);
    } else {
      $filters = self::parseLegacyFilters($rawFilters);
    }

    return self::validateFilters($filters, $schema);
  }

  /**
   * Check if filters are in structured format (new format)
   */
  public static function isStructuredFilterFormat(array $filters): bool
  {
    if (empty($filters) || !is_array($filters[0])) {
      return false;
    }

    $firstFilter = $filters[0];

    return isset($firstFilter['field']) &&
      isset($firstFilter['operator']) &&
      (isset($firstFilter['value']) || in_array($firstFilter['operator'], ['is null', 'is not null']));
  }

  /**
   * Parse structured filters (new format)
   */
  public static function parseStructuredFilters(array $rawFilters): array
  {
    $filters = [];

    foreach ($rawFilters as $filter) {
      if (!is_array($filter) || empty($filter['field']) || empty($filter['operator'])) {
        continue;
      }

      $parsedFilter = [
        'field' => trim($filter['field']),
        'operator' => strtolower(trim($filter['operator'])),
        'value' => $filter['value'] ?? null
      ];

      if (in_array($parsedFilter['operator'], ['is null', 'is not null'])) {
        $parsedFilter['value'] = null;
      }

      if (in_array($parsedFilter['operator'], ['in', 'not in', 'between', 'not between'])) {
        if (!is_array($parsedFilter['value'])) {
          if (in_array($parsedFilter['operator'], ['in', 'not in'])) {
            $parsedFilter['value'] = [$parsedFilter['value']];
          } else {
            continue;
          }
        }
      }

      $filters[] = $parsedFilter;
    }

    return $filters;
  }

  /**
   * Parse legacy filters (old format)
   */
  public static function parseLegacyFilters(array $rawFilters): array
  {
    $filters = [];
    $field = $operator = $value = null;

    foreach ($rawFilters as $item) {
      if (isset($item['field'])) {
        $field = $item['field'];
      }

      if (isset($item['operator'])) {
        $operator = strtolower($item['operator']);
      }

      if (isset($item['value'])) {
        $value = $item['value'];
      }

      // kalau ketiga komponen sudah ada → simpan filter
      if ($field !== null && $operator !== null && $value !== null) {
        $filters[] = [
          'field' => $field,
          'operator' => $operator,
          'value' => $value,
        ];
        // reset biar siap baca filter berikutnya
        $field = $operator = $value = null;
      }
    }

    return $filters;
  }

  /**
   * Validate parsed filters against schema
   */
  public static function validateFilters(array $filters, array $schema): array
  {
    $validatedFilters = [];

    foreach ($filters as $filter) {
      $field = $filter['field'];
      $operator = $filter['operator'];

      if (!isset($schema[$field])) {
        Log::warning("Invalid filter field: {$field}");
        continue;
      }

      $fieldConfig = $schema[$field];

      if (!in_array($operator, $fieldConfig['operators'] ?? [])) {
        Log::warning("Invalid operator '{$operator}' for field '{$field}'");
        continue;
      }

      try {
        $filter['value'] = self::validateFilterValue(
          $filter['value'],
          $fieldConfig['type'],
          $operator
        );
        $validatedFilters[] = $filter;
      } catch (\Exception $e) {
        Log::warning("Invalid filter value for field '{$field}': " . $e->getMessage());
        continue;
      }
    }

    return $validatedFilters;
  }

  public static function validateFilterValue($value, string $fieldType, string $operator)
  {
    if (in_array($operator, ['is null', 'is not null'])) {
      return null;
    }

    if (in_array($operator, ['in', 'not in', 'between', 'not between'])) {
      if (!is_array($value)) {
        throw new \InvalidArgumentException("Array value required for operator: {$operator}");
      }

      if (in_array($operator, ['between', 'not between']) && count($value) !== 2) {
        throw new \InvalidArgumentException("Exactly 2 values required for between operator");
      }
    }

    switch ($fieldType) {
      case 'integer':
      case 'int':
        return is_array($value) ? array_map('intval', $value) : (int) $value;

      case 'float':
      case 'decimal':
        return is_array($value) ? array_map('floatval', $value) : (float) $value;

      case 'boolean':
      case 'bool':
        if (is_array($value)) {
          throw new \InvalidArgumentException("Boolean field cannot have array value");
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

      case 'date':
      case 'datetime':
        if (is_array($value)) {
          return array_map([self::class, 'validateDate'], $value);
        }
        return self::validateDate($value);

      default:
        return is_array($value) ? array_map('strval', $value) : (string) $value;
    }
  }

  public static function validateDate($value): string
  {
    if (!is_string($value)) {
      throw new \InvalidArgumentException("Date value must be string");
    }

    $formats = ['Y-m-d', 'Y-m-d H:i:s', 'Y/m/d', 'Y/m/d H:i:s'];

    foreach ($formats as $format) {
      $date = \DateTime::createFromFormat($format, $value);
      if ($date && $date->format($format) === $value) {
        return $date->format('Y-m-d H:i:s');
      }
    }

    $timestamp = strtotime($value);
    if ($timestamp === false) {
      throw new \InvalidArgumentException("Invalid date format: {$value}");
    }

    return date('Y-m-d H:i:s', $timestamp);
  }


  public static function getFilterSchema(string $modelClass): array
  {
    if (!method_exists($modelClass, 'filterableFields')) {
      throw new \RuntimeException("Model {$modelClass} must implement filterableFields()");
    }

    $fields = $modelClass::filterableFields();
    return collect($fields)->keyBy('key')->toArray();
  }

  public static function prepareFilterSchemaForFrontend(array $schema): array
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
