import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { EmployeeListView } from "@/feature/employee/views/employee-list-view";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { PlusIcon } from "lucide-react";
import { ReactNode } from "react";

export default function EmployeeListPage(){
    return (
        <Page.Dashboard
            title="Colaboradores"
            action={
                <Button nativeButton={false} render={<Link href="/dashboard/employees/create" />}>
                    <PlusIcon />
                    Novo colaborador
                </Button>
            }
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/employees',
                    name:"Colaboradores"
                }
            ]}
        >
            <EmployeeListView/>
        </Page.Dashboard>
    )
}

EmployeeListPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;