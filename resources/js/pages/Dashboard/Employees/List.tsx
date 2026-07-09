import { Pages } from "@/components/pages";
import { Button } from "@/components/ui/button";

import AppLayout from "@/layouts/AppLayout";
import { Link } from "@inertiajs/react";
import { ReactNode } from "react";

export default function EmployeeList(){
    return (
        <Pages.Container>
            <Pages.Header>
                <Pages.Title>Colaboradores</Pages.Title>
                <Pages.Action>
                    <Button render={<Link href="/dashboard/employees/create" />}>Novo colaborador</Button>
                </Pages.Action>
            </Pages.Header>
            <Pages.Breadcrumb chain={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/employees',
                    name:"Colaboradores"
                }
            ]}>
               
            </Pages.Breadcrumb>
            
        </Pages.Container>
    )
}

EmployeeList.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;