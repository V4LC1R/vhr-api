import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { EmployeeNewView } from "@/feature/employee/views/employee-new-view";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { ReactNode } from "react";

export default function EmployeeNewPage(){
    return (
        <Page.Dashboard
            title="Cadastrar Colaborador"
            action={
                <Button nativeButton={false} render={<Link href="/dashboard/employees" />}>Voltar</Button>
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
            <EmployeeNewView />
        </Page.Dashboard>
    )
}

EmployeeNewPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;