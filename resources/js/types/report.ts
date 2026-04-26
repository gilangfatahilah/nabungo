export type PeriodPreset =
  | 'this_month'
  | 'last_month'
  | 'last_3_months'
  | 'last_6_months'
  | 'this_year'
  | 'last_year'
  | 'custom';

export type CashFlowGroup = 'daily' | 'weekly' | 'monthly';

export interface Period {
  preset: PeriodPreset;
  from: string;
  to: string;
}

export interface MetricItem {
  value: number;
  change: number;
  trend: 'up' | 'down';
}

export interface ReportSummary {
  income: MetricItem;
  expense: MetricItem;
  net: MetricItem;
  savingsRate: MetricItem;
}

export interface CategoryBreakdown {
  category_id: number;
  category_name: string;
  total: number;
  percentage: number;
}

export interface CashFlowItem {
  date: string;
  income: number;
  expense: number;
}

export interface BudgetActual {
  category_id: number;
  category_name: string;
  budgeted: number;
  actual: number;
  remaining: number;
  usage: number;
  status: 'ok' | 'warning' | 'over';
}

export interface TopTransaction {
  id: number;
  type: 'income' | 'expense';
  amount: number;
  description: string | null;
  transaction_date: string;
  category: string | null;
  account: string | null;
}

export interface TopTransactions {
  income: TopTransaction[];
  expense: TopTransaction[];
}

export interface AccountTrendItem {
  date: string;
  balance: number;
}

export interface AccountTrend {
  account_id: number;
  account_name: string;
  account_type: string;
  current: number;
  trend: AccountTrendItem[];
}

export interface GoalSnapshot {
  id: number;
  title: string;
  account: string | null;
  target_amount: number;
  saved_amount: number;
  remaining: number;
  progress: number;
  status: 'ongoing' | 'achieved' | 'cancelled';
  due_date: string | null;
  days_left: number | null;
  months_to_finish: number | null;
}

export interface DebtGroup {
  total: number;
  paid: number;
  remaining: number;
}

export interface OverdueItem {
  id: number;
  title: string;
  type: 'debt' | 'receivable';
  remaining: number;
  due_date: string;
  contact_name: string | null;
}

export interface DebtSummary {
  debt: DebtGroup;
  receivable: DebtGroup;
  overdue: OverdueItem[];
}

export interface ReportPageProps {
  period: Period;
  cashFlowGroup: CashFlowGroup;
  summary: ReportSummary;
  categoryBreakdown: CategoryBreakdown[];
  cashFlow: CashFlowItem[];
  budgetVsActual: BudgetActual[];
  topTransactions: TopTransactions;
  accountTrends: AccountTrend[];
  goalSnapshot: GoalSnapshot[];
  debtSummary: DebtSummary;
}
