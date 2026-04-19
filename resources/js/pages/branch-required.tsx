import { Head, Link, usePage } from '@inertiajs/react';
import { Building2, LogOut } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { logout } from '@/routes';
import type { Auth } from '@/types';

export default function BranchRequired() {
    const { auth } = usePage<{ auth: Auth }>().props;

    return (
        <>
            <Head title="Sin sucursal asignada" />

            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6">
                <div className="flex w-full max-w-sm flex-col items-center gap-6 text-center">

                    <div className="flex h-14 w-14 items-center justify-center rounded-xl bg-muted">
                        <AppLogoIcon className="h-8 w-8 text-foreground" />
                    </div>

                    <div className="space-y-1">
                        <p className="text-xs font-medium uppercase tracking-widest text-muted-foreground">
                            Comercial San Francisco
                        </p>
                        <h1 className="text-xl font-semibold">Sin sucursal asignada</h1>
                    </div>

                    <div className="rounded-lg border bg-muted/40 p-4 text-sm text-muted-foreground">
                        <div className="mb-3 flex justify-center">
                            <Building2 className="h-8 w-8 opacity-40" />
                        </div>
                        <p>
                            Tu cuenta <strong className="text-foreground">{auth.user.email}</strong> no
                            tiene ninguna sucursal asignada todavía.
                        </p>
                        <p className="mt-2">
                            Contacta al administrador del sistema para que te asigne acceso a una o más sucursales.
                        </p>
                    </div>

                    <Link
                        href={logout()}
                        method="post"
                        as="button"
                    >
                        <Button variant="outline" className="gap-2">
                            <LogOut className="h-4 w-4" />
                            Cerrar sesión
                        </Button>
                    </Link>

                </div>
            </div>
        </>
    );
}
