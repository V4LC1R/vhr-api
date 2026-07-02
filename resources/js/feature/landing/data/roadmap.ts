export type RoadmapStatus = "done" | "in-progress" | "planned";

export interface RoadmapPhase {
    id: string;
    period: string;
    title: string;
    status: RoadmapStatus;
    progress: number;
    items: string[];
}

export const statusLabel: Record<RoadmapStatus, string> = {
    done: "Concluído",
    "in-progress": "Em andamento",
    planned: "Planejado",
};

export const roadmap: RoadmapPhase[] = [
    {
        id: "fase-1",
        period: "Fase 1",
        title: "Fundação",
        status: "done",
        progress: 100,
        items: [
            "Cadastro de empresas e colaboradores",
            "Perfis e permissões por empresa",
            "Login seguro e recuperação de senha",
        ],
    },
    {
        id: "fase-2",
        period: "Fase 2",
        title: "Lançamento de pontos",
        status: "done",
        progress: 100,
        items: [
            "Digitalização das planilhas de ponto",
            "Aprovação de dias antes do fechamento",
            "Relatório mensal pronto para o contador",
        ],
    },
    {
        id: "fase-3",
        period: "Fase 3",
        title: "Gestão do dia a dia",
        status: "in-progress",
        progress: 55,
        items: [
            "Controle estruturado de folgas e ausências",
            "Exportação do relatório em PDF e Excel",
            "Painel com indicadores do mês",
        ],
    },
    {
        id: "fase-4",
        period: "Fase 4",
        title: "Automação",
        status: "planned",
        progress: 0,
        items: [
            "Bate-ponto automático",
            "Integração com a folha de pagamento",
            "Aplicativo para celular",
        ],
    },
];
