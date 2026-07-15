import { Page } from "@/components/pages";
import { DayliWorkersReportView } from "@/feature/report/views/dayli-workers-report-view";

import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function DayliWorkersReportPage(){
    return (
        <Page.Dashboard
            title="Diaristas e temporários"
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/reports/dayli-workers',
                    name:"Relatórios"
                }
            ]}
        >
            <DayliWorkersReportView />
        </Page.Dashboard>
    )
}

DayliWorkersReportPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
