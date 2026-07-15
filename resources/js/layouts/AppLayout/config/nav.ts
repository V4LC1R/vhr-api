export type NavItem = {
    icon: string;
    label: string;
    path: string;
    permission?: string;
    /** Gate por papel (hasRole) — usado quando o item não é uma permissão granular, e sim owner-only. */
    role?: string;
    children?: NavItem[];
};

export const navItems: NavItem[] = [
    { icon: 'lucide:layout-dashboard', label: 'Dashboard', path: '/dashboard' },

    {
        icon: 'lucide:users',
        label: 'Colaboradores',
        path: '/employees',
        permission: 'job.employees.view',
        children: [
            { icon: 'lucide:list', label: 'Colaboradores', path: '/dashboard/employees', permission: 'job.employees.view' },
            { icon: 'lucide:calendar-clock', label: 'Jornadas', path: '/dashboard/workloads', permission: 'job.workloads.view' },
        ],
    },

    {
        icon: 'lucide:clock',
        label: 'Ponto',
        path: '/time-entries',
        permission: 'attendance.timeEntries.view',
        children: [
            { icon: 'lucide:clipboard-list', label: 'Lançamentos', path: '/dashboard/time-entries', permission: 'attendance.timeEntries.view' },
            { icon: 'lucide:file-check', label: 'Aprovações', path: '/dashboard/approvals', permission: 'attendance.dailyEngagements.approve' },
        ],
    },

    {
        icon: 'lucide:bar-chart-3',
        label: 'Relatórios',
        path: '/dashboard/reports/hours-summary',
        permission: 'attendance.dailyEngagements.view',
        children: [
            { icon: 'lucide:list-checks', label: 'Geral', path: '/dashboard/reports/hours-summary', permission: 'attendance.dailyEngagements.view' },
            { icon: 'lucide:calendar-x', label: 'Faltas e horas negativas', path: '/dashboard/reports/absences', role: 'owner' },
            { icon: 'lucide:hand-coins', label: 'Diaristas', path: '/dashboard/reports/dayli-workers', role: 'owner' },
        ],
    },

    {
        icon: 'lucide:settings',
        label: 'Cadastros',
        path: '/dashboard/companies',
        permission: 'core.companies.view',
        children: [
            { icon: 'lucide:building-2', label: 'Empresas', path: '/dashboard/companies', permission: 'core.companies.view' },
            { icon: 'lucide:user-cog', label: 'Usuários', path: '/dashboard/users', permission: 'core.users.view' },
        ],
    },
];
