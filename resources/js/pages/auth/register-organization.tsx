import { Head, Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { home } from '@/routes';
import { store } from '@/routes/register-organization';

type PlanRow = {
    id: string;
    name: string;
    description: string | null;
    price_monthly: string | number | null;
    price_yearly: string | number | null;
    currency: string | null;
    trial_period: number | null;
    trial_interval: string | null;
    modules: string[] | null;
};

type ModuleOption = { key: string; label: string };

export default function RegisterOrganization({
    plans,
    moduleOptions,
    appDomain,
}: {
    plans: PlanRow[];
    moduleOptions: ModuleOption[];
    appDomain: string;
}) {
    const [step, setStep] = useState(0);

    const form = useForm({
        id: '',
        company_name: '',
        plan_id: plans[0]?.id ?? '',
        active_modules: [] as string[],
        billing_cycle: 'monthly',
        billing_gateway: 'cash',
        admin_name: '',
        admin_email: '',
        admin_password: '',
        admin_password_confirmation: '',
    });

    const selectedPlan = useMemo(
        () => plans.find((p) => p.id === form.data.plan_id),
        [plans, form.data.plan_id],
    );

    const allowedModuleKeys = useMemo(() => {
        const fromPlan = selectedPlan?.modules;

        if (Array.isArray(fromPlan) && fromPlan.length > 0) {
            return fromPlan;
        }

        return moduleOptions.map((m) => m.key);
    }, [moduleOptions, selectedPlan?.modules]);

    const visibleModules = useMemo(
        () => moduleOptions.filter((m) => allowedModuleKeys.includes(m.key)),
        [allowedModuleKeys, moduleOptions],
    );

    function toggleModule(key: string, checked: boolean): void {
        const next = new Set(form.data.active_modules);

        if (checked) {
            next.add(key);
        } else {
            next.delete(key);
        }

        form.setData('active_modules', [...next]);
    }

    function handlePrimary(): void {
        if (step < 3) {
            setStep((s) => s + 1);

            return;
        }

        form.post(store.url());
    }

    const steps = ['Empresa', 'Plan y módulos', 'Administrador', 'Confirmación'];

    return (
        <>
            <Head title="Registrar empresa" />
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-muted p-6 md:p-10">
                <div className="flex w-full max-w-lg flex-col gap-6">
                    <Link
                        href={home()}
                        className="flex items-center gap-2 self-center text-sm font-medium"
                    >
                        Volver al inicio
                    </Link>

                    <div className="rounded-xl border bg-card p-8 shadow-sm">
                        <h1 className="mb-1 text-center text-xl font-semibold">
                            Alta de empresa
                        </h1>
                        <p className="mb-6 text-center text-sm text-muted-foreground">
                            {steps[step]} — paso {step + 1} de {steps.length}
                        </p>

                        <div className="mb-6 flex justify-center gap-2">
                            {steps.map((label, i) => (
                                <span
                                    key={label}
                                    className={`h-2 w-8 rounded-full ${i <= step ? 'bg-primary' : 'bg-muted-foreground/25'}`}
                                    title={label}
                                />
                            ))}
                        </div>

                        <div className="space-y-4">
                            <div className={step === 0 ? '' : 'hidden'}>
                                <div className="grid gap-2">
                                    <Label htmlFor="company_name">
                                        Nombre de la empresa
                                    </Label>
                                    <Input
                                        id="company_name"
                                        value={form.data.company_name}
                                        onChange={(e) =>
                                            form.setData(
                                                'company_name',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="organization"
                                    />
                                    <InputError message={form.errors.company_name} />
                                </div>
                                <div className="mt-4 grid gap-2">
                                    <Label htmlFor="id">
                                        Subdominio (solo minúsculas y guiones)
                                    </Label>
                                    <div className="flex flex-wrap items-center gap-2 text-sm">
                                        <Input
                                            id="id"
                                            value={form.data.id}
                                            onChange={(e) =>
                                                form.setData(
                                                    'id',
                                                    e.target.value
                                                        .toLowerCase()
                                                        .replace(
                                                            /[^a-z-]/g,
                                                            '',
                                                        ),
                                                )
                                            }
                                            className="max-w-[200px]"
                                            autoComplete="off"
                                        />
                                        <span className="text-muted-foreground">
                                            .{appDomain}
                                        </span>
                                    </div>
                                    <InputError message={form.errors.id} />
                                </div>
                            </div>

                            <div className={step === 1 ? '' : 'hidden'}>
                                <div className="grid gap-2">
                                    <Label>Plan</Label>
                                    <Select
                                        value={form.data.plan_id}
                                        onValueChange={(v) =>
                                            form.setData('plan_id', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Elige un plan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {plans.map((p) => (
                                                <SelectItem key={p.id} value={p.id}>
                                                    {p.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.plan_id} />
                                </div>

                                {selectedPlan?.description ? (
                                    <p className="mt-2 text-sm text-muted-foreground">
                                        {selectedPlan.description}
                                    </p>
                                ) : null}

                                <div className="mt-4 grid gap-2">
                                    <Label>Ciclo de facturación</Label>
                                    <Select
                                        value={form.data.billing_cycle}
                                        onValueChange={(v) =>
                                            form.setData(
                                                'billing_cycle',
                                                v as 'monthly' | 'yearly',
                                            )
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="monthly">
                                                Mensual
                                            </SelectItem>
                                            <SelectItem value="yearly">
                                                Anual
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>

                                <div className="mt-4 grid gap-3">
                                    <Label>Módulos</Label>
                                    {visibleModules.map((m) => (
                                        <label
                                            key={m.key}
                                            className="flex cursor-pointer items-center gap-2 text-sm"
                                        >
                                            <Checkbox
                                                checked={form.data.active_modules.includes(
                                                    m.key,
                                                )}
                                                onCheckedChange={(c) =>
                                                    toggleModule(
                                                        m.key,
                                                        c === true,
                                                    )
                                                }
                                            />
                                            {m.label}
                                        </label>
                                    ))}
                                    <InputError message={form.errors.active_modules} />
                                </div>
                            </div>

                            <div className={step === 2 ? '' : 'hidden'}>
                                <div className="grid gap-2">
                                    <Label htmlFor="admin_name">Nombre</Label>
                                    <Input
                                        id="admin_name"
                                        value={form.data.admin_name}
                                        onChange={(e) =>
                                            form.setData(
                                                'admin_name',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="name"
                                    />
                                    <InputError message={form.errors.admin_name} />
                                </div>
                                <div className="mt-4 grid gap-2">
                                    <Label htmlFor="admin_email">Correo</Label>
                                    <Input
                                        id="admin_email"
                                        type="email"
                                        value={form.data.admin_email}
                                        onChange={(e) =>
                                            form.setData(
                                                'admin_email',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="email"
                                    />
                                    <InputError message={form.errors.admin_email} />
                                </div>
                                <div className="mt-4 grid gap-2">
                                    <Label htmlFor="admin_password">
                                        Contraseña
                                    </Label>
                                    <PasswordInput
                                        id="admin_password"
                                        value={form.data.admin_password}
                                        onChange={(e) =>
                                            form.setData(
                                                'admin_password',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="new-password"
                                    />
                                    <InputError message={form.errors.admin_password} />
                                </div>
                                <div className="mt-4 grid gap-2">
                                    <Label htmlFor="admin_password_confirmation">
                                        Confirmar contraseña
                                    </Label>
                                    <PasswordInput
                                        id="admin_password_confirmation"
                                        value={
                                            form.data.admin_password_confirmation
                                        }
                                        onChange={(e) =>
                                            form.setData(
                                                'admin_password_confirmation',
                                                e.target.value,
                                            )
                                        }
                                        autoComplete="new-password"
                                    />
                                    <InputError
                                        message={
                                            form.errors
                                                .admin_password_confirmation
                                        }
                                    />
                                </div>
                            </div>

                            <div className={step === 3 ? '' : 'hidden'}>
                                <dl className="space-y-2 text-sm">
                                    <div>
                                        <dt className="text-muted-foreground">
                                            Empresa
                                        </dt>
                                        <dd>{form.data.company_name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">
                                            URL
                                        </dt>
                                        <dd>
                                            {form.data.id}.{appDomain}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">
                                            Plan
                                        </dt>
                                        <dd>{selectedPlan?.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">
                                            Administrador
                                        </dt>
                                        <dd>{form.data.admin_email}</dd>
                                    </div>
                                </dl>
                            </div>

                            <div className="flex justify-between gap-3 pt-4">
                                <Button
                                    type="button"
                                    variant="outline"
                                    disabled={step === 0 || form.processing}
                                    onClick={() => setStep((s) => Math.max(0, s - 1))}
                                >
                                    Atrás
                                </Button>
                                <Button
                                    type="button"
                                    disabled={form.processing}
                                    onClick={handlePrimary}
                                >
                                    {form.processing ? (
                                        'Procesando…'
                                    ) : step === 3 ? (
                                        'Crear empresa'
                                    ) : (
                                        'Siguiente'
                                    )}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
