import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { ReactNode } from "react";

export default function EmployeeEditPage(){
    return (
        <Page.Dashboard
            title="Editar Colaborador"
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

EmployeeEditPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;