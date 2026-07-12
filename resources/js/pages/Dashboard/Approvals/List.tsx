import { Page } from "@/components/pages";
import { ApprovalsView } from "@/feature/attendance/views/approvals-view";

import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function ApprovalsPage(){
    return (
        <Page.Dashboard
            title="Aprovações de ponto"
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/approvals',
                    name:"Aprovações"
                }
            ]}
        >
            <ApprovalsView />
        </Page.Dashboard>
    )
}

ApprovalsPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
