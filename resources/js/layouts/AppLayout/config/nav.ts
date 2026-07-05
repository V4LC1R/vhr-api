export type NavItem = {
    icon: string;
    label: string;
    path: string;
    permission?:string,
    children?: NavItem[];
};

export const navItems: NavItem[] = [
    { icon: 'lucide:layout-dashboard', label: 'Dashboard', path: '/dashboard' },
    {
        icon: 'lucide:users',
        label: 'Colaboradores',
        path: '/colaboradores',
        
        children: [
            { icon: 'lucide:list', label: 'Listar', path: '/colaboradores' },
            { icon: 'lucide:user-plus', label: 'Novo', path: '/colaboradores/novo' },
        ],
    },
    { icon: 'lucide:clock', label: 'Lançamento de pontos', path: '/pontos' },
];
