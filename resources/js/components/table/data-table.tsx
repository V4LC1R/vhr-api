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
import { ChevronRightIcon } from "lucide-react"

import { cn } from "@/lib/utils"
import { useIsMobile } from "@/hooks/use-mobile"
import { Button } from "@/components/ui/button"
import {
    Table,
    TableBody,
    TableCell,
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
}

export function DataTable<TData>({
    columns,
    data,
    getRowCanExpand,
    renderExpandedRow,
    emptyMessage = "Sem resultados.",
    footer,
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
        <div className="flex min-h-0 flex-1 flex-col rounded-md border">
            <Table containerClassName="min-h-0 flex-1 overflow-y-auto">
                <TableHeader>
                    {table.getHeaderGroups().map((headerGroup) => (
                        <TableRow key={headerGroup.id}>
                            {headerGroup.headers.map((header) => (
                                <TableHead
                                    key={header.id}
                                    style={isMobile ? undefined : { width: header.getSize() }}
                                    className="sticky top-0 z-10 bg-background"
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
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
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
                                {emptyMessage}
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
            {footer && <div className="border-t bg-muted/50 p-2">{footer}</div>}
        </div>
    )
}

function expanderColumn<TData>(): ColumnDef<TData, any> {
    return {
        id: "expander",
        size: 32,
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
