import { Page } from "@/components/pages";
import { HoursSummaryReportView } from "@/feature/report/views/hours-summary-report-view";

import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function HoursSummaryReportPage(){
    return (
        <Page.Dashboard
            title="Relatório geral de horas"
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/reports/hours-summary',
                    name:"Relatórios"
                }
            ]}
        >
            <HoursSummaryReportView />
        </Page.Dashboard>
    )
}

HoursSummaryReportPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
