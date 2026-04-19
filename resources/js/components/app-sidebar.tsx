import { Link } from '@inertiajs/react';
import {
    AlertTriangle,
    BarChart3,
    BookOpen,
    Building2,
    LayoutGrid,
    Package,
    ShoppingCart,
    Tag,
    Truck,
    Users,
    Warehouse,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { BranchSwitcher } from '@/components/branch-switcher';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarSeparator,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Inventario',
        href: '#',
        icon: Package,
        disabled: true,
    },
    {
        title: 'Compras',
        href: '#',
        icon: ShoppingCart,
        disabled: true,
    },
    {
        title: 'Ventas',
        href: '#',
        icon: Tag,
        disabled: true,
    },
    {
        title: 'Bodega',
        href: '#',
        icon: Warehouse,
        disabled: true,
    },
    {
        title: 'Contabilidad',
        href: '#',
        icon: BookOpen,
        disabled: true,
    },
    {
        title: 'Alertas',
        href: '#',
        icon: AlertTriangle,
        disabled: true,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Reportes',
        href: '#',
        icon: BarChart3,
        disabled: true,
    },
    {
        title: 'Proveedores',
        href: '#',
        icon: Truck,
        disabled: true,
    },
    {
        title: 'Sucursales',
        href: '#',
        icon: Building2,
        disabled: true,
    },
    {
        title: 'Usuarios',
        href: '#',
        icon: Users,
        disabled: true,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} label="Módulos" />
                <SidebarSeparator />
                <NavMain items={adminNavItems} label="Administración" />
            </SidebarContent>

            <SidebarFooter>
                <BranchSwitcher />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
