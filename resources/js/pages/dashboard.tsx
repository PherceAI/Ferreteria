import { Head, usePage } from '@inertiajs/react';
import {
    AlertTriangle,
    Building2,
    Clock,
    Package,
    ShoppingCart,
    TrendingDown,
    Truck,
} from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import type { Auth } from '@/types';

type KpiCardProps = {
    title: string;
    value: string;
    description: string;
    icon: React.ElementType;
    badge?: { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' };
};

function KpiCard({ title, value, description, icon: Icon, badge }: KpiCardProps) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                <Icon className="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="flex items-end gap-2">
                    <span className="text-2xl font-bold">{value}</span>
                    {badge && (
                        <Badge variant={badge.variant} className="mb-0.5 text-xs">
                            {badge.label}
                        </Badge>
                    )}
                </div>
                <p className="mt-1 text-xs text-muted-foreground">{description}</p>
            </CardContent>
        </Card>
    );
}

function EmptyState({ icon: Icon, title, description }: { icon: React.ElementType; title: string; description: string }) {
    return (
        <div className="flex flex-col items-center justify-center gap-2 py-10 text-center text-muted-foreground">
            <Icon className="h-8 w-8 opacity-30" />
            <p className="text-sm font-medium">{title}</p>
            <p className="text-xs">{description}</p>
        </div>
    );
}

function greeting(): string {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos días';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
}

function formatDate(): string {
    return new Date().toLocaleDateString('es-EC', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

export default function Dashboard() {
    const { auth } = usePage<{ auth: Auth }>().props;
    const firstName = auth.user.name.split(' ')[0];
    const branchName = auth.activeBranch?.name ?? 'Sin sucursal';
    const branchCode = auth.activeBranch?.code ?? '';

    return (
        <>
            <Head title="Dashboard" />

            <div className="flex flex-col gap-6 p-6">

                {/* Header de bienvenida */}
                <div className="flex flex-col gap-1">
                    <div className="flex items-center gap-2">
                        <h1 className="text-2xl font-semibold">
                            {greeting()}, {firstName}
                        </h1>
                    </div>
                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                        <Building2 className="h-3.5 w-3.5" />
                        <span>{branchName}</span>
                        {branchCode && (
                            <Badge variant="outline" className="text-xs">
                                {branchCode}
                            </Badge>
                        )}
                        <span className="text-muted-foreground/50">·</span>
                        <span className="capitalize">{formatDate()}</span>
                    </div>
                </div>

                <Separator />

                {/* KPI Cards */}
                <div>
                    <h2 className="mb-3 text-sm font-medium text-muted-foreground uppercase tracking-wide">
                        Resumen operativo
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <KpiCard
                            title="Stock bajo mínimo"
                            value="—"
                            description="Productos por debajo del umbral definido"
                            icon={TrendingDown}
                            badge={{ label: 'ETL pendiente', variant: 'outline' }}
                        />
                        <KpiCard
                            title="Alertas activas"
                            value="—"
                            description="Requieren atención inmediata"
                            icon={AlertTriangle}
                            badge={{ label: 'ETL pendiente', variant: 'outline' }}
                        />
                        <KpiCard
                            title="Facturas pendientes"
                            value="—"
                            description="Sin confirmar en bodega"
                            icon={ShoppingCart}
                            badge={{ label: 'ETL pendiente', variant: 'outline' }}
                        />
                        <KpiCard
                            title="Transferencias"
                            value="—"
                            description="En tránsito entre sucursales"
                            icon={Truck}
                            badge={{ label: 'ETL pendiente', variant: 'outline' }}
                        />
                    </div>
                </div>

                {/* Fila inferior: Alertas + Actividad */}
                <div className="grid gap-4 lg:grid-cols-2">

                    {/* Panel de alertas */}
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <AlertTriangle className="h-4 w-4 text-amber-500" />
                                Alertas recientes
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <EmptyState
                                icon={AlertTriangle}
                                title="Sin alertas activas"
                                description="Las alertas de stock, caducidad y descuadres aparecerán aquí una vez que el ETL esté conectado."
                            />
                        </CardContent>
                    </Card>

                    {/* Panel de actividad reciente */}
                    <Card>
                        <CardHeader className="pb-3">
                            <CardTitle className="flex items-center gap-2 text-base">
                                <Clock className="h-4 w-4 text-muted-foreground" />
                                Actividad reciente
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <EmptyState
                                icon={Package}
                                title="Sin actividad registrada"
                                description="Los movimientos de inventario, confirmaciones de bodega y cambios operativos se reflejarán aquí."
                            />
                        </CardContent>
                    </Card>

                </div>

                {/* Banner de estado del sistema */}
                <Card className="border-dashed bg-muted/30">
                    <CardContent className="flex items-center gap-3 py-4">
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                            <Clock className="h-4 w-4 text-amber-600 dark:text-amber-400" />
                        </div>
                        <div>
                            <p className="text-sm font-medium">Sistema en configuración</p>
                            <p className="text-xs text-muted-foreground">
                                El bridge ETL con TINI está pendiente de configuración. Los datos operativos se habilitarán una vez conectado.
                            </p>
                        </div>
                    </CardContent>
                </Card>

            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
