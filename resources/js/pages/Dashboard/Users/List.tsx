import { Page } from "@/components/pages";
import { Button } from "@/components/ui/button";
import { UserListView } from "@/feature/user/views/user-list-view";
import { useAuth } from "@/hooks/use-auth";

import AppLayout from "@/layouts/AppLayout";
import { PlusIcon } from "lucide-react";
import { ReactNode, useState } from "react";

export default function UserListPage(){
    const [createOpen, setCreateOpen] = useState(false)
    const { can } = useAuth()

    return (
        <Page.Dashboard
            title="Usuários"
            action={
                can("core.users.create") && (
                    <Button onClick={() => setCreateOpen(true)}>
                        <PlusIcon />
                        Novo usuário
                    </Button>
                )
            }
            breadcrumbs={[
                {
                    link:'/dashboard',
                    name:"Dashboard"
                },
                {
                    link:'/dashboard/users',
                    name:"Usuários"
                }
            ]}
        >
            <UserListView createOpen={createOpen} onCreateOpenChange={setCreateOpen} />
        </Page.Dashboard>
    )
}

UserListPage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
