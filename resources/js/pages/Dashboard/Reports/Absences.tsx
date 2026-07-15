import { Page } from "@/components/pages";
import { AbsencesReportView } from "@/feature/report/views/absences-report-view";

import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function AbsencesReportPage(){
    return (
        <Page.Dashboard
            title="Faltas e horas negativas"
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/reports/absences',
                    name:"Relatórios"
                }
            ]}
        >
            <AbsencesReportView />
        </Page.Dashboard>
    )
}

AbsencesReportPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
