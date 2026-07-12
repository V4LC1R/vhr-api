import { Page } from "@/components/pages";
import { TimesheetView } from "@/feature/attendance/views/timesheet-view";

import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function TimeEntriesPage(){
    return (
        <Page.Dashboard
            title="Lançamento de ponto"
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/time-entries',
                    name:"Lançamentos"
                }
            ]}
        >
            <TimesheetView />
        </Page.Dashboard>
    )
}

TimeEntriesPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
