import { Fragment, type ReactNode } from "react"
import { Breadcrumb, BreadcrumbItem, BreadcrumbLink, BreadcrumbList, BreadcrumbSeparator } from "../ui/breadcrumb"
import { Link } from "@inertiajs/react"

type Props = {
    children:ReactNode
}

export function ContainerPage({children}:Props) {
    return (
        <div className="mx-auto flex h-full min-h-0 w-full flex-col md:max-w-225 pt-3 pb-3 md:px-0 px-2">
            {children}
        </div>
    )
}

// cabecalhos da pagina
export function HeaderPage({children}:Props) {
    return (
        <div className="flex items-center justify-between gap-4 pb-1">
            {children}
        </div>
    )
}

export function TitlePage({children}:Props) {
    return (
        <h1 className="text-2xl text-primary font-semibold tracking-tight">
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
                                    <BreadcrumbLink render={<Link href={it.link} />}>{it.name}</BreadcrumbLink>
                                </BreadcrumbItem>
                                {
                                    (idx+1) < chain.length && (
                                        <BreadcrumbSeparator />
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

export const BasePages = {
    Container:ContainerPage,
    Header:HeaderPage,
    Action:ActionsPage,
    Breadcrumb:BreadcrumbPage,
    Title:TitlePage
}