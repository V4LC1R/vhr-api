import { Fragment, type ReactNode } from "react"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "../ui/breadcrumb"
import { Link } from "@inertiajs/react"

type Props = {
    children:ReactNode
}

export function ContainerPage({children}:Props) {
    return (
        <div className=" flex flex-col h-full min-h-0 w-full mx-auto  pb-3 md:px-0 px-2">
            {children}
        </div>
    )
}

// cabecalhos da pagina
export function HeaderPage({children}:Props) {
    return (
        <div className="bg-white dark:bg-background p-6 flex flex-row items-center justify-between gap-4 border-b">
            {children}
        </div>
    )
}

export function TitlePage({children}:Props) {
    return (
        <h1 className="text-3xl text-primary font-medium tracking-tight">
            {children}
        </h1>
    )
}
export type ChainBreadcrumbs={link:string,name:string}

type BreadcrumbPageProps =  {
    chain:ChainBreadcrumbs[]
}
export function BreadcrumbPage({chain}:BreadcrumbPageProps) {
    return (
        <div className="flex flex-row w-auto">
            <Breadcrumb>
                <BreadcrumbList>
                    {
                        chain.map((it,idx)=>(
                            <Fragment key={it.link}>
                                <BreadcrumbItem>
                                    <BreadcrumbLink className="font-light" render={<Link href={it.link} />}>{it.name}</BreadcrumbLink>
                                </BreadcrumbItem>
                                {
                                    (idx+1) < chain.length && (
                                        <BreadcrumbSeparator className="font-light" />
                                    )
                                }
                            </Fragment>
                        ))
                    }
                </BreadcrumbList>
            </Breadcrumb>
        </div>
    )
}

export function ActionsPage({children}:Props) {
    return (
        <div className="flex items-center gap-2">
            {children}
        </div>
    )
}

// intermediarios do conteudo e o header

export function ContentPage({children}:Props) {
    return (
        <div className="flex min-h-0 flex-1 flex-col p-6">
            {children}
        </div>
    )
}
export const BasePages = {
    Container:ContainerPage,
    Header:HeaderPage,
    Action:ActionsPage,
    Breadcrumb:BreadcrumbPage,
    Title:TitlePage,
    Content:ContentPage
}