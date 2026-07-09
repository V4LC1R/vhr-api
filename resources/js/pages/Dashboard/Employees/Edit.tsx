import { Pages } from "@/components/pages";
import AppLayout from "@/layouts/AppLayout";
import { ReactNode } from "react";

export default function EmployeeEdit(){
    return (
        <Pages.Container>
            <></>
        </Pages.Container>
    )
}

EmployeeEdit.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;