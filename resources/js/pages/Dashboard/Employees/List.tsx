import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { EmployeeListView } from "@/feature/employee/views/employee-list-view";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { ReactNode } from "react";

export default function EmployeeListPage(){
    return (
        <Page.Dashboard
            title="Colaboradores"
            action={
                <Button render={<Link href="/dashboard/employees/create" />}>Novo colaborador</Button>
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