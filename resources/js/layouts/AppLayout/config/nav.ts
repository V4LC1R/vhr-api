export type NavItem = {
    icon: string;
    label: string;
    path: string;
    permission?: string;
    children?: NavItem[];
};

export const navItems: NavItem[] = [
    { icon: 'lucide:layout-dashboard', label: 'Dashboard', path: '/dashboard' },

    {
        icon: 'lucide:users',
        label: 'Colaboradores',
        path: '/colaboradores',
        permission: 'job.employees.view',
        children: [
            { icon: 'lucide:list', label: 'Listar', path: '/colaboradores', permission: 'job.employees.view' },
            { icon: 'lucide:user-plus', label: 'Novo', path: '/colaboradores/novo', permission: 'job.employees.create' },
            { icon: 'lucide:calendar-clock', label: 'Jornadas', path: '/jornadas', permission: 'job.workloads.view' },
        ],
    },

    {
        icon: 'lucide:clock',
        label: 'Ponto',
        path: '/pontos',
        permission: 'attendance.timeEntries.view',
        children: [
            { icon: 'lucide:clipboard-list', label: 'Lançamentos', path: '/pontos', permission: 'attendance.timeEntries.view' },
            { icon: 'lucide:file-check', label: 'Aprovações diárias', path: '/pontos-diarios', permission: 'attendance.dailyEngagements.view' },
        ],
    },

    {
        icon: 'lucide:settings',
        label: 'Cadastros',
        path: '/empresas',
        permission: 'core.companies.view',
        children: [
            { icon: 'lucide:building-2', label: 'Empresas', path: '/empresas', permission: 'core.companies.view' },
            { icon: 'lucide:contact', label: 'Pessoas', path: '/pessoas', permission: 'core.persons.view' },
            { icon: 'lucide:user-cog', label: 'Usuários', path: '/usuarios', permission: 'core.users.view' },
        ],
    },
];
