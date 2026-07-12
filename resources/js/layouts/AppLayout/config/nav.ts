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
            { icon: 'lucide:calendar-days', label: 'Diárias', path: '/daily-engagements', permission: 'attendance.dailyEngagements.view' },
        ],
    },

    {
        icon: 'lucide:settings',
        label: 'Cadastros',
        path: '/companies',
        permission: 'core.companies.view',
        children: [
            { icon: 'lucide:building-2', label: 'Empresas', path: '/companies', permission: 'core.companies.view' },
            { icon: 'lucide:contact', label: 'Pessoas', path: '/persons', permission: 'core.persons.view' },
            { icon: 'lucide:user-cog', label: 'Usuários', path: '/users', permission: 'core.users.view' },
        ],
    },
];
