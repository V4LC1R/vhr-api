import * as React from "react"
import {
    ColumnDef,
    ExpandedState,
    Row,
    flexRender,
    getCoreRowModel,
    getExpandedRowModel,
    useReactTable,
} from "@tanstack/react-table"
import { ChevronRightIcon, Loader2Icon } from "lucide-react"

import { cn } from "@/lib/utils"
import { useIsMobile } from "@/hooks/use-mobile"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"

interface DataTableProps<TData> {
    columns: ColumnDef<TData, any>[]
    data: TData[]
    getRowCanExpand?: (row: Row<TData>) => boolean
    renderExpandedRow?: (row: Row<TData>) => React.ReactNode
    emptyMessage?: string
    footer?: React.ReactNode
    isLoading?: boolean
    /** Classes extras por linha (ex.: destacar fim de semana / hoje). */
    rowClassName?: (row: Row<TData>) => string | undefined
}

export function DataTable<TData>({
    columns,
    data,
    getRowCanExpand,
    renderExpandedRow,
    emptyMessage = "Sem resultados.",
    footer,
    isLoading,
    rowClassName,
}: DataTableProps<TData>) {
    const isMobile = useIsMobile()
    const [expanded, setExpanded] = React.useState<ExpandedState>({})

    const resolvedColumns = React.useMemo<ColumnDef<TData, any>[]>(
        () => (renderExpandedRow ? [...columns,expanderColumn<TData>()] : columns),
        [columns, renderExpandedRow]
    )

    const table = useReactTable({
        data,
        columns: resolvedColumns,
        state: { expanded },
        onExpandedChange: setExpanded,
        getRowCanExpand: getRowCanExpand ?? (() => Boolean(renderExpandedRow)),
        getCoreRowModel: getCoreRowModel(),
        getExpandedRowModel: getExpandedRowModel(),
    })

    return (
        <div className="relative flex min-h-0 flex-1 flex-col rounded-md border-border border overflow-hidden">
            {isLoading && (
                <div className="absolute inset-0 z-20 flex items-center justify-center rounded-md bg-background/60">
                    <Loader2Icon className="size-6 animate-spin text-muted-foreground" />
                </div>
            )}
            <Table containerClassName="min-h-0 flex-1 bg-white dark:bg-background overflow-y-auto">
                <TableHeader>
                    {table.getHeaderGroups().map((headerGroup) => (
                        <TableRow key={headerGroup.id}>
                            {headerGroup.headers.map((header) => (
                                <TableHead
                                    key={header.id}
                                    // sem `size` declarado a coluna flexiona com o conteúdo
                                    style={
                                        isMobile || header.column.columnDef.size === undefined
                                            ? undefined
                                            : { width: header.getSize() }
                                    }
                                    className="sticky top-0 z-10 bg-border/50"
                                >
                                    {header.isPlaceholder
                                        ? null
                                        : flexRender(header.column.columnDef.header, header.getContext())}
                                </TableHead>
                            ))}
                        </TableRow>
                    ))}
                </TableHeader>
                <TableBody>
                    {table.getRowModel().rows.length ? (
                        table.getRowModel().rows.map((row) => (
                            <React.Fragment key={row.id}>
                                <TableRow
                                    data-state={row.getIsSelected() ? "selected" : undefined}
                                    aria-expanded={row.getIsExpanded()}
                                    className={rowClassName?.(row)}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell className="px-5 py-3" key={cell.id}>
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </TableCell>
                                    ))}
                                </TableRow>
                                {row.getIsExpanded() && renderExpandedRow && (
                                    <TableRow>
                                        <TableCell colSpan={row.getVisibleCells().length} className="whitespace-normal">
                                            {renderExpandedRow(row)}
                                        </TableCell>
                                    </TableRow>
                                )}
                            </React.Fragment>
                        ))
                    ) : (
                        <TableRow>
                            <TableCell
                                colSpan={resolvedColumns.length}
                                className="h-24 text-center text-muted-foreground"
                            >
                                {isLoading ? null : emptyMessage}
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
                {resolvedColumns.some((column) => column.footer) && (
                    <TableFooter>
                        {table.getFooterGroups().map((footerGroup) => (
                            <TableRow key={footerGroup.id}>
                                {footerGroup.headers.map((header) => (
                                    // sticky por célula (como no header); bg opaco pras linhas não vazarem por baixo
                                    <TableCell
                                        key={header.id}
                                        className="sticky bottom-0 z-10 border-t bg-muted"
                                    >
                                        {header.isPlaceholder
                                            ? null
                                            : flexRender(
                                                  header.column.columnDef.footer,
                                                  header.getContext()
                                              )}
                                    </TableCell>
                                ))}
                            </TableRow>
                        ))}
                    </TableFooter>
                )}
            </Table>
            {footer && <div className="border-t bg-muted/50 p-2">{footer}</div>}
        </div>
    )
}

function expanderColumn<TData>(): ColumnDef<TData, any> {
    return {
        id: "expander",
        size: 12,
        header: () => null,
        cell: ({ row }) =>
            row.getCanExpand() ? (
                <Button
                    variant="ghost"
                    size="icon-xs"
                    onClick={row.getToggleExpandedHandler()}
                    aria-expanded={row.getIsExpanded()}
                >
                    <ChevronRightIcon
                        className={cn("transition-transform", row.getIsExpanded() && "rotate-90")}
                    />
                </Button>
            ) : null,
    }
}
