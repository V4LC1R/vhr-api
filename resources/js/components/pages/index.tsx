import { ThemeToggle } from "../theme/theme-toggle";
import { ThemeToggleButton } from "../theme/theme-toggle-button";
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
               
                <div className="flex flex-col justify-between w-full">
                    <BasePages.Breadcrumb chain={breadcrumbs ?? []}/>
                    <BasePages.Title>{title ?? ''}</BasePages.Title>
                    
                </div>
                
                <BasePages.Action>
                    <ThemeToggleButton
                        className="bg-transparent text-primary border-secondary border-2 cursor-pointer"
                    />
                </BasePages.Action>
              
            </BasePages.Header>
               
            <BasePages.Content>
               {children}
            </BasePages.Content>
            
        </BasePages.Container>
    )
}

export const Page = {
    Dashboard
}