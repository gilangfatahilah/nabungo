import { NavItem } from '@/types';
import { ArrowLeftRight, Blocks, ChartLine, ClipboardList, FileClock, HandCoins, LayoutGrid, Logs, SquarePercent, WalletMinimal, } from 'lucide-vue-next';

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
];

const secondaryNavItems: NavItem[] = [
  {
    title: 'Reports',
    url: '/report',
    icon: ChartLine,
  },
  {
    title: 'Transaction History',
    url: '/history',
    icon: FileClock,
  },
  {
    title: 'Activity Log',
    url: '/activity-log',
    icon: Logs,
  },
]

export { mainNavItems, secondaryNavItems };
