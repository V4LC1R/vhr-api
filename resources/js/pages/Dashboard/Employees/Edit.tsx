import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { EmployeeEditView } from "@/feature/employee/views/employee-edit-view";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { ArrowLeftIcon } from "lucide-react";
import { ReactNode } from "react";

interface Props {
    id: string;
}

export default function EmployeeEditPage({ id }: Props){
    return (
        <Page.Dashboard
            title="Editar Colaborador"
            action={
                <Button nativeButton={false} render={<Link href="/dashboard/employees" />}>
                    <ArrowLeftIcon />
                    Voltar
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
            <EmployeeEditView employeeId={id} />
        </Page.Dashboard>
    )
}

EmployeeEditPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
