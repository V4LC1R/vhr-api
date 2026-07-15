import { DownloadIcon, FileSpreadsheetIcon, FileTextIcon } from "lucide-react"

import { Button } from "@/components/ui/button"
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu"

type ExportFormat = "csv" | "xlsx" | "pdf"

interface ReportExportMenuProps {
    /** Endpoint de exportação, ex.: "/api/v1/reports/hours-summary/export". */
    endpoint: string
    range: { from: string; to: string }
    name?: string
}

/** Cada item é um link direto pro back — download nativo do navegador, sem blob no JS. */
export function ReportExportMenu({ endpoint, range, name }: ReportExportMenuProps) {
    function exportUrl(format: ExportFormat) {
        const params = new URLSearchParams({ format, from: range.from, to: range.to })
        if (name) params.set("name", name)
        return `${endpoint}?${params.toString()}`
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger render={<Button variant="outline" />}>
                <DownloadIcon />
                Exportar
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuItem render={<a href={exportUrl("csv")} />}>
                    <FileTextIcon />
                    CSV
                </DropdownMenuItem>
                <DropdownMenuItem render={<a href={exportUrl("xlsx")} />}>
                    <FileSpreadsheetIcon />
                    Excel (XLSX)
                </DropdownMenuItem>
                <DropdownMenuItem render={<a href={exportUrl("pdf")} />}>
                    <FileTextIcon />
                    PDF
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    )
}
