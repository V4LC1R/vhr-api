import { ChainBreadcrumbs, BasePages } from "./base";
import { ReactNode } from "react";

type Props = {
    title:string,
    breadcrumbs?:ChainBreadcrumbs []
    action?:ReactNode
    children?:ReactNode
}

export function Dashboard({children,action,title,breadcrumbs}:Props) {
    return (
        <BasePages.Container>
            <BasePages.Header>
                <BasePages.Title>{title ?? ''}</BasePages.Title>
               {
                action && (
                     <BasePages.Action>
                        {action}
                    </BasePages.Action>
                )
               }
            </BasePages.Header>
            
            <BasePages.Breadcrumb chain={breadcrumbs ?? []}>
               
            </BasePages.Breadcrumb>
            
            {children}
        </BasePages.Container>
    )
}

export const Page = {
    Dashboard
}