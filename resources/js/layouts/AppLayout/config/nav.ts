export type NavItem = {
    icon: string;
    label: string;
    path: string;
    permission?: string;
    role?: string;
    children?: NavItem[];
};

export const navItems: NavItem[] = [
    { icon: 'lucide:layout-dashboard', label: 'Painel', path: '/dashboard' },

    {
        icon: 'lucide:users',
        label: 'Colaboradores',
        path: '/dashboard/employees',
        permission: 'job.employees.view',
    },
    { 
        icon: 'lucide:clipboard-list', 
        label: 'Lançamentos', 
        path: '/dashboard/time-entries', 
        permission: 'attendance.timeEntries.view' 
    },
    { 
        icon: 'lucide:file-check', 
        label: 'Aprovações', 
        path: '/dashboard/approvals', 
        permission: 'attendance.dailyEngagements.approve' 
    },
    { 
        icon: 'lucide:list-checks', 
        label: 'Relatorios', 
        path: '/dashboard/reports/hours-summary', 
        permission: 'attendance.dailyEngagements.view' 
    },


    {
        icon: 'lucide:settings',
        label: 'Configurção',
        path: '/dashboard/companies',
        permission: 'core.companies.view',
        children: [
            { icon: 'lucide:building-2', label: 'Jornadas', path: '/dashboard/companies', permission: 'core.companies.view' },
            { icon: 'lucide:user-cog', label: 'Empresas', path: '/dashboard/users', permission: 'core.users.view' },
            { icon: 'lucide:user-cog', label: 'Usuarios', path: '/dashboard/users', permission: 'core.users.view' },
        ],
    },
];
