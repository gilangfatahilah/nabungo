import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
  user: User;
}

export interface BreadcrumbItem {
  title: string;
  href: string;
}

export interface NavItem {
  title: string;
  href: string;
  icon?: LucideIcon;
  isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
  name: string;
  quote: { message: string; author: string };
  auth: Auth;
  ziggy: Config & { location: string };
  sidebarOpen: boolean;
};

interface PaginationLink {
  first: string,
  prev: string,
  next: string,
  last: string
}

interface PageQuery {
  page: number;
  per_page?: number;
  search?: string;
}

interface TableResponse<T> {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number;
  last_page: number;
  last_page_url: string;
  links: PaginationLink;
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number;
  total: number;
  meta: {
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
  }
}

interface User {
  id: number;
  name: string;
  email: string;
  avatar?: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

interface Account {
  id: number;
  user_id: number;
  name: string;
  type: 'cash' | 'bank' | 'ewallet' | 'asset' | 'liability';
  balance: number;
  notes?: string;
  created_at: string;
  updated_at: string;
}

interface Category {
  id: number;
  user_id: number;
  name: string;
  type: 'income' | 'expense';
  created_at: string;
  updated_at: string;
}

interface Budget {
  id: number;
  user_id: number;
  category_id: number;
  category: Category;
  month: string;
  amount: number;
  created_at: string;
  updated_at: string;
}

interface Transaction {
  id: number;
  user_id: number;
  category_id: number;
  account_id: number;
  account_target_id?: number;
  type: 'income' | 'expense' | 'transfer';
  amount: number;
  description?: string;
  transaction_date: string;
  created_at: string;
  updated_at: string;
  user: User;
  category: Category;
  account: Account;
  account_target?: Account;
}

export { TableResponse, PageQuery, User, Account, Category, Budget, Transaction }

export type BreadcrumbItemType = BreadcrumbItem;
