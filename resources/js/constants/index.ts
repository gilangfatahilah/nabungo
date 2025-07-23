import { NavItem } from '@/types';
import { ArrowLeftRight, ClipboardList, FileClock, HandCoins, LayoutGrid, SquarePercent, WalletMinimal, } from 'lucide-vue-next';

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Transaction',
    href: '/transaction',
    icon: ArrowLeftRight,
  },
  {
    title: 'Budget',
    href: '/budget',
    icon: ClipboardList,
  },
  {
    title: 'Goal',
    href: '/goal',
    icon: HandCoins,
  },
  {
    title: 'Debt',
    href: '/debt',
    icon: SquarePercent,
  },
  {
    title: 'Account',
    href: '/account',
    icon: WalletMinimal,
  },
  {
    title: 'History',
    href: '/history',
    icon: FileClock,
  },
];

export { mainNavItems };
