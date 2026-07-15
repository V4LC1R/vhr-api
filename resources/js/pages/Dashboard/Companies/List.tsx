import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { CompanyListView } from "@/feature/company/views/company-list-view";
import { useAuth } from "@/hooks/use-auth";

import AppLayout from "@/layouts/AppLayout";
import { PlusIcon } from "lucide-react";
import { ReactNode, useState } from "react";

export default function CompanyListPage(){
    const [createOpen, setCreateOpen] = useState(false)
    const { hasRole } = useAuth()

    return (
        <Page.Dashboard
            title="Empresas"
            action={
                hasRole("owner") && (
                    <Button onClick={() => setCreateOpen(true)}>
                        <PlusIcon />
                        Nova empresa
                    </Button>
                )
            }
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/companies',
                    name:"Empresas"
                }
            ]}
        >
            <CompanyListView createOpen={createOpen} onCreateOpenChange={setCreateOpen} />
        </Page.Dashboard>
    )
}

CompanyListPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
