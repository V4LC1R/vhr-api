import { Pages } from "@/components/pages";
import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function EmployeeNew(){
    return (
        <Pages.Container>
            <></>
        </Pages.Container>
    )
}

EmployeeNew.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;