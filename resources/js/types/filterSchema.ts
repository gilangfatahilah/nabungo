export type FilterOperator =
  "=" | "!=" | "like" | "<" | ">" | "<=" | ">=" | "between";

export type FilterFieldType = "string" | "enum" | "date" | "number";

export interface EnumOption {
  label: string;
  value: string | number;
}

export interface BaseFilterField {
  type: FilterFieldType;
  operators: FilterOperator[];
}

export interface StringFilterField extends BaseFilterField {
  type: "string";
}

export interface NumberFilterField extends BaseFilterField {
  type: "number";
}

export interface DateFilterField extends BaseFilterField {
  type: "date";
}

export interface EnumFilterField extends BaseFilterField {
  type: "enum";
  enumOptions: EnumOption[];
}

export type FilterField =
  | StringFilterField
  | NumberFilterField
  | DateFilterField
  | EnumFilterField;

// Schema hasil dari backend
export type FilterSchema = Record<string, FilterField>;
