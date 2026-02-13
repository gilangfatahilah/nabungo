import { NavItem } from '@/types';
import { ArrowLeftRight, Blocks, ClipboardList, FileClock, HandCoins, LayoutGrid, SquarePercent, WalletMinimal, } from 'lucide-vue-next';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        url: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Transaction',
        url: '/transaction',
        icon: ArrowLeftRight,
    },
    {
        title: 'Account',
        url: '/account',
        icon: WalletMinimal,
    },
    {
        title: 'Category',
        url: '/category',
        icon: Blocks,
    },
    {
        title: 'Budget',
        url: '/budget',
        icon: ClipboardList,
    },
    {
        title: 'Goal',
        url: '/goal',
        icon: HandCoins,
    },
    {
        title: 'Debt',
        url: '/debt',
        icon: SquarePercent,
    },
    {
        title: 'History',
        url: '/history',
        icon: FileClock,
    },
];

export { mainNavItems };
