import { Head, useForm } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { register } from '@/routes';
import { request as passwordRequest } from '@/routes/password';

type TenantInfo = {
    name: string;
    company_name: string;
};

type Props = {
    tenant?: TenantInfo;
    /** `public` = /login (usuarios); `panel` = /panel/login (backoffice Filament). */
    loginContext?: 'public' | 'panel';
    canResetPassword?: boolean;
    canRegister?: boolean;
    status?: string;
};

export default function Login({
    tenant,
    loginContext = 'public',
    canResetPassword,
    canRegister,
    status,
}: Props) {
    const isTenantContext = tenant !== undefined;
    const displayName = isTenantContext
        ? tenant.company_name || tenant.name || 'Acceso'
        : import.meta.env.VITE_APP_NAME || 'Laravel';

    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/login');
    }

    const tenantSubtitle =
        loginContext === 'panel'
            ? 'Acceso al panel de administración'
            : 'Inicia sesión en tu espacio de trabajo';

    return (
        <>
            <Head title={isTenantContext ? displayName : 'Iniciar sesión'} />

            <div className="flex min-h-svh flex-col items-center justify-center bg-zinc-50 px-4 py-12 dark:bg-zinc-950">
                <div className="w-full max-w-sm space-y-8 rounded-lg border border-zinc-200 bg-white p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    {status && (
                        <p className="text-center text-sm font-medium text-green-600 dark:text-green-400">
                            {status}
                        </p>
                    )}

                    <div className="space-y-1 text-center">
                        <h1 className="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                            {isTenantContext ? displayName : 'Iniciar sesión'}
                        </h1>
                        <p className="text-sm text-zinc-500 dark:text-zinc-400">
                            {isTenantContext ? tenantSubtitle : 'Accede con tu cuenta'}
                        </p>
                    </div>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <label
                                htmlFor="email"
                                className="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                Correo electrónico
                            </label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value={data.email}
                                autoComplete="email"
                                autoFocus
                                required
                                className="block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none ring-zinc-400 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                                onChange={(e) => setData('email', e.target.value)}
                            />
                            {errors.email && (
                                <p className="text-sm text-red-600 dark:text-red-400">{errors.email}</p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <div className="flex items-center justify-between gap-2">
                                <label
                                    htmlFor="password"
                                    className="block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >
                                    Contraseña
                                </label>
                                {!isTenantContext && canResetPassword && (
                                    <TextLink
                                        href={passwordRequest.url()}
                                        className="text-xs text-zinc-600 underline dark:text-zinc-400"
                                    >
                                        ¿Olvidaste tu contraseña?
                                    </TextLink>
                                )}
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                value={data.password}
                                autoComplete="current-password"
                                required
                                className="block w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 shadow-sm outline-none ring-zinc-400 focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                                onChange={(e) => setData('password', e.target.value)}
                            />
                            {errors.password && (
                                <p className="text-sm text-red-600 dark:text-red-400">{errors.password}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-2">
                            <input
                                id="remember"
                                type="checkbox"
                                name="remember"
                                checked={data.remember}
                                className="size-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
                                onChange={(e) => setData('remember', e.target.checked)}
                            />
                            <label
                                htmlFor="remember"
                                className="text-sm text-zinc-600 dark:text-zinc-400"
                            >
                                Recordarme
                            </label>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="flex w-full justify-center rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
                        >
                            {processing ? 'Entrando…' : 'Entrar'}
                        </button>
                    </form>

                    {!isTenantContext && canRegister && (
                        <p className="text-center text-sm text-zinc-600 dark:text-zinc-400">
                            ¿No tienes cuenta?{' '}
                            <TextLink href={register.url()} tabIndex={5}>
                                Registrarse
                            </TextLink>
                        </p>
                    )}
                </div>
            </div>
        </>
    );
}
