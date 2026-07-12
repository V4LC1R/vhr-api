import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { WorkloadListView } from "@/feature/workload/views/workload-list-view";

import AppLayout from "@/layouts/AppLayout";
import { PlusIcon } from "lucide-react";
import { ReactNode, useState } from "react";

export default function WorkloadListPage(){
    const [createOpen, setCreateOpen] = useState(false)

    return (
        <Page.Dashboard
            title="Jornadas"
            action={
                <Button onClick={() => setCreateOpen(true)}>
                    <PlusIcon />
                    Nova jornada
                </Button>
            }
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/workloads',
                    name:"Jornadas"
                }
            ]}
        >
            <WorkloadListView createOpen={createOpen} onCreateOpenChange={setCreateOpen} />
        </Page.Dashboard>
    )
}

WorkloadListPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
