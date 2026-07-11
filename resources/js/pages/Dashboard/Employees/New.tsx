import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";

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
            <></>
        </Page.Dashboard>
    )
}

EmployeeNewPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;