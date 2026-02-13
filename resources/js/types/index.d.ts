import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';
import { Account } from '@/types';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    url: string;
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
    type: 'cash' | 'bank' | 'ewallet' | 'asset' | 'liability' | 'goal';
    balance: number;
    notes?: string;
    created_at: string;
    updated_at: string;
}

interface AccountHistory {
    id: number;
    user_id: number;
    account_id: number;
    transaction_id: number;
    type: 'in' | 'out';
    amount: number;
    balance_before: number;
    balance_after: number;
    notes?: string;
    created_at: string;
    updated_at: string;
    transaction: Transaction;
    account: Account;
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
    usage: number;
    total_expense: number;
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

interface Goal {
    id: number;
    user_id: number;
    account_id: number;
    title: string;
    target_amount: number;
    saved_amount: number;
    due_date?: string;
    notes?: string;
    status: 'ongoing' | 'achieved' | 'cancelled';
    deadline: string
    user: User
    account: Account
}

export interface ActivityLog {
    id: number;
    log_name: string;
    description: string | null;
    subject_id: number | null;
    subject_type: string | null;
    causer_id: number | null;
    causer_type: string | null;
    event: 'created' | 'updated' | 'deleted';
    properties: {
        old?: Record<string, any>;
        attributes?: Record<string, any>;
    };
    batch_uuid?: string | null;
    created_at: string;
    updated_at: string;
    causer: User;
}

export { TableResponse, PageQuery, User, Account, AccountHistory, Category, Budget, Transaction, Goal }

export type BreadcrumbItemType = BreadcrumbItem;
